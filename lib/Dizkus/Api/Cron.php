<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
class Dizkus_Api_Cron extends Zikula_AbstractApi
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
        if (ModUtil::getVar('Dizkus', 'm2f_enabled') <> 'yes') {
            return;
        }

        $force = (isset($args['force'])) ? (boolean)$args['force'] : false;
        $managedForum = new Dizkus_Manager_Forum($args['forum']);
        $forum = $managedForum->get();

        include_once 'modules/Dizkus/lib/vendor/pop3.php';
        if ((($forum['pop3_active'] == 1) && ($forum['pop3_last_connect'] <= time() - ($forum['pop3_interval'] * 60)) ) || ($force == true)) {
            $this->mailcronecho('found active: ' . $forum['forum_id'] . ' = ' . $forum['forum_name'] . "\n", $args['debug']);
            // get new mails for this forum
            $pop3 = new pop3_class;
            $pop3->hostname = $forum['pop3_server'];
            $pop3->port = $forum['pop3_port'];
            $error = '';

            // open connection to pop3 server
            if (($error = $pop3->Open()) == '') {
                $this->mailcronecho("Connected to the POP3 server '" . $pop3->hostname . "'.\n", $args['debug']);
                // login to pop3 server
                if (($error = $pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0)) == '') {
                    $this->mailcronecho("User '" . $forum['pop3_login'] . "' logged into POP3 server '" . $pop3->hostname . "'.\n", $args['debug']);
                    // check for message
                    if (($error = $pop3->Statistics($messages, $size)) == '') {
                        $this->mailcronecho("There are $messages messages in the mailbox, amounting to a total of $size bytes.\n", $args['debug']);
                        // get message list...
                        $result = $pop3->ListMessages('', 1);
                        if (is_array($result) && count($result) > 0) {
                            // logout the currentuser
                            $this->mailcronecho("Logging out '" . UserUtil::getVar('uname') . "'.\n", $args['debug']);
                            UserUtil::logOut();
                            // login the correct user
                            if (UserUtil::logIn($forum['pop3_pnuser'], base64_decode($forum['pop3_pnpassword']), false)) {
                                $this->mailcronecho('Done! User ' . UserUtil::getVar('uname') . ' successfully logged in.', $args['debug']);
                                if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $forum)) {
                                    $this->mailcronecho("Error! Insufficient permissions for " . UserUtil::getVar('uname') . " in forum " . $forum['forum_name'] . "(id=" . $forum['forum_id'] . ").", $args['debug']);
                                    UserUtil::logOut();
                                    $this->mailcronecho('Done! User ' . UserUtil::getVar('uname') . ' logged out.', $args['debug']);
                                    return false;
                                }
                                $this->mailcronecho("Adding new posts as user '" . UserUtil::getVar('uname') . "'.\n", $args['debug']);
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
                                            if (empty($forum['pop3_matchstring']) || (preg_match($forum['pop3_matchstring'], $subject) <> 0)) {
                                                $message = '[code=htmlmail,user=' . $from . ']' . implode("\n", $body) . '[/code]';
                                                if (!empty($replyto)) {
                                                    // this seems to be a reply, we find the original posting
                                                    // and store this mail in the same thread
                                                    $topic = $this->entityManager->getRepository('Dizkus_Entity_Topic')->findOneBy(array('post_msgid', $replyto));
                                                    if (!isset($topic)) {
                                                        // msgid not found, we clear replyto to create a new topic
                                                        $replyto = '';
                                                    } else {
                                                        $topic_id = $topic->getTopic_id();
                                                        // topic_id found, add this posting as a reply there
                                                        list($start, $post_id ) = ModUtil::apiFunc('Dizkus', 'user', 'storereply', array('topic_id' => $topic_id,
                                                                    'message' => $message,
                                                                    'attach_signature' => 1,
                                                                    'subscribe_topic' => 0,
                                                                    'msgid' => $msgid));
                                                        $this->mailcronecho("added new post '$subject' (post=$post_id) to topic $topic_id\n", $args['debug']);
                                                    }
                                                }

                                                // check again for replyto and create a new topic
                                                if (empty($replyto)) {
                                                    // store message in forum
                                                    $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic', array('subject' => $subject,
                                                                'message' => $message,
                                                                'forum_id' => $forum['forum_id'],
                                                                'attach_signature' => 1,
                                                                'subscribe_topic' => 0,
                                                                'msgid' => $msgid));
                                                    $this->mailcronecho("Added new topic '$subject' (topic ID $topic_id) to '" . $forum['forum_name'] . "' forum.\n", $args['debug']);
                                                }
                                            } else {
                                                $this->mailcronecho("Warning! Message subject  line '$subject' does not match requirements and will be ignored.", $args['debug']);
                                            }
                                        } else {
                                            $this->mailcronecho("Warning! The message subject line '$subject' is a possible loop and will be ignored.", $args['debug']);
                                        }
                                        // mark message for deletion
                                        $pop3->DeleteMessage($cnt);
                                    }
                                }
                                // logout the mail2forum user
                                if (UserUtil::logOut()) {
                                    $this->mailcronecho('Done! User ' . $forum['pop3_pnuser'] . ' logged out.', $args['debug']);
                                }
                            } else {
                                $this->mailcronecho("Error! Could not log user '" . $forum['pop3_pnuser'] . "' in.\n");
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
                $this->mailcronecho("error: ", htmlspecialchars($error) . "\n");
            }

            // store the timestamp of the last connection to the database
            $managedForum = new Dizkus_Manager_Forum($forum['forum_id']);
            $managedForum->get()->setForum_pop3_lastconnect(time());
            $this->entityManager->flush();
        }

        return;
    }

    /**
     * mailcronecho
     */
    private function mailcronecho($text, $debug)
    {
        echo $text;
        if ($debug==true) {
            echo '<br />';
        }
        flush();
        return;
    }

    /**
     * testpop3connection
     *
     * @params $args['forum_id'] int the id of the forum to test the pop3 connection
     * @returns array of messages from pop3 connection test
     *
     */
//    public function testpop3connection($args)
//    {
//        if (!isset($args['forum_id']) || !is_numeric($args['forum_id'])) {
//            return LogUtil::registerArgsError();
//        }
//
//        $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums', array('forum_id' => $args['forum_id']));
//        Loader::includeOnce('modules/Dizkus/includes/pop3.php');
//
//        $pop3 = new pop3_class;
//        $pop3->hostname = $forum['pop3_server'];
//        $pop3->port = $forum['pop3_port'];
//
//        $error = '';
//        $pop3messages = array();
//        if (($error = $pop3->Open()) == '') {
//            $pop3messages[] = "connected to the POP3 server '" . $pop3->hostname . "'";
//            if (($error = $pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0)) == '') {
//                $pop3messages[] = "user '" . $forum['pop3_login'] . "' logged in";
//                if (($error = $pop3->Statistics($messages, $size)) == '') {
//                    $pop3messages[] = "There are $messages messages in the mailbox, amounting to a total of $size bytes.";
//                    $result = $pop3->ListMessages('', 1);
//                    if (is_array($result) && count($result) > 0) {
//                        for ($cnt = 1; $cnt <= count($result); $cnt++) {
//                            if (($error = $pop3->RetrieveMessage($cnt, $headers, $body, -1)) == '') {
//                                foreach ($headers as $header) {
//                                    if (strpos(strtolower($header), 'subject:') === 0) {
//                                        $subject = trim(strip_tags(substr($header, 8)));
//                                    }
//                                }
//                            }
//                        }
//                        if ($error == '' && ($error = $pop3->Close()) == '') {
//                            $pop3messages[] = "Disconnected from POP3 server '" . $pop3->hostname . "'.\n";
//                        }
//                    } else {
//                        $error = $result;
//                    }
//                }
//            }
//        }
//        if (!empty($error)) {
//            $pop3messages[] = 'error: ' . htmlspecialchars($error);
//        }
//
//        return $pop3messages;
//    }
}
