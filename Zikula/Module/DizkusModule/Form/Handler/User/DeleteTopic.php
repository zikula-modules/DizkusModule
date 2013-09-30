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
use LogUtil;
use Zikula_Form_View;
use Zikula_Exception_Forbidden;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Module\DizkusModule\Entity\ForumUserEntity;
use Zikula\Module\DizkusModule\Manager\PostManager;
use Zikula\Module\DizkusModule\Manager\TopicManager;

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
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        $this->topic_id = (int)$this->request->query->get('topic');

        if (empty($this->topic_id)) {
            $post_id = (int)$this->request->query->get('post');
            if (empty($post_id)) {
                return LogUtil::registerArgsError();
            }
            $managedPost = new PostManager($post_id);
            $this->topic_id = $managedPost->getTopicId();
        }

        $managedTopic = new TopicManager($this->topic_id);

        $this->topic_poster = $managedTopic->get()->getPoster();
        $topicPerms = $managedTopic->getPermissions();

        if ($topicPerms['moderate'] <> true) {
            return LogUtil::registerPermissionError();
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
            $url = ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $this->topic_id));

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
            ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array(
                'toaddress' => $this->topic_poster->getUser()->getEmail(),
                'subject' => $this->__('Post deleted'),
                'body' => $data['reason'],
                'html' => true)
            );
            LogUtil::registerStatus($this->__('Email sent!'));
        }

        // redirect to the forum of the deleted topic
        $url = ModUtil::url($this->name, 'user', 'viewforum', array('forum' => $forum_id));

        return $view->redirect($url);
    }

}
