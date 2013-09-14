<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Api;

use ModUtil;
use UserUtil;
use DataUtil;
use Zikula\Module\DizkusModule\Manager\ForumManager;
use pop3_class;

class CronApi extends \Zikula_AbstractApi
{

    /**
     * cron
     *
     * @params $args['forum'] integer
     * @params $args['force'] boolean if true force connection no matter of active setting or interval
     * @params $args['debug'] boolean indicates debug mode on/off
     * @returns void
     */
    public function mail($args)
    {
        if (ModUtil::getVar($this->name, 'm2f_enabled') != 'yes') {
            return;
        }
        $force = isset($args['force']) ? (bool)$args['force'] : false;
        $managedForum = new ForumManager($args['forum']);
        $forum = $managedForum->get();
        $pop3conn = $forum->getPop3Connection()->getConnection();
        // array of connection details
        include_once 'modules/Dizkus/lib/vendor/pop3class/pop3.php';
        if ($forum->getPop3Connection()->isActive() && $pop3conn['last_connect'] <= time() - $pop3conn['interval'] * 60 || $force == true) {
            $this->mailcronecho('found active: ' . $forum['forum_id'] . ' = ' . $forum['name'] . '\n', $args['debug']);
            // get new mails for this forum
            $pop3 = new pop3_class();
            $pop3->hostname = $pop3conn['server'];
            $pop3->port = $pop3conn['port'];
            $error = '';
            // open connection to pop3 server
            if (($error = $pop3->Open()) == '') {
                $this->mailcronecho('Connected to the POP3 server \'' . $pop3->hostname . "'.\n", $args['debug']);
                // login to pop3 server
                if (($error = $pop3->Login($pop3conn['login'], base64_decode($pop3conn['password']), 0)) == '') {
                    $this->mailcronecho('User \'' . $pop3conn['login'] . '\' logged into POP3 server \'' . $pop3->hostname . "'.\n", $args['debug']);
                    // check for message
                    if (($error = $pop3->Statistics($messages, $size)) == '') {
                        $this->mailcronecho("There are {$messages} messages in the mailbox, amounting to a total of {$size} bytes.\n", $args['debug']);
                        // get message list...
                        $result = $pop3->ListMessages('', 1);
                        if (is_array($result) && count($result) > 0) {
                            // logout the currentuser
                            $this->mailcronecho('Logging out \'' . UserUtil::getVar('uname') . "'.\n", $args['debug']);
                            UserUtil::logOut();
                            // login the correct user
                            if (UserUtil::logIn($pop3conn['coreUser']->getUid(), base64_decode($pop3conn['coreUser']->getPass()), false)) {
                                $this->mailcronecho('Done! User ' . $pop3conn['coreUser']->getUname() . ' successfully logged in.', $args['debug']);
                                if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $forum)) {
                                    $this->mailcronecho('Error! Insufficient permissions for ' . $pop3conn['coreUser']->getUname() . ' in forum ' . $forum['name'] . '(id=' . $forum['forum_id'] . ').', $args['debug']);
                                    UserUtil::logOut();
                                    $this->mailcronecho('Done! User ' . $pop3conn['coreUser']->getUname() . ' logged out.', $args['debug']);

                                    return false;
                                }
                                $this->mailcronecho('Adding new posts as user \'' . $pop3conn['coreUser']->getUname() . "'.\n", $args['debug']);
                                // .cycle through the message list
                                for ($cnt = 1; $cnt <= count($result); $cnt++) {
                                    if (($error = $pop3->RetrieveMessage($cnt, $headers, $body, -1)) == '') {
                                        // echo "Message $i:\n---Message headers starts below---\n";
                                        $subject = '';
                                        $from = '';
                                        $msgid = '';
                                        $replyto = '';
                                        $original_topic_id = '';
                                        foreach ($headers as $header) {
                                            //echo htmlspecialchars($header),"\n";
                                            // get subject
                                            $header = strtolower($header);
                                            if (strpos(strtolower($header), 'subject:') === 0) {
                                                $subject = trim(strip_tags(substr($header, 8)));
                                            }
                                            // get sender
                                            if (strpos($header, 'from:') === 0) {
                                                $from = trim(strip_tags(substr($header, 5)));
                                                // replace @ and . to make it harder for email harvesers,
                                                // credits to Teb for this idea
                                                $from = str_replace(array('@', '.'), array(' (at) ', ' (dot) '), $from);
                                            }
                                            // get msgid from In-Reply-To: if this is an nswer to a prior
                                            // posting
                                            if (strpos($header, 'in-reply-to:') === 0) {
                                                $replyto = trim(strip_tags(substr($header, 12)));
                                            }
                                            // this msg id
                                            if (strpos($header, 'message-id:') === 0) {
                                                $msgid = trim(strip_tags(substr($header, 11)));
                                            }
                                            // check for X-DizkusTopicID, if set, then this is a possible
                                            // loop (mailinglist subscribed to the forum too)
                                            if (strpos($header, 'X-DizkusTopicID:') === 0) {
                                                $original_topic_id = trim(strip_tags(substr($header, 17)));
                                            }
                                        }
                                        if (empty($subject)) {
                                            $subject = DataUtil::formatForDisplay($this->__('Error! The post has no subject line.'));
                                        }
                                        // check if subject matches our matchstring
                                        if (empty($original_topic_id)) {
                                            if (empty($pop3conn['matchstring']) || preg_match($pop3conn['matchstring'], $subject) != 0) {
                                                $message = '[code=htmlmail,user=' . $from . ']' . implode("\n", $body) . '[/code]';
                                                if (!empty($replyto)) {
                                                    // this seems to be a reply, we find the original posting
                                                    // and store this mail in the same thread
                                                    $topic = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->findOneBy(array('msgid', $replyto));
                                                    if (!isset($topic)) {
                                                        // msgid not found, we clear replyto to create a new topic
                                                        $replyto = '';
                                                    } else {
                                                        $topic_id = $topic->getTopic_id();
                                                        // topic_id found, add this posting as a reply there
                                                        list($start, $post_id) = ModUtil::apiFunc($this->name, 'user', 'storereply', array(
                                                                    'topic_id' => $topic_id,
                                                                    'message' => $message,
                                                                    'attach_signature' => 1,
                                                                    'subscribe_topic' => 0,
                                                                    'msgid' => $msgid));
                                                        $this->mailcronecho("added new post '{$subject}' (post={$post_id}) to topic {$topic_id}\n", $args['debug']);
                                                    }
                                                }
                                                // check again for replyto and create a new topic
                                                if (empty($replyto)) {
                                                    // store message in forum
                                                    $topic_id = ModUtil::apiFunc($this->name, 'user', 'storenewtopic', array(
                                                                'subject' => $subject,
                                                                'message' => $message,
                                                                'forum_id' => $forum['forum_id'],
                                                                'attach_signature' => 1,
                                                                'subscribe_topic' => 0,
                                                                'msgid' => $msgid));
                                                    $this->mailcronecho("Added new topic '{$subject}' (topic ID {$topic_id}) to '" . $forum['name'] . "' forum.\n", $args['debug']);
                                                }
                                            } else {
                                                $this->mailcronecho("Warning! Message subject  line '{$subject}' does not match requirements and will be ignored.", $args['debug']);
                                            }
                                        } else {
                                            $this->mailcronecho("Warning! The message subject line '{$subject}' is a possible loop and will be ignored.", $args['debug']);
                                        }
                                        // mark message for deletion
                                        $pop3->DeleteMessage($cnt);
                                    }
                                }
                                // logout the mail2forum user
                                if (UserUtil::logOut()) {
                                    $this->mailcronecho('Done! User ' . $pop3conn['coreUser']->getUname() . ' logged out.', $args['debug']);
                                }
                            } else {
                                $this->mailcronecho("Error! Could not log user '" . $pop3conn['coreUser']->getUname() . "' in.\n");
                            }
                            // close pop3 connection and finally delete messages
                            if ($error == '' && ($error = $pop3->Close()) == '') {
                                $this->mailcronecho("Disconnected from POP3 server '" . $pop3->hostname . "'.\n");
                            }
                        } else {
                            $error = $result;
                        }
                    }
                }
            }
            if (!empty($error)) {
                $this->mailcronecho('error: ', htmlspecialchars($error) . "\n");
            }
            // store the timestamp of the last connection to the database
            $managedForum->get()->getPop3Connection()->updateConnectTime();
            $this->entityManager->flush();
        }

        return;
    }

    /**
     * mailcronecho
     */
    private function mailcronecho($text, $debug = false)
    {
        echo $text;
        if ($debug == true) {
            echo '<br />';
        }
        flush();

        return;
    }

}
