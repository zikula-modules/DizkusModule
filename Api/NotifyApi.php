<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Dizkus\Api;

use System;
use ModUtil;
use Zikula_View;
use DateUtil;
use UserUtil;
use SecurityUtil;
use DataUtil;
use LogUtil;

class NotifyApi extends \Zikula_AbstractApi
{

    /**
     * Notify Subscribers by e-mail
     *
     * Sending notify e-mail to users subscribed to the topic or the forum
     *
     * @params $args['post'] Dizkus_Entity_Post
     *
     * @returns boolean
     */
    public function emailSubscribers($args)
    {
        setlocale(LC_TIME, System::getVar('locale'));
        $dizkusModuleInfo = ModUtil::getInfoFromName('Dizkus');
        $dizkusFrom = ModUtil::getVar('Dizkus', 'email_from');
        $fromAddress = !empty($dizkusFrom) ? $dizkusFrom : System::getVar('adminmail');
        /* @var $post Dizkus_Entity_Post */
        $post = $args['post'];
        $subject = $post->isFirst() ? '' : 'Re: ';
        $subject .= $post->getTopic()->getForum()->getName() . ' :: ' . $post->getTopic()->getTitle();
        /* @var $view Zikula_View */
        $view = Zikula_View::getInstance($this->getName());
        $view->assign('sitename', System::getVar('sitename'))->assign('parent_forum_name', $post->getTopic()->getForum()->getParent()->getName())->assign('name', $post->getTopic()->getForum()->getName())->assign('topic_subject', $post->getTopic()->getTitle())->assign('poster_name', $post->getPoster()->getUser()->getUname())->assign('topic_time_ml', DateUtil::formatDatetime($post->getTopic()->getTopic_time(), 'datetimebrief'))->assign('post_message', $post->getPost_text())->assign('topic_id', $post->getTopic_id())->assign('forum_id', $post->getTopic()->getForum()->getForum_id())->assign('topic_url', ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $post->getTopic_id()), null, 'dzk_quickreply', true))->assign('subscription_url', ModUtil::url('Dizkus', 'user', 'prefs', array(), null, null, true))->assign('base_url', System::getBaseUrl());
        $message = $view->fetch('mail/notifyuser.txt');
        $topicSubscriptions = $post->getTopic()->getSubscriptions()->toArray();
        $forumSubscriptions = $post->getTopic()->getForum()->getSubscriptions()->toArray();
        $subscriptions = array_merge($topicSubscriptions, $forumSubscriptions);
        // we do not want to notify the current poster
        $notified = array($post->getPoster_id());
        foreach ($subscriptions as $subscription) {
            // check permissions
            /* @var $subscriber Zikula\Module\UsersModule\Entity\UserEntity */
            $subscriber = $subscription->getForumUser()->getUser();
            $subscriberEmail = $subscriber->getEmail();
            if (in_array($subscriber->getUid(), $notified) || empty($subscriberEmail)) {
                continue;
            }
            if (SecurityUtil::checkPermission('Dizkus::', $post->getTopic()->getForum()->getParent()->getName() . ':' . $post->getTopic()->getForum()->getName() . ':', ACCESS_READ, $subscriber->getUid())) {
                $args = array('fromname' => System::getVar('sitename'), 'fromaddress' => $fromAddress, 'toname' => $subscriber->getUname(), 'toaddress' => $subscriberEmail, 'subject' => $subject, 'body' => $message, 'headers' => array('X-UserID: ' . md5(UserUtil::getVar('uid')), 'X-Mailer: Dizkus v' . $dizkusModuleInfo['version'], 'X-DizkusTopicID: ' . $post->getTopic_id()));
                ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $args);
                $notified[] = $subscriber->getUid();
            }
        }

        return $notified;
    }

    /**
     * notify moderators
     *
     * @params $args['post'] Dizkus_Entity_Post
     * @params $args['comment'] string
     * @returns void
     */
    public function notify_moderator($args)
    {
        setlocale(LC_TIME, System::getVar('locale'));
        $mods = ModUtil::apiFunc('Dizkus', 'moderators', 'get', array('forum_id' => $args['post']->getTopic()->getForum()->getForum_id()));
        // generate the mailheader
        $email_from = ModUtil::getVar('Dizkus', 'email_from');
        if ($email_from == '') {
            // nothing in forumwide-settings, use adminmail
            $email_from = System::getVar('adminmail');
        }
        $subject = DataUtil::formatForDisplay($this->__('Moderation request')) . ': ' . strip_tags($args['post']->getTopic()->getTitle());
        $sitename = System::getVar('sitename');
        $recipients = array();
        // using the uid as the key to the array avoids duplication
        // check if list is empty - then do nothing
        // we create an array of recipients here
        $notifyAdminAsMod = (int) $this->getVar('notifyAdminAsMod');
        $admin_is_mod = false;
        if (count($mods['groups']) > 0) {
            foreach (array_keys($mods['groups']) as $gid) {
                $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid' => $gid));
                if ($group != false) {
                    foreach ($group['members'] as $gm_uid) {
                        $mod_email = UserUtil::getVar('email', $gm_uid);
                        $mod_uname = UserUtil::getVar('uname', $gm_uid);
                        if (!empty($mod_email)) {
                            $recipients[$gm_uid] = array('uname' => $mod_uname, 'email' => $mod_email);
                        }
                        if ($gm_uid == $notifyAdminAsMod) {
                            // admin is also moderator
                            $admin_is_mod = true;
                        }
                    }
                }
            }
        }
        if (count($mods['users']) > 0) {
            foreach ($mods['users'] as $uid => $uname) {
                $mod_email = UserUtil::getVar('email', $uid);
                if (!empty($mod_email)) {
                    $recipients[$uid] = array('uname' => $uname, 'email' => $mod_email);
                }
                if ($uid == $notifyAdminAsMod) {
                    // admin is also moderator
                    $admin_is_mod = true;
                }
            }
        }
        // determine if we also notify an admin as a moderator
        if ($admin_is_mod == false && $notifyAdminAsMod > 1) {
            $recipients[$notifyAdminAsMod] = array('uname' => UserUtil::getVar('uname', $notifyAdminAsMod), 'email' => UserUtil::getVar('email', $notifyAdminAsMod));
        }
        $reporting_userid = UserUtil::getVar('uid');
        $reporting_username = UserUtil::getVar('uname');
        if (is_null($reporting_username)) {
            $reporting_username = $this->__('Guest');
        }
        $start = ModUtil::apiFunc('Mailer', 'user', 'getTopicPage', array('replyCount' => $args['post']->getTopic()->getReplyCount()));
        $linkToTopic = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $args['post']->getTopic_id(), 'start' => $start), null, 'pid' . $args['post']->getPost_id(), true);
        $posttext = $this->getVar('striptagsfromemail') ? strip_tags($args['post']->getPost_text()) : $args['post']->getPost_text();
        $message = $this->__f('Request for moderation on %s', System::getVar('sitename')) . '
' . $args['post']->getTopic()->getForum()->getName() . ' :: ' . $args['post']->getTopic()->getTitle() . '

' . $this->__('Reporting user') . ": {$reporting_username}\n" . $this->__('Comment') . ':
' . strip_tags($args['comment']) . '

' . $this->__('Post Content') . ':
' . '---------------------------------------------------------------------
' . $posttext . '
' . '---------------------------------------------------------------------

' . $this->__('Link to topic') . ": {$linkToTopic}\n" . '
';
        $modinfo = ModUtil::getInfoFromName('Dizkus');
        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array('fromname' => $sitename, 'fromaddress' => $email_from, 'toname' => $recipient['uname'], 'toaddress' => $recipient['email'], 'subject' => $subject, 'body' => $message, 'headers' => array('X-UserID: ' . $reporting_userid, 'X-Mailer: ' . $modinfo['name'] . ' ' . $modinfo['version'])));
            }
            LogUtil::registerStatus($this->__('The moderator has been contacted about this post. Thank you.'));
        } else {
            LogUtil::registerError($this->__('There were no moderators set to be notified. Consider manually contacting the site admin.'));
        }

        return;
    }

    /**
     * email
     *
     * @params $args['sendto_email'] string the recipients email address
     * @params $args['message'] string the text
     * @params $args['subject'] string the subject
     * @returns bool
     */
    public function email($args)
    {
        $sender_name = UserUtil::getVar('uname');
        $sender_email = UserUtil::getVar('email');
        if (!UserUtil::isLoggedIn()) {
            $sender_name = ModUtil::getVar('Users', 'anonymous');
            $sender_email = ModUtil::getVar('Dizkus', 'email_from');
        }
        $args2 = array('fromname' => $sender_name, 'fromaddress' => $sender_email, 'toname' => $args['sendto_email'], 'toaddress' => $args['sendto_email'], 'subject' => $args['subject'], 'body' => $args['message']);

        return ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $args2);
    }

}
