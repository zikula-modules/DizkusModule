<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * This class provides a handler to create a new topic.
 */
class Dizkus_Form_Handler_User_NewTopic extends Zikula_Form_AbstractHandler
{

    /**
     * forum id
     *
     * @var integer
     */
    private $_forumId;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        // get the input
        $this->_forumId = (int)$this->request->query->get('forum');

        if (!isset($this->_forumId)) {
            return LogUtil::registerError($this->__('Error! Missing forum id.'), null, ModUtil::url('Dizkus', 'user', 'index'));
        }

        $forum = new Dizkus_Manager_Forum($this->_forumId);
        if ($forum->get()->isLocked()) {
            // it should be impossible for a user to get here, but this is just a sanity check
            return LogUtil::registerError($this->__('Error! This forum is locked. New topics cannot be created.'), null, ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $this->_forumId)));
        }
        $view->assign('forum', $forum->get());
        $view->assign('breadcrumbs', $forum->getBreadcrumbs(false));
        $view->assign('preview', false);

        return true;
    }

    /**
     * Handle form submission.
     *
     * @param Zikula_Form_View $view  Current Zikula_Form_View instance.
     * @param array            &$args Arguments.
     *
     * @return bool|void
     */
    function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $this->_forumId));
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        // check hooked modules for validation for POST
        $postHook = new Zikula_ValidationHook('dizkus.ui_hooks.post.validate_edit', new Zikula_Hook_ValidationProviders());
        $postHookValidators = $this->notifyHooks($postHook)->getValidators();
        if ($postHookValidators->hasErrors()) {
            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
        }
        // check hooked modules for validation for TOPIC
        $topicHook = new Zikula_ValidationHook('dizkus.ui_hooks.topic.validate_edit', new Zikula_Hook_ValidationProviders());
        $topicHookValidators = $this->notifyHooks($topicHook)->getValidators();
        if ($topicHookValidators->hasErrors()) {
            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
        }

        $data = $view->getValues();
        $data['forum_id'] = $this->_forumId;
        $data['message'] = ModUtil::apiFunc('Dizkus', 'user', 'dzkstriptags', $data['message']);
        $data['title'] = ModUtil::apiFunc('Dizkus', 'user', 'dzkstriptags', $data['title']);

        /* if ($this->isSpam($args['message'])) {
          return LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
          } */

        $newManagedTopic = new Dizkus_Manager_Topic();
        $newManagedTopic->prepare($data);

        // show preview
        if ($args['commandName'] == 'preview') {
            $view->assign('preview', true);
            $view->assign('post', $newManagedTopic->getPreview());
            list($lastVisit, $lastVisitUnix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $view->assign('last_visit', $lastVisit);
            $view->assign('last_visit_unix', $lastVisitUnix);
            $view->assign('data', $data);
            return true;
        }

        // store new topic
        $topicId = $newManagedTopic->create();
        $url = new Zikula_ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $newManagedTopic->getId()));
        // notify hooks for both POST and TOPIC
        $this->notifyHooks(new Zikula_ProcessHook('dizkus.ui_hooks.post.process_edit', $newManagedTopic->getFirstPost()->getPost_id(), $url));
        $this->notifyHooks(new Zikula_ProcessHook('dizkus.ui_hooks.topic.process_edit', $newManagedTopic->getId(), $url));

        // notify topic & forum subscribers
        ModUtil::apiFunc('Dizkus', 'notify', 'emailSubscribers', array('post' => $newManagedTopic->getFirstPost()));

        // redirect to the new topic
        return $view->redirect($url->getUrl());
    }

}