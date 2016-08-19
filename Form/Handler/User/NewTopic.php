<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Form\Handler\User;

use Zikula\Module\DizkusModule\Manager\ForumManager;
use Zikula\Module\DizkusModule\Manager\TopicManager;
use ModUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\RouteUrl;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Module\DizkusModule\Entity\RankEntity;

/**
 * This class provides a handler to create a new topic.
 */
class NewTopic extends \Zikula_Form_AbstractHandler
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
     * @throws AccessDeniedException If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new AccessDeniedException();
        }

        // get the input
        $this->_forumId = (int) $this->request->query->get('forum');

        if (!isset($this->_forumId)) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Missing forum id.'));
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_index', array(), RouterInterface::ABSOLUTE_URL);
            return $view->redirect($url);
        }

        $managedforum = new ForumManager($this->_forumId);
        if ($managedforum->get()->isLocked()) {
            // it should be impossible for a user to get here, but this is just a sanity check
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! This forum is locked. New topics cannot be created.'));
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewforum', array('forum' => $this->_forumId), RouterInterface::ABSOLUTE_URL);
            return $view->redirect($url);
        }
        $view->assign('forum', $managedforum->get());
        $view->assign('breadcrumbs', $managedforum->getBreadcrumbs(false));
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
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewforum', array('forum' => $this->_forumId), RouterInterface::ABSOLUTE_URL);
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        // check hooked modules for validation for POST
        $postHook = new ValidationHook(new ValidationProviders());
        /** @var $postHookValidators \Zikula\Core\Hook\ValidationProviders */
        $postHookValidators = $this->dispatchHooks('dizkus.ui_hooks.post.validate_edit', $postHook)->getValidators();
        if ($postHookValidators->hasErrors()) {
            // do not use $view->setErrorMsg() here so that form is redisplayed
            foreach ($postHookValidators->getErrors() as $error) {
                $this->request->getSession()->getFlashBag()->add('error', "Error! $error");
            }
            return false;
        }
        // check hooked modules for validation for TOPIC
        $topicHook = new ValidationHook(new ValidationProviders());
        /** @var $topicHookValidators \Zikula\Core\Hook\ValidationProviders */
        $topicHookValidators = $this->dispatchHooks('dizkus.ui_hooks.topic.validate_edit', $topicHook)->getValidators();
        if ($topicHookValidators->hasErrors()) {
            // do not use $view->setErrorMsg() here so that form is redisplayed
            foreach ($postHookValidators->getErrors() as $error) {
                $this->request->getSession()->getFlashBag()->add('error', "Error! $error");
            }
            return false;
        }

        $data = $view->getValues();
        $data['forum_id'] = $this->_forumId;
        $data['message'] = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $data['message']);
        $data['title'] = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $data['title']);

        $newManagedTopic = new TopicManager();
        $newManagedTopic->prepare($data);

        // check to see if the post contains spam
        if (ModUtil::apiFunc($this->name, 'user', 'isSpam', $newManagedTopic->getFirstPost())) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Your post contains unacceptable content and has been rejected.'));
            return false;
        }

        // show preview
        if ($args['commandName'] == 'preview') {
            $view->assign('preview', true);
            $post = $newManagedTopic->getPreview()->toArray();
            $post['post_id'] = 0;
            $post['post_time'] = time();
            $post['topic_id'] = 0;
            $post['attachSignature'] = $data['attachSignature'];
            $post['subscribe_topic'] = $data['subscribe_topic'];
            $post['solveStatus'] = $data['solveStatus'];
            $view->assign('post', $post);
            $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
            $view->assign('last_visit_unix', $lastVisitUnix);
            $view->assign('data', $data);
            list(, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => RankEntity::TYPE_POSTCOUNT));
            $this->view->assign('ranks', $ranks);

            return true;
        }

        // store new topic
        $newManagedTopic->create();
        $url = RouteUrl::createFromRoute('zikuladizkusmodule_user_viewforum', array('topic' => $newManagedTopic->getId()));
        // notify hooks for both POST and TOPIC
        $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($newManagedTopic->getFirstPost()->getPost_id(), $url));
        $this->dispatchHooks('dizkus.ui_hooks.topic.process_edit', new ProcessHook($newManagedTopic->getId(), $url));

        // notify topic & forum subscribers
//        ModUtil::apiFunc($this->name, 'notify', 'emailSubscribers', array('post' => $newManagedTopic->getFirstPost()));

        // redirect to the new topic
        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $newManagedTopic->getId()), RouterInterface::ABSOLUTE_URL);
        return $view->redirect($url);
    }

}
