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
 * This class provides a handler to delete a topic.
 */
class Dizkus_Form_Handler_User_DeleteTopic extends Zikula_Form_AbstractHandler
{

    /**
     * topic id
     *
     * @var integer
     */
    private $topic_id;

    /**
     * topic poster uid
     *
     * @var integer
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
    function initialize(Zikula_Form_View $view)
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
            $managedPost = new Dizkus_Manager_Post($post_id);
            $this->topic_id = $managedPost->getTopicId();
        }

        // TODO: the api method 'readtopic' has been deleted. This is left here as a reminder
        // that there are many other usages throughout the module that must also be removed.
//        $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array(
//                    'topic_id' => $this->topic_id,
//                    'count' => false)
//        );
        $topic = new Dizkus_Manager_Topic($this->topic_id);

        $this->topic_poster = $topic->get()->getTopic_poster();
        $topicPerms = $topic->getPermissions();

        if ($topicPerms['moderate'] <> true) {
            return LogUtil::registerPermissionError();
        }

        $view->assign($topic->toArray());

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
        // rewrite to topic if cancel was pressed
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $this->topic_id));
            return $view->redirect($url);
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $hook = new Zikula_ValidationHook('dizkus.ui_hooks.topic.validate_delete', new Zikula_Hook_ValidationProviders());
        $hookvalidators = $this->notifyHooks($hook)->getValidators();
        if ($hookvalidators->hasErrors()) {
            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
        }

        $forum_id = ModUtil::apiFunc('Dizkus', 'topic', 'delete', $this->topic_id);
        $this->notifyHooks(new Zikula_ProcessHook('dizkus.ui_hooks.topic.process_delete', $this->topic_id));

        $data = $view->getValues();

        // send the poster a reason why his/her post was deleted
        if ($data['sendReason'] && !empty($data['reason'])) {
            $toaddress = UserUtil::getVar('email', $this->topic_poster);
            ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array(
                'toaddress' => $toaddress,
                'subject' => $this->__('Post deleted'),
                'body' => $data['reason'],
                'html' => true)
            );
            LogUtil::registerStatus($this->__('Email sent!'));
        }

        // redirect to the forum of the deleted topic
        $url = ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id));
        return $view->redirect($url);
    }

}