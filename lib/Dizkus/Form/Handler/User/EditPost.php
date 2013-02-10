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
class Dizkus_Form_Handler_User_EditPost extends Zikula_Form_AbstractHandler
{

    /**
     * Dizkus_Manager_Post
     *
     * @var integer
     */
    private $_post;

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
        $id = (int)$this->request->query->get('post');

        if (!isset($id)) {
            return LogUtil::registerError(
                $this->__('Error! Missing post id.'), null, ModUtil::url('Dizkus', 'user', 'main')
            );
        }

        $this->_post = new Dizkus_Manager_Post($id);
        $view->assign($this->_post->toArray());
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
        $data = $view->getValues();
        $deleting = (isset($data['delete']) && $data['delete'] === true);
        $fragment = $deleting ? null : 'pid' . $this->_post->getId();
        $url = new Zikula_ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $this->_post->getTopicId()), $fragment);

        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        // check hooked modules for validation
        $hookarea = $deleting ? 'dizkus.ui_hooks.post.validate_delete' : 'dizkus.ui_hooks.post.validate_edit';
        $hook = new Zikula_ValidationHook($hookarea, new Zikula_Hook_ValidationProviders());
        $hookvalidators = $this->notifyHooks($hook)->getValidators();
        if ($hookvalidators->hasErrors()) {
            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
        }

        /* if ($this->isSpam($args['message'])) {
          return LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
          } */

        if ($deleting) {
            $this->_post->delete();
            $this->notifyHooks(new Zikula_ProcessHook('dizkus.ui_hooks.post.process_delete', $this->_post->getId()));
            return $view->redirect($url->getUrl());
        }
        unset($data['delete']);

        $this->_post->prepare($data);

        // show preview
        if ($args['commandName'] == 'preview') {
            $view->assign('preview', true);
            $view->assign('post', $this->_post->toArray());
            list($lastVisit, $lastVisitUnix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $view->assign('last_visit', $lastVisit);
            $view->assign('last_visit_unix', $lastVisitUnix);
            $view->assign('data', $data);
            return true;
        }

        // store post
        $this->_post->update();
        $this->notifyHooks(new Zikula_ProcessHook('dizkus.ui_hooks.post.process_edit', $this->_post->getId(), $url));

        // redirect to the new topic
        return $view->redirect($url->getUrl());
    }

}