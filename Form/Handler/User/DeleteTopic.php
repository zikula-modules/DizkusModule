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

use ModUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Module\DizkusModule\Entity\ForumUserEntity;
use Zikula\Module\DizkusModule\Manager\PostManager;
use Zikula\Module\DizkusModule\Manager\TopicManager;
use Symfony\Component\Routing\RouterInterface;

/**
 * This class provides a handler to delete a topic.
 */
class DeleteTopic extends \Zikula_Form_AbstractHandler
{

    /**
     * topic id
     *
     * @var integer
     */
    private $topic_id;

    /**
     * topic poster
     *
     * @var ForumUserEntity
     */
    private $topic_poster;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws AccessDeniedException If the current user does not have adequate permissions to perform this function.
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new AccessDeniedException();
        }

        $this->topic_id = (int)$this->request->query->get('topic');

        if (empty($this->topic_id)) {
            $post_id = (int)$this->request->query->get('post');
            if (empty($post_id)) {
                throw new \InvalidArgumentException();
            }
            $managedPost = new PostManager($post_id);
            $this->topic_id = $managedPost->getTopicId();
        }

        $managedTopic = new TopicManager($this->topic_id);

        $this->topic_poster = $managedTopic->get()->getPoster();
        $topicPerms = $managedTopic->getPermissions();

        if ($topicPerms['moderate'] <> true) {
            throw new AccessDeniedException();
        }

        $view->assign($managedTopic->toArray());

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
        // rewrite to topic if cancel was pressed
        if ($args['commandName'] == 'cancel') {
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->topic_id), RouterInterface::ABSOLUTE_URL);
            return $view->redirect($url);
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->dispatchHooks('dizkus.ui_hooks.topic.validate_delete', $hook)->getValidators();
        if ($hookvalidators->hasErrors()) {
            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
        }

        $forum_id = ModUtil::apiFunc($this->name, 'topic', 'delete', array('topic' => $this->topic_id));
        $this->dispatchHooks('dizkus.ui_hooks.topic.process_delete', new ProcessHook($this->topic_id));

        $data = $view->getValues();

        // send the poster a reason why his/her post was deleted
        if ($data['sendReason'] && !empty($data['reason'])) {
            $poster = $this->topic_poster->getUser();
            ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array(
                'toaddress' => $poster['email'],
                'subject' => $this->__('Post deleted'),
                'body' => $data['reason'],
                'html' => true)
            );
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Email sent!'));
        }

        // redirect to the forum of the deleted topic
        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewforum', array('forum' => $forum_id), RouterInterface::ABSOLUTE_URL);
        return $view->redirect($url);
    }

}
