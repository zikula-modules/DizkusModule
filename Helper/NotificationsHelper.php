<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Helper;

use DataUtil;
use DateUtil;
use Doctrine\ORM\EntityManager;
use ModUtil;
use SecurityUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use System;
use UserUtil;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula_View;

/**
 * NotificationHelper
 *
 * @todo
 *
 * @author Kaik
 */
class NotificationsHelper
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CurrentUserApi
     */
    private $userApi;

    public function __construct(
            RequestStack $requestStack,
            EntityManager $entityManager,
            CurrentUserApi $userApi
         ) {
        $this->name = 'ZikulaDizkusModule';
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->userApi = $userApi;
    }

    /**
     * Notify Subscribers by e-mail.
     *
     * Sending notify e-mail to users subscribed to the topic or the forum
     *
     * @param $args['post'] Zikula\Module\DizkusModule\Entity\PostEntity
     *
     * @return bool
     */
    public function emailSubscribers($args)
    {
        setlocale(LC_TIME, System::getVar('locale'));
        $dizkusModuleInfo = ModUtil::getInfoFromName($this->name);
        $dizkusFrom = ModUtil::getVar($this->name, 'email_from');
        $fromAddress = !empty($dizkusFrom) ? $dizkusFrom : System::getVar('adminmail');
        /* @var $post \Zikula\DizkusModule\Entity\PostEntity */
        $post = $args['post'];
        $subject = $post->isFirst() ? '' : 'Re: ';
        $subject .= $post->getTopic()->getForum()->getName().' :: '.$post->getTopic()->getTitle();
        /* @var $view Zikula_View */
        $view = Zikula_View::getInstance($this->getName());
        $poster = $post->getPoster()->getUser();
        $view->assign('sitename', System::getVar('sitename'))
            ->assign('parent_forum_name', $post->getTopic()->getForum()->getParent()->getName())
            ->assign('name', $post->getTopic()->getForum()->getName())
            ->assign('topic_subject', $post->getTopic()->getTitle())
            ->assign('poster_name', $poster['uname'])
            ->assign('topic_time_ml', DateUtil::formatDatetime($post->getTopic()->getTopic_time(), 'datetimebrief'))
            ->assign('post_message', $post->getPost_text())->assign('topic_id', $post->getTopic_id())
            ->assign('forum_id', $post->getTopic()->getForum()->getForum_id())
            ->assign('topic_url', $this->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $post->getTopic_id()], RouterInterface::ABSOLUTE_URL).'#dzk_quickreply')
            ->assign('subscription_url', $this->get('router')->generate('zikuladizkusmodule_user_prefs', [], RouterInterface::ABSOLUTE_URL))
            ->assign('base_url', $view->getRequest()->getBaseUrl());
        $message = $view->fetch('Mail/notifyuser.txt');
        $topicSubscriptions = $post->getTopic()->getSubscriptions()->toArray();
        $forumSubscriptions = $post->getTopic()->getForum()->getSubscriptions()->toArray();
        $subscriptions = array_merge($topicSubscriptions, $forumSubscriptions);
        // we do not want to notify the current poster
        $notified = [$post->getPoster_id()];
        foreach ($subscriptions as $subscription) {
            // check permissions
            $subscriber = $subscription->getForumUser()->getUser();
            if (in_array($subscriber['uid'], $notified) || empty($subscriber['email'])) {
                continue;
            }
            if (SecurityUtil::checkPermission($this->name.'::', $post->getTopic()->getForum()->getParent()->getName().':'.$post->getTopic()->getForum()->getName().':', ACCESS_READ, $subscriber['uid'])) {
                $args = [
                    'fromname'    => System::getVar('sitename'),
                    'fromaddress' => $fromAddress,
                    'toname'      => $subscriber['uname'],
                    'toaddress'   => $subscriber['email'],
                    'subject'     => $subject,
                    'body'        => $message,
                    'headers'     => [
                        'X-UserID: '.md5(UserUtil::getVar('uid')),
                        'X-Mailer: Dizkus v'.$dizkusModuleInfo['version'],
                        'X-DizkusTopicID: '.$post->getTopic_id(), ], ];

                try {
                    $isNotified = ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendmessage', $args);
                } catch (\Exception $e) {
                    $isNotified = false;
                }
                if ($isNotified) {
                    $notified[] = $subscriber['uid'];
                }
            }
        }

        return $notified;
    }

    /**
     * notify moderators.
     *
     * @param $args['post'] Zikula\Module\DizkusModule\Entity\PostEntity
     * @param $args['comment'] string
     *
     * @return void
     */
    public function notify_moderator($args)
    {
        setlocale(LC_TIME, System::getVar('locale'));
        $mods = ModUtil::apiFunc($this->name, 'moderators', 'get', ['forum_id' => $args['post']->getTopic()->getForum()->getForum_id()]);
        // generate the mailheader
        $email_from = ModUtil::getVar($this->name, 'email_from');
        if ('' == $email_from) {
            // nothing in forumwide-settings, use adminmail
            $email_from = System::getVar('adminmail');
        }
        $subject = DataUtil::formatForDisplay($this->__('Moderation request')).': '.strip_tags($args['post']->getTopic()->getTitle());
        $sitename = System::getVar('sitename');
        $recipients = [];
        // using the uid as the key to the array avoids duplication
        // check if list is empty - then do nothing
        // we create an array of recipients here
        $notifyAdminAsMod = (int) $this->getVar('notifyAdminAsMod');
        $admin_is_mod = false;
        if (count($mods['groups']) > 0) {
            foreach (array_keys($mods['groups']) as $gid) {
                $group = ModUtil::apiFunc('Groups', 'user', 'get', ['gid' => $gid]);
                if (false != $group) {
                    foreach ($group['members'] as $gm_uid) {
                        $mod_email = UserUtil::getVar('email', $gm_uid);
                        $mod_uname = UserUtil::getVar('uname', $gm_uid);
                        if (!empty($mod_email)) {
                            $recipients[$gm_uid] = [
                                'uname' => $mod_uname,
                                'email' => $mod_email, ];
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
                    $recipients[$uid] = [
                        'uname' => $uname,
                        'email' => $mod_email, ];
                }
                if ($uid == $notifyAdminAsMod) {
                    // admin is also moderator
                    $admin_is_mod = true;
                }
            }
        }
        // determine if we also notify an admin as a moderator
        if (false == $admin_is_mod && $notifyAdminAsMod > 1) {
            $recipients[$notifyAdminAsMod] = [
                'uname' => UserUtil::getVar('uname', $notifyAdminAsMod),
                'email' => UserUtil::getVar('email', $notifyAdminAsMod), ];
        }
        $reporting_userid = UserUtil::getVar('uid');
        $reporting_username = UserUtil::getVar('uname');
        if (is_null($reporting_username)) {
            $reporting_username = $this->__('Guest');
        }
        $start = ModUtil::apiFunc('Mailer', 'user', 'getTopicPage', ['replyCount' => $args['post']->getTopic()->getReplyCount()]);
        $linkToTopic = $this->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $args['post']->getTopic_id(), 'start' => $start], RouterInterface::ABSOLUTE_URL)."#pid{$args['post']->getPost_id()}";
        $posttext = $this->getVar('striptagsfromemail') ? strip_tags($args['post']->getPost_text()) : $args['post']->getPost_text();
        $message = $this->__f('Request for moderation on %s', System::getVar('sitename')).'
'.$args['post']->getTopic()->getForum()->getName().' :: '.$args['post']->getTopic()->getTitle().'

'.$this->__('Reporting user').": {$reporting_username}\n".$this->__('Comment').':
'.strip_tags($args['comment']).'

'.$this->__('Post Content').':
'.'---------------------------------------------------------------------
'.$posttext.'
'.'---------------------------------------------------------------------

'.$this->__('Link to topic').": {$linkToTopic}\n".'
';
        $modinfo = ModUtil::getInfoFromName($this->name);
        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                ModUtil::apiFunc('Mailer', 'user', 'sendmessage', [
                    'fromname'    => $sitename,
                    'fromaddress' => $email_from,
                    'toname'      => $recipient['uname'],
                    'toaddress'   => $recipient['email'],
                    'subject'     => $subject,
                    'body'        => $message,
                    'headers'     => [
                        'X-UserID: '.$reporting_userid,
                        'X-Mailer: '.$modinfo['name'].' '.$modinfo['version'], ], ]);
            }
            $this->request->getSession()->getFlashBag()->add('status', $this->__('The moderator has been contacted about this post. Thank you.'));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('There were no moderators set to be notified. Consider manually contacting the site admin.'));
        }
    }

    /**
     * email.
     *
     * @param $args['sendto_email'] string the recipients email address
     * @param $args['message'] string the text
     * @param $args['subject'] string the subject
     *
     * @return bool
     */
    public function email($args)
    {
        $sender_name = UserUtil::getVar('uname');
        $sender_email = UserUtil::getVar('email');
        if (!UserUtil::isLoggedIn()) {
            $sender_name = ModUtil::getVar('Users', 'anonymous');
            $sender_email = ModUtil::getVar($this->name, 'email_from');
        }
        $args2 = [
            'fromname'    => $sender_name,
            'fromaddress' => $sender_email,
            'toname'      => $args['sendto_email'],
            'toaddress'   => $args['sendto_email'],
            'subject'     => $args['subject'],
            'body'        => $args['message'], ];

        return ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $args2);
    }

    /*
     * email
     *
     * This functions emails a topic to a given email address.
     *
     * @param array $args Arguments array.
     *        string $args['sendto_email'] The recipients email address.
     *        string $args['message'] The text.
     *        string $args['subject'] The subject.
     *
     * @return boolean
     */
//    public function email($sendto_email, $subject, $message)
//    {
//
//
//
//        $sender_name = UserUtil::getVar('uname');
//        $sender_email = UserUtil::getVar('email');
//        if (!UserUtil::isLoggedIn()) {
//            $sender_name = ModUtil::getVar('Users', 'anonymous');
//            $sender_email = ModUtil::getVar($this->name, 'email_from');
//        }
//        $params = [
//            'fromname' => $sender_name,
//            'fromaddress' => $sender_email,
//            'toname' => $args['sendto_email'],
//            'toaddress' => $args['sendto_email'],
//            'subject' => $args['subject'],
//            'body' => $args['message']];
//
//        return ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $params);
//    }
}
