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
 * This class provides the post api functions
 */
class Dizkus_Api_Post extends Zikula_AbstractApi {

    /**
     * Check if this is the first post in a topic.
     *
     * @param array $args The argument array.
     *        int $args['topic_id'] The topics id.
     *        int $args['post_id'] The postings id.
     *
     * @return boolean
     */
    public function isFirst($args)
    {
        // compare the given post_id with the lowest post_id in the topic
        $minpost = ModUtil::apiFunc('Dizkus', 'user', 'get_firstlast_post_in_topic',
            array('topic_id' => $args['topic_id'],
                'first'    => true,
                'id_only'  => true
            )
        );

        return ($minpost == $args['post_id']) ? true : false;
    }



    /**
     * read
     *
     * Reads a single posting
     *
     * @param int $post_id The postings id.
     *
     * @return array with posting information.
     *
     */
    public function read($post_id)
    {
        $ztable = DBUtil::getTables();
        $postscols = DBUtil::_getAllColumnsQualified('dizkus_posts', 'p');

        $sql = 'SELECT '. $postscols .',
                          t.topic_title,
                          t.topic_replies,
                          f.forum_name,
                          c.cat_title,
                          c.cat_id
                FROM '.$ztable['dizkus_posts'].' p
                LEFT JOIN '.$ztable['dizkus_topics'].' t ON t.topic_id = p.topic_id
                LEFT JOIN '.$ztable['dizkus_forums'].' f ON f.forum_id = t.forum_id
                LEFT JOIN '.$ztable['dizkus_categories'].' c ON c.cat_id = f.cat_id
                WHERE p.post_id = '.(int)DataUtil::formatForStore($post_id);

        $result = DBUtil::executeSQL($sql);
        if ($result === false) {
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }

        $colarray   = DBUtil::getColumnsArray ('dizkus_posts');
        $colarray[] = 'topic_title';
        $colarray[] = 'topic_replies';
        $colarray[] = 'forum_name';
        $colarray[] = 'cat_title';
        $colarray[] = 'cat_id';

        $objarray = DBUtil::marshallObjects ($result, $colarray);
        $post = $objarray[0];
        if (!allowedtoreadcategoryandforum($post['cat_id'], $post['forum_id'])) {
            return LogUtil::registerPermissionError();
        }

        $post['post_id']          = DataUtil::formatForDisplay($post['post_id']);
        $post['post_time']        = DataUtil::formatForDisplay($post['post_time']);
        $message                  = $post['post_text'];
        $post['has_signature']    = (substr($message, -8, 8)=='[addsig]');
        $post['post_rawtext']     = dzk_replacesignature($message, '');
        $post['post_rawtext']     = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $post['post_rawtext']);
        $post['post_rawtext']     = str_replace('<br />', '', $post['post_rawtext']);
        $post['topic_id']         = DataUtil::formatForDisplay($post['topic_id']);
        $post['topic_rawsubject'] = strip_tags($post['topic_title']);
        $post['topic_subject']    = DataUtil::formatForDisplay($post['topic_title']);
        $post['topic_replies']    = DataUtil::formatForDisplay($post['topic_replies']);
        $post['forum_id']         = DataUtil::formatForDisplay($post['forum_id']);
        $post['forum_name']       = DataUtil::formatForDisplay($post['forum_name']);
        $post['cat_title']        = DataUtil::formatForDisplay($post['cat_title']);
        $post['cat_id']           = DataUtil::formatForDisplay($post['cat_id']);
        $post['poster_data']      = ModUtil::apiFunc($this->name, 'user', 'get_userdata_from_id', array('userid' => $post['poster_id']));

        // create unix timestamp
        $post['post_unixtime'] = strtotime($post['post_time']);
        $post['posted_unixtime'] = $post['post_unixtime'];

        $pn_uid = UserUtil::getVar('uid');
        $post['moderate'] = false;
        if (allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
            $post['moderate'] = true;
        }

        $post['poster_data']['edit'] = false;
        $post['poster_data']['reply'] = false;
        $post['poster_data']['seeip'] = false;
        $post['poster_data']['moderate'] = false;

        if ($post['poster_data']['uid']==$pn_uid) {
            // user is allowed to moderate || own post
            $post['poster_data']['edit'] = true;
        }
        if (allowedtowritetocategoryandforum($post['cat_id'], $post['forum_id'])) {
            // user is allowed to reply
            $post['poster_data']['reply'] = true;
        }

        if (allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id']) &&
            ModUtil::getVar('Dizkus', 'log_ip') == 'yes') {
            // user is allowed to see ip
            $post['poster_data']['seeip'] = true;
        }
        if (allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
            // user is allowed to moderate
            $post['poster_data']['moderate'] = true;
            $post['poster_data']['edit'] = true;
        }

        $post['post_textdisplay'] = phpbb_br2nl($message);
        $post['post_textdisplay'] = dzk_replacesignature($post['post_textdisplay'], $post['poster_data']['signature']);

        // call hooks for $message_display ($message remains untouched for the textarea)
        // list($post['post_textdisplay']) = ModUtil::callHooks('item', 'transform', $post['post_id'], array($post['post_textdisplay']));
        $post['post_textdisplay'] = dzkVarPrepHTMLDisplay($post['post_textdisplay']);
        $post['post_text'] = $post['post_textdisplay'];

        // allow to edit the subject if first post
        $post['first_post'] = $this->isFirst(array('topic_id' => $post['topic_id'], 'post_id' => $post['post_id']));

        return $post;
    }

      /**
     * preparereply
     * prepare a reply to a posting by reading the last ten postign in revers order for review
     *
     * @params $args['topic_id'] int the topics id
     * @params $args['post_id'] int the post id to reply to
     * @params $args['quote'] bool if user wants to qupte or not (**not used**)
     * @params $args['last_visit'] string the users last visit data (**not used**)
     * @params $args['reply_start'] bool true if we start a new reply
     * @params $args['attach_signature'] int 1=attach signature, otherwise no
     * @params $args['subscribe_topic'] int =subscribe topic, otherwise no
     * @return array Very complex array, see {debug} for more information
     */
    public function preparereply($args)
    {
        $ztable = DBUtil::getTables();
    
        $reply = array();
    
        if ($args['post_id'] <> 0) {
            // We have a post id, so include that in the checks
            // create a reply with quote
            $sql = 'SELECT f.forum_id,
                           f.cat_id,
                           t.topic_id,
                           t.topic_title,
                           t.topic_status,
                           p.post_text,
                           p.post_time,
                           u.uname
                    FROM '.$ztable['dizkus_forums'].' AS f,
                         '.$ztable['dizkus_topics'].' AS t,
                         '.$ztable['dizkus_posts'].' AS p,
                         '.$ztable['users'].' AS u
                    WHERE (p.post_id = '.(int)DataUtil::formatForStore($args['post_id']).')
                    AND (t.forum_id = f.forum_id)
                    AND (p.topic_id = t.topic_id)
                    AND (p.poster_id = u.uid)';
            $colarray = array('forum_id', 'cat_id', 'topic_id', 'topic_title', 'topic_status', 'post_text', 'post_time', 'uname');
        } else {
            // No post id, just check topic.
            // reply without quote
            $sql = 'SELECT f.forum_id,
                           f.cat_id,
                           t.topic_id,
                           t.topic_title,
                           t.topic_status
                    FROM '.$ztable['dizkus_forums'].' AS f,
                         '.$ztable['dizkus_topics'].' AS t
                    WHERE (t.topic_id = '.(int)DataUtil::formatForStore($args['topic_id']).')
                    AND (t.forum_id = f.forum_id)';
            $colarray = array('forum_id', 'cat_id', 'topic_id', 'topic_title', 'topic_status');
        }
        $res = DBUtil::executeSQL($sql);
        $result = DBUtil::marshallObjects($res, $colarray);
    
        if (!is_array($result) || empty($result)) {
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        } else {
            $reply = $result[0];
        }
    
        $reply['topic_subject'] = DataUtil::formatForDisplay($reply['topic_title']);
        // the next line is only producing a valid result, if we get a post_id which
        // means we are producing a reply with quote
        if (array_key_exists('post_text', $reply)) {
            $text = Dizkus_bbdecode($reply['post_text']);
            $text = preg_replace('/(<br[ \/]*?>)/i', '', $text);
            // just for backwards compatibility
            $text = Dizkus_undo_make_clickable($text);
            $text = str_replace('[addsig]', '', $text);
            $reply['message'] = '[quote='.$reply['uname'].']'.trim($text).'[/quote]';
        } else {
            $reply['message'] = '';
        }
    
        // anonymous user has uid=0, but needs uid=1
        // also check subscription status here
        if (!UserUtil::isLoggedIn()) {
            $pn_uid = 1;
            $reply['attach_signature'] = false;
            $reply['subscribe_topic'] = false;
        } else {
            $pn_uid = UserUtil::getVar('uid');
            // get the users topic_subscription status to show it in the quick repliy checkbox
            // correctly
            if ($args['reply_start']==true) {
                $reply['attach_signature'] = true;
                $reply['subscribe_topic'] = false;
                $is_subscribed = ModUtil::apiFunc($this->name, 'topic', 'get_topic_subscription_status',array('user_id'   => $pn_uid,
                                                                            'topic_id' => $reply['topic_id']));
    
                if ($is_subscribed == true || ModUtil::getVar('Dizkus', 'autosubscribe') == 'yes') {
                    $reply['subscribe_topic'] = true;
                } else {
                    $reply['subscribe_topic'] = false;
                }
            } else {
                $reply['attach_signature'] = $args['attach_signature'];
                $reply['subscribe_topic'] = $args['subscribe_topic'];
            }
        }
        $reply['poster_data'] = ModUtil::apiFunc($this->name, 'user', 'get_userdata_from_id', array('userid' => $pn_uid));
    
        if ($reply['topic_status'] == 1) {
            return LogUtil::registerError($this->__('Error! You cannot post a message under this topic. It has been locked.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        if (!allowedtowritetocategoryandforum($reply['cat_id'], $reply['forum_id'])) {
            return LogUtil::registerPermissionError();
        }
    
        // Topic review (show last 10)
        $sql = 'SELECT p.post_id,
                       p.poster_id,
                       p.post_time,
                       p.post_text,
                       t.topic_title
                FROM '.$ztable['dizkus_posts'].' p
                LEFT JOIN '.$ztable['dizkus_topics'].' t ON t.topic_id=p.topic_id
                WHERE p.topic_id = ' . (int)DataUtil::formatForStore($reply['topic_id']) . ' 
                ORDER BY p.post_id DESC';
    
        $res = DBUtil::executeSQL($sql, -1, 10);
        $colarray = array('post_id', 'poster_id', 'post_time', 'post_text', 'topic_title');
        $result    = DBUtil::marshallObjects($res, $colarray);
    
        $reply['topic_review'] = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $review) {
                $review['user_name'] = UserUtil::getVar('uname', $review['poster_id']);
                if ($review['user_name'] == '') {
                    // user deleted from the db?
                    $review['poster_id'] = 1;
                }
            
                $review['poster_data'] = ModUtil::apiFunc($this->name, 'user', 'get_userdata_from_id', array('userid' => $post['poster_id']));
            
                // TODO extract unixtime directly from MySql
                $review['post_unixtime'] = strtotime($review['post_time']);
                $review['post_ml'] = DateUtil::formatDatetime($review['post_unixtime'], 'datetimebrief');
            
                $message = $review['post_text'];
                // we use br2nl here for backward compatibility
                $message = phpbb_br2nl($message);
                // Before we insert the sig, we have to strip its HTML if HTML is disabled by the admin.
            
                // We do this _before_ bbencode(), otherwise we'd kill the bbcode's html.
                $message = dzk_replacesignature($message, $review['poster_data']['signature']);
            
                // call hooks for $message
                // list($message) = ModUtil::callHooks('item', 'transform', $review['post_id'], array($message));
                $review['post_text'] = $message;
            
                array_push($reply['topic_review'], $review);
            }
        }
    
        return $reply;
    }
    
    /**
     * storereply
     * store the users reply in the database
     *
     * @params $args['message'] string the text
     * @params $args['title'] string the posting title
     * @params $args['topic_id'] int the topics id
     * @params $args['forum_id'] int the forums id
     * @params $args['attach_signature'] int 1=yes, otherwise no
     * @params $args['subscribe_topic'] int 1=yes, otherwise no
     * @returns array(int, int) start parameter and new post_id
     */
    public function storereply($args)
    {
        list($forum_id, $cat_id) = ModUtil::apiFunc('Dizkus', 'topic', 'get_forumid_and_categoryid_from_topicid',$args['topic_id']);
    
        if (!allowedtowritetocategoryandforum($cat_id, $forum_id)) {
            return LogUtil::registerPermissionError();
        }
        
        if ($this->isSpam($args['message'])) {
            return LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
        }
    
        if (trim($args['message']) == '') {
            return LogUtil::registerError($this->__('Error! You tried to post a blank message. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        /*
        it's a submitted page and message is not empty
        */
    
        // grab message for notification
        // without html-specialchars, bbcode, smilies <br> and [addsig]
        $posted_message = stripslashes($args['message']);
    
        // signature is always on, except anonymous user
        // anonymous user has uid=0, but needs uid=1
        $islogged = UserUtil::isLoggedIn();
        if ($islogged) {
            if ($args['attach_signature'] == 1) {
                $args['message'] .= '[addsig]';
            }
            $pn_uid = UserUtil::getVar('uid');
        } else {
            $pn_uid = 1;
        }
        
        if (ModUtil::getVar('Dizkus', 'log_ip') == 'no') {
            // for privacy issues ip logging can be deactivated
            $poster_ip = '127.0.0.1';
        } else {
            // some enviroment for logging ;)
            if (System::serverGetVar('HTTP_X_FORWARDED_FOR')) {
                $poster_ip = System::serverGetVar('REMOTE_ADDR')."/".System::serverGetVar('HTTP_X_FORWARDED_FOR');
            } else {
                $poster_ip = System::serverGetVar('REMOTE_ADDR');
             }
        }

        // Prep for DB is done by DBUtil
        $obj['post_time']  = date('Y-m-d H:i:s');
        $obj['topic_id']   = $args['topic_id'];
        $obj['forum_id']   = $forum_id;
        $obj['post_text']  = $args['message'];
        $obj['poster_id']  = $pn_uid;
        $obj['poster_ip']  = $poster_ip;
        $obj['post_title'] = $args['title'];
    
        DBUtil::insertObject($obj, 'dizkus_posts', 'post_id');
    
        // update topics table
        $tobj['topic_last_post_id'] = $obj['post_id'];
        $tobj['topic_time']         = $obj['post_time'];
        $tobj['topic_id']           = $obj['topic_id'];
        DBUtil::updateObject($tobj, 'dizkus_topics', null, 'topic_id');
        DBUtil::incrementObjectFieldByID('dizkus_topics', 'topic_replies', $obj['topic_id'], 'topic_id');
    
        if ($islogged) {
            // user logged in we have to update users attributes
            UserUtil::setVar('dizkus_user_posts', UserUtil::getVar('dizkus_user_posts') + 1);
            //DBUtil::incrementObjectFieldByID('dizkus__users', 'user_posts', $obj['poster_id'], 'user_id');
    
            // update subscription
            if ($args['subscribe_topic']==1) {
                // user wants to subscribe the topic
               ModUtil::apiFunc('Dizkus', 'topic', 'subscribe',array('topic_id' => $obj['topic_id']));
            } else {
                // user does not want to subscribe the topic
                ModUtil::apiFunc('Dizkus', 'topic', 'unsubscribe',array('topic_id' => $obj['topic_id']));
            }
        }
    
        // update forums table
        $fobj['forum_last_post_id'] = $obj['post_id'];
        $fobj['forum_id']           = $obj['forum_id'];
        DBUtil::updateObject($fobj, 'dizkus_forums', null, 'forum_id');
        DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_posts', $obj['forum_id'], 'forum_id');
    
        // get the last topic page
         $start = ModUtil::apiFunc('Dizkus', 'topic', 'get_last_topic_page',$obj['topic_id']);
    
        // Let any hooks know that we have created a new item.
        //ModUtil::callHooks('item', 'create', $this_post, array('module' => 'Dizkus'));
//        ModUtil::callHooks('item', 'update', $obj['topic_id'], array('module' => 'Dizkus',
//                                                          'post_id' => $obj['post_id']));
    
        ModUtil::apiFunc('Dizkus', 'user', 'notify_by_email',array('topic_id' => $obj['topic_id'], 'poster_id' => $obj['poster_id'], 'post_message' => $posted_message, 'type' => '2'));
    
        return array($start, $obj['post_id']);
    }

    
    /**
     * Update post
     *
     * Updates a posting in the db after editing it.
     *
     * @param array $args The arguments array.
     *        int $args['post_id'] The postings id.
     *        int $args['topic_id'] The topic id (might be empty!!!).
     *        string $args['subject'] The subject.
     *        string $args['message'] The text.
     *        boolean $args['delete'] True if the posting is to be deleted.
     *        boolean $args['attach_signature'] True if the addsig place holder has to be appended.
     *
     * @return string url to redirect to after action (topic of forum if the (last) posting has been deleted)
     */
    public function updatepost($args)
    {
        if (!isset($args['topic_id']) || empty($args['topic_id']) || !is_numeric($args['topic_id'])) {
            $args['topic_id'] = ModUtil::apiFunc('Dizkus', 'topic', 'get_topicid_by_postid', $args['post_id']);
        }
        
        if ($this->isSpam($args['message'])) {
            return LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
        }
    
        $ztable = DBUtil::getTables();
    
        $sql = "SELECT p.poster_id,
                       p.forum_id,
                       t.topic_status,
                       f.cat_id
                FROM  ".$ztable['dizkus_posts']." as p,
                      ".$ztable['dizkus_topics']." as t,
                      ".$ztable['dizkus_forums']." as f
                WHERE (p.post_id = '".(int)DataUtil::formatForStore($args['post_id'])."')
                  AND (t.topic_id = p.topic_id)
                  AND (f.forum_id = p.forum_id)";

        $res = DBUtil::executeSQL($sql);
        $colarray = array('poster_id', 'forum_id', 'topic_status', 'cat_id');
        $result = DBUtil::marshallObjects($res, $colarray);
        $row = $result[0];




        if (!is_array($row)) {
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        if (trim($args['message']) == '') {
            // no message
            return LogUtil::registerError($this->__('Error! You tried to post a blank message. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        if ((($row['poster_id'] != UserUtil::getVar('uid')) || ($row['topic_status'] == 1)) &&
            !allowedtomoderatecategoryandforum($row['cat_id'], $row['forum_id'])) {
            // user is not allowed to edit post
            return LogUtil::registerPermissionError();
        }
    
    
        if (empty($args['delete'])) {
    
            // update the posting
            if (!allowedtoadmincategoryandforum($row['cat_id'], $row['forum_id'])) {
                // if not admin then add a edited by line
                // If it's been edited more than once, there might be old "edited by" strings with
                // escaped HTML code in them. We want to fix this up right here:
                $args['message'] = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $args['message']);
                // who is editing?
                $edit_name  = UserUtil::isLoggedIn() ? UserUtil::getVar('uname') : ModUtil::getVar('Users', 'anonymous');
                $edit_date = DateUtil::formatDatetime('', 'datetimebrief');
                $args['message'] .= '<br /><br /><!-- editby --><br /><br /><em>' . $this->__f('Edited by %1$s on %2$s.', array($edit_name, $edit_date)) . '</em><!-- end editby --> ';
            }
    
            // add signature placeholder
            if ($row['poster_id'] <> 1 && $args['attach_signature'] == true) {
                $args['message'] .= '[addsig]';
            }
    
            $updatepost = array('post_id'   => $args['post_id'],
                                'post_text' => $args['message']);
            DBUtil::updateObject($updatepost, 'dizkus_posts', null, 'post_id');
    
            if (trim($args['subject']) != '') {
                //  topic has a new subject
                $updatetopic = array('topic_id'    => $args['topic_id'],
                                     'topic_title' => $args['subject']);
                DBUtil::updateObject($updatetopic, 'dizkus_topics', null, 'topic_id');
            }
    
            // Let any hooks know that we have updated an item.
            // ModUtil::callHooks('item', 'update', $post_id, array('module' => 'Dizkus'));
            // ModUtil::callHooks('item', 'update', $args['post_id'], array('module'  => 'Dizkus',
            // 'post_id' => $args['post_id']));
    
            // update done, return now
            return ModUtil::url('Dizkus', 'topic', 'viewtopic', array('topic' => $args['topic_id']));
    
        } else {
            // we are going to delete this posting
            // read raw posts in this topic, sorted by post_time asc
            $posts = DBUtil::selectObjectArray('dizkus_posts', 'topic_id='.$args['topic_id'], 'post_time asc, post_id asc', 1, -1, 'post_id');
            
            // figure out first and last posting and the one to delete
            reset($posts);
            $firstpost = current($posts);
            $lastpost = end($posts);
            $post_to_delete = $posts[$args['post_id']];
            
            // read the raw topic itself
            $topic = ModUtil::apiFunc('Dizkus', 'topic', 'read0', $args['topic_id']);
            // read the raw forum
            $forum = DBUtil::selectObjectById('dizkus_forums', $firstpost['forum_id'], 'forum_id');

            if ($args['post_id'] == $lastpost['post_id']) {
                // posting is the last one in the array
                // if it is the first one too, delete the topic
                if ($args['post_id'] == $firstpost['post_id']) {
                    // ... and it is also the first posting in the topic, so we can simply
                    // delete the complete topic
                    // this also adjusts the counters
                    ModUtil::apiFunc('Dizkus', 'topic', 'delete',$args['topic_id']);
                    // cannot return to the topic, must return to the forum
                    return System::redirect(ModUtil::url('Dizkus', 'forum', 'viewforum', array('forum' => $row['forum_id'])));
                } else {
                    // it was the last one, but there is still more in this topic
                    // find the new "last posting" in this topic
                    $cutofflastpost = array_pop($posts);
                    $newlastpost = end($posts);
                    $topic['topic_replies']--;
                    $topic['topic_last_post_id'] = $newlastpost['post_id'];
                    $topic['topic_time']         = $newlastpost['post_time'];
                    $forum['forum_posts']--;
                    // get the forums last posting id - may be from another topic and may have changed - does not need to
                    $forum['forum_last_post_id'] = DBUtil::selectFieldMax('dizkus_posts', 'post_id', 'MAX', 'forum_id='.DataUtil::formatForStore($forum['forum_id']).' AND post_id<>'.DataUtil::formatForStore($args['post_id']));
                }
            } else {
                // posting is not the last one, so we can simply decrement the posting counters in the forum and the topic
                // last_posts ids do not change, neither in the topic nor the forum
                $forum['forum_posts']--;
                $topic['topic_replies']--;
            }
            
            // finally delete the posting now
            DBUtil::deleteObjectByID('dizkus_posts', $args['post_id'], 'post_id');
            
            // decrement user post counter 
            UserUtil::setVar('dizkus_user_posts', UserUtil::getVar('dizkus_user_posts', $post_to_delete['poster_id']) - 1, $post_to_delete['poster_id']);
            //DBUtil::decrementObjectFieldByID('dizkus__users', 'user_posts', $post_to_delete['poster_id'], 'user_id');
             
            // update forum
            DBUtil::updateObject($forum, 'dizkus_forums', null, 'forum_id');
            
            // update topic
            DBUtil::updateObject($topic, 'dizkus_topics', null, 'topic_id');

            if (SessionUtil::getVar('zk_ajax_call', '')  <> 'ajax') {
                $url = ModUtil::url('Dizkus', 'topic', 'viewtopic', array('topic' => $topic['topic_id']));
                return System::redirect($url);
            }
        }
    
        // we should not get here, but who knows...
        return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
    }
    
     /**
     * get_latest_posts
     *
     * @params $args['selorder'] int 1-6, see below
     * @params $args['nohours'] int posting within these hours
     * @params $args['unanswered'] int 0 or 1(= postings with no answers)
     * @params $args['last_visit'] string the users last visit data
     * @params $args['last_visit_unix'] string the users last visit data as unix timestamp
     * @params $args['limit'] int limits the numbers hits read (per list), defaults and limited to 250
     * @returns array (postings, mail2forumpostings, rsspostings, text_to_display)
     */
    public function get_latest_posts($args)
    {
        $ztable = DBUtil::getTables();
    
        // init some arrays
        $posts = array();
        $m2fposts = array();
        $rssposts = array();
    
        if (!isset($args['limit']) || empty($args['limit']) || ($args['limit'] < 0) || ($args['limit'] > 100)) {
            $args['limit'] = 100;
        }
    
        $dizkusvars      = ModUtil::getVar('Dizkus');
        $posts_per_page  = $dizkusvars['posts_per_page'];
        $post_sort_order = $dizkusvars['post_sort_order'];
        $hot_threshold   = $dizkusvars['hot_threshold'];
    
        if ($args['unanswered'] == 1) {
            $args['unanswered'] = "AND t.topic_replies = '0' ORDER BY t.topic_time DESC";
        } else {
            $args['unanswered'] = 'ORDER BY t.topic_time DESC';
        }
    
        // sql part per selected time frame
        switch ($args['selorder'])
        {
            case '2' : // today
                       $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) = 0 ";
                       $text = $this->__('Today');
                       break;
            case '3' : // yesterday
                       $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) = 1 ";
                       $text = $this->__('Yesterday');
                       break;
            case '4' : // lastweek
                       $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) < 8 ";
                       $text= $this->__('Last week');
                       break;
            case '5' : // last x hours
                       $wheretime  = " AND t.topic_time > DATE_SUB(NOW(), INTERVAL " . DataUtil::formatForStore($args['nohours']) . " HOUR) ";
                       $text = DataUtil::formatForDisplay($this->__f('Last %s hours', $args['nohours']));
                       break;
            case '6' : // last visit
                       $wheretime = " AND t.topic_time > '" . DataUtil::formatForStore($args['last_visit']) . "' ";
                       $text = DataUtil::formatForDisplay($this->__f('Last visit: %s', DateUtil::formatDatetime($args['last_visit_unix'], 'datetimebrief')));
                       break;
            case '1' :
            default:   // last 24 hours
                       $wheretime = " AND t.topic_time > DATE_SUB(NOW(), INTERVAL 1 DAY) ";
                       $text  =$this->__('Last 24 hours');
                       break;
        }
    
        // get all forums the user is allowed to read
        $userforums = ModUtil::apiFunc('Dizkus', 'user', 'readuserforums');
        if (!is_array($userforums) || count($userforums) == 0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return array($posts, $m2fposts, $rssposts, $text);
        }
    
        // now create a very simple array of forum_ids only. we do not need
        // all the other stuff in the $userforums array entries
        $allowedforums = array_map(array($this,'_get_forum_ids'), $userforums);
        $whereforum = ' f.forum_id IN (' . DataUtil::formatForStore(implode(',', $allowedforums)) . ') ';
    
        // integrate contactlist's ignorelist here
        $whereignorelist = '';
        if ((isset($dizkusvars['ignorelist_options']) && $dizkusvars['ignorelist_options'] <> 'none') && ModUtil::available('ContactList')) {
            $ignorelist_setting = ModUtil::apiFunc('Dizkus', 'user', 'get_settings_ignorelist', array('uid' => UserUtil::getVar('uid')));
            if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
                // get user's ignore list
                $ignored_users = ModUtil::apiFunc('ContactList', 'user', 'getallignorelist', array('uid' => UserUtil::getVar('uid')));
                $ignored_uids = array();
                foreach ($ignored_users as $item) {
                    $ignored_uids[] = (int)$item['iuid'];
                }
                if (count($ignored_uids) > 0) {
                    $whereignorelist = " AND t.topic_poster NOT IN (".DataUtil::formatForStore(implode(',', $ignored_uids)).")";
                }
            }
        }
    
        $topicscols = DBUtil::_getAllColumnsQualified('dizkus_topics', 't');
        // build the tricky sql
        $sql = 'SELECT '. $topicscols .',
                          f.forum_name,
                          f.forum_pop3_active,
                          c.cat_id,
                          c.cat_title,
                          p.post_time,
                          p.poster_id
                     FROM '.$ztable['dizkus_topics'].' AS t,
                          '.$ztable['dizkus_forums'].' AS f,
                          '.$ztable['dizkus_categories'].' AS c,
                          '.$ztable['dizkus_posts'].' AS p
                    WHERE f.forum_id = t.forum_id
                      AND c.cat_id = f.cat_id
                      AND p.post_id = t.topic_last_post_id
                      AND '.$whereforum
                           .$wheretime
                           .$whereignorelist
                           .$args['unanswered'];
    
        $res = DBUtil::executeSQL($sql, -1, $args['limit']);
    
        $colarray   = DBUtil::getColumnsArray ('dizkus_topics');
        $colarray[] = 'forum_name';
        $colarray[] = 'forum_pop3_active';
        $colarray[] = 'cat_id';
        $colarray[] = 'cat_title';
        $colarray[] = 'post_time';
        $colarray[] = 'poster_id';
        $postarray  = DBUtil::marshallObjects ($res, $colarray);
    
        foreach ($postarray as $post) {
            $post = DataUtil::formatForDisplay($post);
    
            // does this topic have enough postings to be hot?
            $post['hot_topic'] = ($post['topic_replies'] >= $hot_threshold) ? true : false;
    
            // get correct page for latest entry
            if ($post_sort_order == 'ASC') {
                $hc_dlink_times = 0;
                if (($post['topic_replies'] + 1 - $posts_per_page) >= 0) {
                    $hc_dlink_times = 0;
                    for ($x = 0; $x < $post['topic_replies'] + 1 - $posts_per_page; $x += $posts_per_page) {
                        $hc_dlink_times++;
                    }
                }
                $start = $hc_dlink_times * $posts_per_page;
            } else {
                // latest topic is on top anyway...
                $start = 0;
            }
            $post['start'] = $start;
    
            if ($post['poster_id'] == 1) {
                $post['poster_name'] = ModUtil::getVar('Users', 'anonymous');
            } else {
                $post['poster_name'] = UserUtil::getVar('uname', $post['poster_id']);
            }
    
            $post['posted_unixtime'] = strtotime($post['post_time']);
            $post['post_time'] = DateUtil::formatDatetime($post['posted_unixtime'], 'datetimebrief');
    
            $post['last_post_url'] = DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'topic', 'viewtopic',
                                                         array('topic' => $post['topic_id'],
                                                               'start' => (ceil(($post['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page), null, null, true));
    
            $post['last_post_url_anchor'] = $post['last_post_url'] . '#pid' . $post['topic_last_post_id'];
    
            switch ((int)$post['forum_pop3_active'])
            {
                case 1: // mail2forum
                    array_push($m2fposts, $post);
                    break;
                case 2:
                    array_push($rssposts, $post);
                    break;
                case 0: // normal posting
                default:
                    array_push($posts, $post);
            }
        }
    
        return array($posts, $m2fposts, $rssposts, $text);
    }
   
   
    /**
     * movepost
     *
     * @params $args['post'] array with posting data as returned from readpost()
     * @params $args['to_topic']
     * @returns int id of the new topic
     */
    public function movepost($args)
    {
        $old_topic_id = $args['old_topic_id'];
        $to_topic_id     = $args['to_topic_id'];
        $post_id      = $args['post_id'];
        
        // 1 . update topic_id, post_time in posts table
        // for post[post_id]
        // 2 . update topic_replies in nuke_dizkus_topics ( COUNT )
        // for old_topic
        // 3 . update topic_last_post_id in nuke_dizkus_topics
        // for old_topic
        // 4 . update topic_replies in nuke_dizkus_topics ( COUNT )
        // 5 . update topic_last_post_id in nuke_dizkus_topics if necessary
    
        $ztable = DBUtil::getTables();
    
        // 1 . update topic_id in posts table
        $sql = 'UPDATE '.$ztable['dizkus_posts'].'
                SET topic_id='.(int)DataUtil::formatForStore($to_topic_id).'
                WHERE post_id = '.(int)DataUtil::formatForStore($post_id);
    
        DBUtil::executeSQL($sql);
    
        // for to_topic
        // 2 . update topic_replies in dizkus_topics ( COUNT )
        // 3 . update topic_last_post_id in dizkus_topics
        // get the new topic_last_post_id of to_topic
        $sql = 'SELECT post_id, post_time
                FROM '.$ztable['dizkus_posts'].'
                WHERE topic_id = '.(int)DataUtil::formatForStore($to_topic_id).'
                ORDER BY post_time DESC';
    
        $res = DBUtil::executeSQL($sql, -1, 1);
        $colarray = array('post_id', 'post_time');
        $result    = DBUtil::marshallObjects($res, $colarray);
        $to_last_post_id = $result[0]['post_id'];
        $to_post_time    = $result[0]['post_time'];
    
        $sql = 'UPDATE '.$ztable['dizkus_topics'].'
                SET topic_replies = topic_replies + 1,
                    topic_last_post_id='.(int)DataUtil::formatForStore($to_last_post_id).',
                    topic_time=\''.DataUtil::formatForStore($to_post_time).'\'
                WHERE topic_id='.(int)DataUtil::formatForStore($to_topic_id);
    
        DBUtil::executeSQL($sql);
    
        // for old topic ($old_topic_id)
        // 4 . update topic_replies in nuke_dizkus_topics ( COUNT )
        // 5 . update topic_last_post_id in nuke_dizkus_topics if necessary
    
        // get the new topic_last_post_id of the old topic
        $sql = 'SELECT post_id, post_time
                FROM '.$ztable['dizkus_posts'].'
                WHERE topic_id = '.(int)DataUtil::formatForStore($old_topic_id).'
                ORDER BY post_time DESC';
    
        $res = DBUtil::executeSQL($sql, -1, 1);
        $colarray = array('post_id', 'post_time');
        $result    = DBUtil::marshallObjects($res, $colarray);
        $old_last_post_id = $result[0]['post_id'];
        $old_post_time    = $result[0]['post_time'];
    
        // update
        $sql = 'UPDATE '.$ztable['dizkus_topics'].'
                SET topic_replies = topic_replies - 1,
                    topic_last_post_id='.(int)DataUtil::formatForStore($old_last_post_id).',
                    topic_time=\''.DataUtil::formatForStore($old_post_time).'\'
                WHERE topic_id='.(int)DataUtil::formatForStore($old_topic_id);
    
        DBUtil::executeSQL($sql);
    
        return ModUtil::apiFunc('Dizkus', 'topic', 'get_last_topic_page',$old_topic_id);
    } 

 /**
    * helper function to extract forum_ids from forum array
    */
    private function _get_forum_ids($f)
    {
        return $f['forum_id'];
    }

}
