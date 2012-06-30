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
 * This class provides the topic api functions
 */
class Dizkus_Api_Topic extends Zikula_AbstractApi {
    
    
    
    /**
     * Toggle a topic lock.
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] Topic id.
     *        string $args['mode'] Lock mode (lock|unlocked).
     *
     * @return void
     */
    public function toggleLock($args)
    {
        if (isset($args['topic_id']) && is_numeric($args['topic_id']) && isset($args['mode'])) {
            
            $topic = $this->entityManager->find('Dizkus_Entity_Topics', $args['topic_id']);
            if ($args['mode'] == 'lock') {
                $topic->lock();
            } else {
                $topic->unlock();
            }
            $this->entityManager->flush();
        }
        return;
    }
    
    
    /**
     * Sticky/unsticky a topic.
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] Topic id.
     *        string $args['mode'] Sticky mode (sticky|unsticky).
     *
     * @return void
     */
    public function toggleSticky($args)
    {
        if (isset($args['topic_id']) && is_numeric($args['topic_id']) && isset($args['mode'])) {
            
            $topic = $this->entityManager->find('Dizkus_Entity_Topics', $args['topic_id']);
            if ($args['mode'] == 'sticky') {
                $topic->sticky();
            } else {
                $topic->unsticky();
            }
            $this->entityManager->flush();
        }
        return;
    }
    
    
    /**
     * Subscribe a topic.
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] Topic id.
     *        int $args['user_id'] User id (optional: needs ACCESS_ADMIN).
     *
     * @return void|bool
     */
    public function subscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
    
        list($forum_id, $cat_id) = $this->get_forumid_and_categoryid_from_topicid(array('topic_id' => $args['topic_id']));
        if (!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            return LogUtil::registerPermissionError();
        }

        $status = $this->getSubscriptionStatus(array('userid' => $args['user_id'], 'topic_id' => $args['topic_id']));
        if (!$status) {
            $subscription = new Dizkus_Entity_TopicSubscriptions();
            $subscription->settopic_id($args['topic_id']);
            $subscription->setuser_id($args['user_id']);
            $this->entityManager->persist($subscription);
            $this->entityManager->flush();
        }

        return;
    }
    
    
    /**
     * Unsubscribe a topic.
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] Topics id, if not set we unsubscribe all topics.
     *        int $args['user_id']  Users id (needs ACCESS_ADMIN).
     *
     * @return void|bool
     */
    public function unsubscribe($args)
    {
        $where = array();
        if (isset($args['user_id'])) {
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
            $where['user_id'] = $args[user_id];
        } else {
            $where['user_id'] = UserUtil::getVar('uid');
        }

        $where = $args['topic_id'];
        
        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_TopicSubscriptions')
                                             ->findBy($where);
        foreach ($subscriptions as $subscription) {
            $this->entityManager->remove($subscription);
        }
        $this->entityManager->flush();
    
        return;
    }
    
    /**
     * Get topic subscription status.
     *
     * @param array $args Arguments array.
     *        int $args['user_id'] Users uid.
     *        int $args['topic_id'] Topic id.
     *
     * @return bool true if the user is subscribed or false if not
     */
    public function getSubscriptionStatus($args)
    {
        
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(s)')
           ->from('Dizkus_Entity_TopicSubscriptions', 's')
           ->where('s.user_id = :user')
           ->setParameter('user', $args['user_id'])
           ->andWhere('s.topic_id = :topic')
           ->setParameter('topic', $args['topic_id'])
           ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();
        return $count > 0; 
    }

    /**
     * readtopic
     *
     * @param int $topic_id The topic id.
     *
     * @return array
     */
    public function read0($topic_id) {
        return $this->entityManager->find('Dizkus_Entity_Topics', $topic_id)->toArray();
    }



    /**
     * read
     *
     * This function reads a topic with the last posts_per_page answers (incl the initial posting when on page #1)
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] The topics id.
     *        int $args['start'] Number of posting to start with (if on page 1+).
     *        boolean $args['complete'] If true, reads the complete thread and does not care about the posts_per_page
     *        setting, ignores 'start'.
     *        boolean $args['count'] True if we have raise the read counter, default false.
     *        boolean $args['nohook'] True if transform hooks should not modify post text.
     *
     * @return array Very complex array, see {debug} for more information
     */
    public function read($args)
    {
        $dizkusvars      = ModUtil::getVar('Dizkus');
        $posts_per_page  = $dizkusvars['posts_per_page'];
        $topics_per_page = $dizkusvars['topics_per_page'];
        $post_sort_order = ModUtil::apiFunc('Dizkus','user','get_user_post_order');
      
        $complete = (isset($args['complete'])) ? $args['complete'] : false;
        $count    = (isset($args['count'])) ? $args['count'] : false;
        $start    = (isset($args['start'])) ? $args['start'] : 0;
        $hooks    = (isset($args['nohook']) && $args['nohook'] == false) ? false : true;
    
        $currentuserid = UserUtil::getVar('uid');
        $now = time();
        $timespanforchanges = $this->getVar('timespanforchanges', 24);
        $timespansecs = $timespanforchanges * 60 * 60;

        
        $topic_id = (isset($args['topic_id'])) ? $args['topic_id'] : false;
         if (!$topic_id) {
            return LogUtil::registerError($this->__('Error! <pre>'.$topic_id.'</pre>'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        $topic = $this->entityManager->find('Dizkus_Entity_Topics', $topic_id)->toArray();


        // integrate forum and category information
        $forum                      = $this->entityManager->find('Dizkus_Entity_Forums', $topic['forum_id']);
        $topic['forum_name']        = (string)$forum->getforum_name();
        $topic['cat_id']            = (int)$forum->getcat_id();
        $topic['cat_title']         = (string)$this->entityManager->find('Dizkus_Entity_Categories', $forum['cat_id'])->getcat_title();
        $topic['forum_pop3_active'] = (int)$forum->getforum_pop3_active();


        // integrate contactlist's ignorelist here (part 1/2)
        $ignored_uids = array();
        $ignorelist_setting = ModUtil::apiFunc('Dizkus','user','get_settings_ignorelist',array('uid' => UserUtil::getVar('uid')));
        if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
            // get user's ignore list
            $ignored_users = ModUtil::apiFunc('ContactList','user','getallignorelist',array('uid' => UserUtil::getVar('uid')));
            $ignored_uids = array();
            foreach ($ignored_users as $item) {
                $ignored_uids[] = (int)$item['iuid'];
            }
        }
    
        $topic['start']           = $start;
        $topic['topic_unixtime']  = $topic['topic_time'];
        $topic['post_sort_order'] = $post_sort_order;

        // pop3_active contains the external source (if any), create the correct var name
        // 0 - no external source
        // 1 - mail
        // 2 - rss
        $topic['externalsource'] = $topic['forum_pop3_active'];
        // kill the wrong var
        unset($topic['forum_pop3_active']);

        if (!allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id'])) {
            return LogUtil::registerPermissionError();
        }

        $topic['forum_mods'] =  ModUtil::apiFunc($this->name, 'Moderators', 'get',array('forum_id' => $topic['forum_id']));

        $topic['access_see']      = allowedtoseecategoryandforum($topic['cat_id'], $topic['forum_id']);
        $topic['access_read']     = $topic['access_see'] && allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id'], $currentuserid);
        $topic['access_comment']  = true;
        $topic['access_moderate'] = false;
        $topic['access_admin']    = false;
        if ($topic['access_read'] == true) {
            $topic['access_comment']  = $topic['access_read'] && allowedtowritetocategoryandforum($topic['cat_id'], $topic['forum_id'], $currentuserid);
            if ($topic['access_comment'] == true) {
                $topic['access_moderate'] = $topic['access_comment'] && allowedtomoderatecategoryandforum($topic['cat_id'], $topic['forum_id'], $currentuserid);
                if ($topic['access_moderate'] == true) {
                    $topic['access_admin']    = $topic['access_moderate'] && allowedtoadmincategoryandforum($topic['cat_id'], $topic['forum_id'], $currentuserid);
                }
            }
        }
        // check permission to change the topic subject
        if ($topic['access_moderate']) {
            // user has moderate perms, copy this to topicsubjectedit
            $topic['access_topicsubjectedit'] = $topic['access_moderate'];
        } else {
            // check if user is the topic starter and give him the permission to
            // update the subject
            $topic['access_topicsubjectedit'] = (UserUtil::getVar('uid') == $topic['topic_poster']);
        }

        // get the next and previous topic_id's for the next / prev button
        $topic['next_topic_id'] = $this->get_previous_or_next_topic_id(array('topic_id' => $topic['topic_id'], 'view'=>'next'));
        $topic['prev_topic_id'] = $this->get_previous_or_next_topic_id(array('topic_id' => $topic['topic_id'], 'view'=>'previous'));

        // get the users topic_subscription status to show it in the quick repliy checkbox
        // correctly
        if ($this->get_topic_subscription_status(array('user_id'   => $currentuserid, 'topic_id' => $topic['topic_id'])) == true) {
            $topic['is_subscribed'] = 1;
        } else {
            $topic['is_subscribed'] = 0;
        }

        // update topic counter
        if ($count == true) {
            $this->entityManager->find('Dizkus_Entity_Topics', $topic['topic_id'])->counter();
            $this->entityManager->flush();
        }
        // more then one page in this topic?
        $topic['total_posts'] = ModUtil::apiFunc($this->name, 'Users', 'boardstats', array('id' => $topic['topic_id'], 'type' => 'topic'));

        if ($topic['total_posts'] > $posts_per_page) {
            $times = 0;
            for ($x = 0; $x < $topic['total_posts']; $x += $posts_per_page) {
                $times++;
            }
            $topic['pages'] = $times;
        }

        $topic['post_start'] = (!empty($start)) ? $start : 0;

        // topic_pager is obsolete, inform the user about this
        $topic['topic_pager'] = 'Error! Deprecated \'$topic.topic_pager\' data field used. Please update the template to incorporate the topic pager plug-in.';

        $topic['posts'] = array();

        // read posts
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('Dizkus_Entity_Posts', 'p')
            ->where('p.topic_id = :topic_id')
            ->setParameter('topic_id', $topic['topic_id'])
            ->orderBy('p.post_id', $post_sort_order);
        $query = $qb->getQuery();
        if (!$complete) {
            $query->setFirstResult($start);
            $query->setMaxResults($posts_per_page);
        }
        $result2 = $query->getArrayResult();

        // performance patch:
        // we store all userdata read for the single postings in the $userdata
        // array for later use. If user A is referenced more than once in the
        // topic, we do not need to load his dat again from the db.
        // array index = userid
        // array value = array with user information
        // this increases the amount of memory used but speeds up the loading of topics
        $userdata = array();

        if (is_array($result2) && !empty($result2)) {
            foreach ($result2 as $post) {
                $post['topic_id'] = $topic['topic_id'];

                // check if array_key_exists() with poster _id in $userdata
                //if (!array_key_exists($post['poster_id'], $userdata)) {
                if (!isset($userdata[$post['poster_id']])) {
                    // not in array, load the data now...
                    $userdata[$post['poster_id']] = ModUtil::apiFunc($this->name, 'UserData', 'getFromId', $post['poster_id']);
                }
                // we now have the data and use them
                $post['poster_data'] = $userdata[$post['poster_id']];
                $post['posted_unixtime'] = $post['post_time']->getTimestamp();
                // we use br2nl here for backwards compatibility
                //$message = phpbb_br2nl($message);
                //$post['post_text'] = phpbb_br2nl($post['post_text']);

                $post['post_text'] = dzk_replacesignature($post['post_text'], $post['poster_data']['signature']);

                if ($hooks == true) {
                    // call hooks for $message
                    // list($post['post_text']) = ModUtil::callHooks('item', 'transform', $post['post_id'], array($post['post_text']));
                }

                $post['post_text'] = dzkVarPrepHTMLDisplay($post['post_text']);
                //$post['post_text'] = DataUtil::formatForDisplayHTML($post['post_text']);

                $post['poster_data']['reply'] = false;
                if ($topic['access_comment'] || $topic['access_moderate'] || $topic['access_admin']) {
                    // user is allowed to reply
                    $post['poster_data']['reply'] = true;
                }

                $post['poster_data']['seeip'] = false;
                if (($topic['access_moderate'] || $topic['access_admin']) && $dizkusvars['log_ip'] == 'yes') {
                    //ModUtil::getVar('Dizkus', 'log_ip') == 'yes') {
                    // user is allowed to see ip
                    $post['poster_data']['seeip'] = true;
                }

                $post['poster_data']['moderate'] = false;
                $post['poster_data']['edit'] = false;
                if ($topic['access_moderate'] || $topic['access_admin']) {
                    // user is allowed to moderate
                    $post['poster_data']['moderate'] = true;
                    $post['poster_data']['edit'] = true;
                } elseif ($post['poster_data']['uid'] == $currentuserid) {
                    // user is allowed to moderate || own post
                    // if the timespanforchanges (in hrs!) setting allows it
                    // timespanforchanges is in hours, but we need seconds:
                    if (($now - $post['posted_unixtime']) <= $timespansecs ) {
                        $post['poster_data']['edit'] = true;
                    }
                }

                // integrate contactlist's ignorelist here (part 2/2)
                // the added variable will be handled in templates
                $post['contactlist_ignored'] = (in_array($post['poster_id'], $ignored_uids)) ? 1 : 0;
                //orignal von quan (e_all): if (in_array($post['poster_id'], $ignored_uids)) $post['contactlist_ignored'] = 1;

                array_push($topic['posts'], $post);
            }
        }

        unset($userdata);
        return $topic;
    }


    /**
     * getIdByReference
     *
     * Gets a topic reference as parameter and delivers the internal topic id used for Dizkus as comment module
     *
     * @param string $reference The reference.
     *
     * @return array Topic data as array
     */
    public function getIdByReference($reference)
    {
        if (empty($reference)) {
            return LogUtil::registerArgsError();
        }

        return $this->entityManager->getRepository('Dizkus_Entity_Topics')
                                   ->findOneBy(array('topic_reference' => $reference))
                                   ->toArray();
    }


    /**
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
    public function email($args)
    {
        $sender_name = UserUtil::getVar('uname');
        $sender_email = UserUtil::getVar('email');
        if (!UserUtil::isLoggedIn()) {
            $sender_name = ModUtil::getVar('Users', 'anonymous');
            $sender_email = ModUtil::getVar('Dizkus', 'email_from');
        }

        $params = array(
            'fromname'    => $sender_name,
            'fromaddress' => $sender_email,
            'toname'      => $args['sendto_email'],
            'toaddress'   => $args['sendto_email'],
            'subject'     => $args['subject'],
            'body'        => $args['message'],
        );
        return ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $params);
    }

    /**
     * deletet
     *
     * This function deletes a topic given by id.
     *
     * @param int $topic_id The topics id.
     *
     * @return int the forums id for redirecting
     */
    public function delete($topic_id)
    {
        list($forum_id, $cat_id) = $this->get_forumid_and_categoryid_from_topicid($topic_id);
        if (!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
            return LogUtil::registerPermissionError();
        }

        // Update the users's post count, this might be slow on big topics but it makes other parts of the
        // forum faster so we win out in the long run.

        // step #1: get all post ids and posters ids
        $postings = $this->entityManager->getRepository('Dizkus_Entity_Posts')
            ->findBy(array('topic_id' => $topic_id));


        // step #2 go through the posting array and decrement the posting counter
        // TO-DO: for larger topics use IN(..., ..., ...) with 50 or 100 posting ids per sql
        // step #3 and delete postings
        foreach ($postings as $posting) {
            UserUtil::setVar('dizkus_user_posts', UserUtil::getVar('dizkus_user_posts', $posting->getposter_id()) - 1, $posting->getposter_id());
            //DBUtil::decrementObjectFieldByID('dizkus__users', 'user_posts', $posting['poster_id'], 'user_id');
            $this->entityManager->remove($posting);
        }


        // now delete the topic itself
        $topic = $this->entityManager->getRepository('Dizkus_Entity_Topics')->find($topic_id);
        $this->entityManager->remove($topic);



        // remove topic subscriptions
        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_TopicSubscriptions')
            ->findBy(array('topic_id' => $topic_id));
        foreach ($subscriptions as $subscription) {
            $this->entityManager->remove($subscription);
        }

        // get forum info for adjustments


        $forum = $this->entityManager->find('Dizkus_Entity_TopicSubscriptions', $forum_id);
        // decrement forum_topics counter
        $forum['forum_topics']--;
        // decrement forum_posts counter
        $forum['forum_posts'] = $forum['forum_posts'] - count($postings);


        $this->entityManager->flush();

        // Let any hooks know that we have deleted an item (topic).
        // ModUtil::callHooks('item', 'delete', $args['topic_id'], array('module' => 'Dizkus'));

        ModUtil::apiFunc('Dizkus', 'admin', 'sync', array('id' => $forum_id, 'type' => 'forum'));
        return $forum_id;
    }

        /**
     * get_firstlast_post_in_topic
     * gets the first or last post in a topic, false if no posts
     *
     * @params $args['topic_id'] int the topics id
     * @params $args['first']   boolean if true then get the first posting, otherwise the last
     * @params $args['id_only'] boolean if true, only return the id, not the complete post information
     * @returns array with post information or false or (int)id if id_only is true
     */
    public function get_firstlast_post_in_topic($args)
    {
        if (!empty($args['topic_id']) && is_numeric($args['topic_id'])) {
            $ztable = DBUtil::getTables();
            $option  = (isset($args['first']) && $args['first'] == true) ? 'MIN' : 'MAX';
            $post_id = DBUtil::selectFieldMax('dizkus_posts', 'post_id', $option, $ztable['dizkus_posts_column']['topic_id'].' = '.(int)$args['topic_id']);
    
            if ($post_id <> false) {
                if (isset($args['id_only']) && $args['id_only'] == true) {
                    return $post_id;
                }
                return $this->readpost(array('post_id' => $post_id));
            }
        }
    
        return false;
    }
    
     /**
     * preparenewtopic
     *
     * @params $args['message'] string the text (only set when preview is selected)
     * @params $args['subject'] string the subject (only set when preview is selected)
     * @params $args['forum_id'] int the forums id
     * @params $args['topic_start'] bool true if we start a new topic
     * @params $args['attach_signature'] int 1= attach signature, otherwise no
     * @params $args['subscribe_topic'] int 1= subscribe topic, otherwise no
     * @returns array with information....
     */
    public function preparenewtopic($args)
    {
        $ztable = DBUtil::getTables();
    
        $newtopic = array();
        $newtopic['forum_id'] = $args['forum_id'];
        $newtopic['topic_id'] = 0;
    
        // select forum name and cat title based on forum_id
        $sql = "SELECT f.forum_name,
                       c.cat_id,
                       c.cat_title
                FROM ".$ztable['dizkus_forums']." AS f,
                    ".$ztable['dizkus_categories']." AS c
                WHERE (forum_id = '".(int)DataUtil::formatForStore($args['forum_id'])."'
                AND f.cat_id=c.cat_id)";
        $res = DBUtil::executeSQL($sql);
        $colarray = array('forum_name', 'cat_id', 'cat_title');
        $myrow    = DBUtil::marshallObjects($res, $colarray);
    
        $newtopic['cat_id']     = $myrow[0]['cat_id'];
        $newtopic['forum_name'] = DataUtil::formatForDisplay($myrow[0]['forum_name']);
        $newtopic['cat_title']  = DataUtil::formatForDisplay($myrow[0]['cat_title']);
    
        $newtopic['topic_unixtime'] = time();
    
        // need at least "comment" to add newtopic
        if (!allowedtowritetocategoryandforum($newtopic['cat_id'], $newtopic['forum_id'])) {
            // user is not allowed to post
            return LogUtil::registerPermissionError();
        }

        $newtopic['poster_data'] = ModUtil::apiFunc('Dizkus', 'user', 'get_userdata_from_id', 
                                                    array('userid' => UserUtil::getVar('uid')));
        $newtopic['subject'] = $args['subject'];
        $newtopic['message'] = $args['message'];
        $newtopic['message_display'] = $args['message']; // phpbb_br2nl($args['message']);
    
        // list($newtopic['message_display']) = ModUtil::callHooks('item', 'transform', '', array($newtopic['message_display']));
        $newtopic['message_display'] = nl2br($newtopic['message_display']);
    
        if (UserUtil::isLoggedIn()) {
            if ($args['topic_start'] == true) {
                $newtopic['attach_signature'] = true;
                $newtopic['subscribe_topic']  = ((int)UserUtil::getVar('dizkus_autosubscription', -1, 1)==1) ? true : false;
            } else {
                $newtopic['attach_signature'] = $args['attach_signature'];
                $newtopic['subscribe_topic']  = $args['subscribe_topic'];
            }
        } else {
            $newtopic['attach_signature'] = false;
            $newtopic['subscribe_topic']  = false;
        }
    
        return $newtopic;
    }
    
    /**
     * storenewtopic
     *
     * @params $args['subject'] string the subject
     * @params $args['message'] string the text
     * @params $args['forum_id'] int the forums id
     * @params $args['time'] string (optional) the time, only needed when creating a shadow
     *                             topic
     * @params $args['attach_signature'] int 1=yes, otherwise no
     * @params $args['subscribe_topic'] int 1=yes, otherwise no
     * @params $args['reference']  string for comments feature: <modname>-<objectid>
     * @params $args['post_as']    int used id under which this topic should be posted
     * @returns int the new topics id
     */
    public function storenewtopic($args)
    {
        $cat_id = $this->get_forum_category(array('forum_id' => $args['forum_id']));
        if (!allowedtowritetocategoryandforum($cat_id, $args['forum_id'])) {
            return LogUtil::registerPermissionError();
        }
        
        if ($this->isSpam($args['message'])) {
            return LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
        }
        

        if (trim($args['message']) == '' || trim($args['subject']) == '') {
            // either message or subject is empty
            return LogUtil::registerError($this->__('Error! You tried to post a blank message. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        /*
        it's a submitted page and message and subject are not empty
        */
    
        //  grab message for notification
        //  without html-specialchars, bbcode, smilies <br /> and [addsig]
        $posted_message = stripslashes($args['message']);
    
        //  anonymous user has uid=0, but needs uid=1
        if (isset($args['post_as']) && !empty($args['post_as']) && is_numeric($args['post_as'])) {
            $pn_uid = $args['post_as'];
        } else {
            if (UserUtil::isLoggedIn()) {
                if ($args['attach_signature'] == 1) {
                    $args['message'] .= '[addsig]';
                }
                $pn_uid = UserUtil::getVar('uid');
            } else  {
                $pn_uid = 1;
            }
        }
        
        // some enviroment for logging ;)
        if (System::serverGetVar('HTTP_X_FORWARDED_FOR')){
            $poster_ip = System::serverGetVar('REMOTE_ADDR')."/".System::serverGetVar('HTTP_X_FORWARDED_FOR');
        } else {
            $poster_ip = System::serverGetVar('REMOTE_ADDR');
        }
        // for privavy issues ip logging can be deactivated
        if (ModUtil::getVar('Dizkus', 'log_ip') == 'no') {
            $poster_ip = '127.0.0.1';
        }
    
        $time = (isset($args['time'])) ? $args['time'] : DateUtil::getDatetime('', '%Y-%m-%d %H:%M:%S');
    
        // create topic
        $obj['topic_title']     = $args['subject'];
        $obj['topic_poster']    = $pn_uid;
        $obj['forum_id']        = $args['forum_id'];
        $obj['topic_time']      = $time;
        $obj['topic_reference'] = (isset($args['reference'])) ? $args['reference'] : '';
        DBUtil::insertObject($obj, 'dizkus_topics', 'topic_id');
    
        // create posting
        $pobj['topic_id']   = $obj['topic_id'];
        $pobj['forum_id']   = $obj['forum_id'];
        $pobj['poster_id']  = $obj['topic_poster'];
        $pobj['post_time']  = $obj['topic_time'];
        $pobj['poster_ip']  = $poster_ip;
        $pobj['post_msgid'] = (isset($msgid)) ? $msgid : '';
        $pobj['post_text']  = $args['message'];
        $pobj['post_title'] = $obj['topic_title'];
        DBUtil::insertObject($pobj, 'dizkus_posts', 'post_id');
    
        if ($pobj['post_id']) {
            //  updates topics-table
            $obj['topic_last_post_id'] = $pobj['post_id'];
            DBUtil::updateObject($obj, 'dizkus_topics', '', 'topic_id');
    
            // Let any hooks know that we have created a new item.
//            ModUtil::callHooks('item', 'create', $obj['topic_id'], array('module' => 'Dizkus'));
        }
    
        if (UserUtil::isLoggedIn()) {
            // user logged in we have to update users-table
            UserUtil::setVar('dizkus_user_posts', UserUtil::getVar('dizkus_user_posts') + 1);
            //DBUtil::incrementObjectFieldByID('dizkus__users', 'user_posts', $obj['topic_poster'], 'user_id');
    
            // update subscription
            if ($args['subscribe_topic'] == 1) {
                // user wants to subscribe the new topic
                $this->subscribe_topic(array('topic_id' => $obj['topic_id']));
            }
        }
    
        // update forums-table
        $fobj['forum_id']           = $obj['forum_id'];
        $fobj['forum_last_post_id'] = $pobj['post_id'];
        DBUtil::updateObject($fobj, 'dizkus_forums', null, 'forum_id');
        DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_posts',  $obj['forum_id'], 'forum_id');
        DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_topics', $obj['forum_id'], 'forum_id');
    
        // notify for newtopic
        $this->notify_by_email(array('topic_id' => $obj['topic_id'], 'poster_id' => $obj['topic_poster'], 'post_message' => $posted_message, 'type' => '0'));
    
        // delete temporary session var
        SessionUtil::delVar('topic_started');
    
        //  switch to topic display
        return $obj['topic_id'];
    }
    
     /**
     * get_forumid_and categoryid_from_topicid
     * used for permission checks
     *
     * @params $args['topic_id'] int the topics id
     * @returns array(forum_id, category_id)
     */
    public function get_forumid_and_categoryid_from_topicid($topic_id)
    {
        
        if (!isset($topic_id)) {
            return LogUtil::registerError($this->__('Error! no topic id.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
        
        $ztable = DBUtil::getTables();

        // we know about the topic_id, let's find out the forum and catgeory name for permission checks
        $sql = "SELECT f.forum_id,
                       c.cat_id
                FROM  ".$ztable['dizkus_topics']." t
                LEFT JOIN ".$ztable['dizkus_forums']." f ON f.forum_id = t.forum_id
                LEFT JOIN ".$ztable['dizkus_categories']." AS c ON c.cat_id = f.cat_id
                WHERE t.topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";
    
        $res = DBUtil::executeSQL($sql);
        if ($res === false) {
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        $colarray = array('forum_id', 'cat_id');
        $objarray = DBUtil::marshallObjects ($res, $colarray);
        return array_values($objarray[0]); // forum_id, cat_id
    }
    
 /**
     * movetopic
     * moves a topic to another forum
     *
     * @params $args['topic_id'] int the topics id
     * @params $args['forum_id'] int the destination forums id
     * @params $args['shadow']   boolean true = create shadow topic
     * @returns void
     */
    public function movetopic($args)
    {
        // get the old forum id and old post date
        $topic = $this->entityManager->find('Dizkus_Entity_Topics', $args['topic_id'])->toArray();
    
        if ($topic['forum_id'] <> $args['forum_id']) {
            // set new forum id
            $newtopic['forum_id'] = $args['forum_id'];
            DBUtil::updateObject($newtopic, 'dizkus_topics', 'topic_id='.(int)DataUtil::formatForStore($args['topic_id']), 'topic_id');
    
            $newpost['forum_id'] = $args['forum_id'];
            DBUtil::updateObject($newpost, 'dizkus_posts', 'topic_id='.(int)DataUtil::formatForStore($args['topic_id']), 'post_id');
    
            if ($args['shadow'] == true) {
                // user wants to have a shadow topic
                $message = $this->__f('The original posting has been moved <a title="moved" href="%s">here</a>.', ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $args['topic_id'])));
                $subject = $this->__f("*** The original posting '%s' has been moved", $topic['topic_title']);
    
                $this->storenewtopic(array('subject'  => $subject,
                                                    'message'  => $message,
                                                    'forum_id' => $topic['forum_id'],
                                                    'time'     => $topic['topic_time'],
                                                    'no_sig'   => true));
            }
            ModUtil::apiFunc('Dizkus', 'admin', 'sync', array('id' => $args['forum_id'], 'type' => 'forum'));
            ModUtil::apiFunc('Dizkus', 'admin', 'sync', array('id' => $topic['forum_id'], 'type' => 'forum'));
        }
    
        return;
    }
    

 /**
     * getTopicSubscriptions
     *
     * @params none
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     * @returns array with topic ids, may be empty
     */
    public function getTopicSubscriptions($uid)
    {
        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_TopicSubscriptions')
                                   ->findBy(array('user_id' => $uid));
    
        return $subscriptions;
    }
    
    
    /**
     * get_topic_subscriptions
     *
     * @params none
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     *
     * @return array with topic ids, may be empty
     */
    public function get_topic_subscriptions($args)
    {
        $ztable = DBUtil::getTables();
    
        if (isset($args['user_id'])) {
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
    
        // read the topic ids
        $sql = 'SELECT ts.topic_id,
                       t.topic_title,
                       t.topic_poster,
                       t.topic_time,
                       t.topic_replies,
                       t.topic_last_post_id,
                       u.uname,
                       f.forum_id,
                       f.forum_name
                FROM '.$ztable['dizkus_topic_subscription'].' AS ts,
                     '.$ztable['dizkus_topics'].' AS t,
                     '.$ztable['users'].' AS u,
                     '.$ztable['dizkus_forums'].' AS f
                WHERE (ts.user_id='.(int)DataUtil::formatForStore($args['user_id']).'
                  AND t.topic_id=ts.topic_id
                  AND u.uid=ts.user_id
                  AND f.forum_id=t.forum_id)
                ORDER BY f.forum_id, ts.topic_id';
    
        $res = DBUtil::executeSQL($sql);
        $colarray = array('topic_id', 'topic_title', 'topic_poster', 'topic_time', 'topic_replies', 'topic_last_post_id', 'poster_name',
                          'forum_id', 'forum_name');
        $subscriptions    = DBUtil::marshallObjects($res, $colarray);
    
        $post_sort_order = ModUtil::apiFunc('Dizkus', 'user', 'get_user_post_order', array('user_id' => $args['user_id']));
        $posts_per_page  = ModUtil::getVar('Dizkus', 'posts_per_page');
    
        if (is_array($subscriptions) && !empty($subscriptions)) {
            for ($cnt=0; $cnt<count($subscriptions); $cnt++) {
                 
                if ($post_sort_order == 'ASC') {
                    $start = ((ceil(($subscriptions[$cnt]['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page);
                } else {
                    // latest topic is on top anyway...
                    $start = 0;
                }
                // we now create the url to the last post in the thread. This might
                // on site 1, 2 or what ever in the thread, depending on topic_replies
                // count and the posts_per_page setting
                $subscriptions[$cnt]['last_post_url'] = DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                                     array('topic' => $subscriptions[$cnt]['topic_id'],
                                                                                           'start' => $start)));
                
                $subscriptions[$cnt]['last_post_url_anchor'] = $subscriptions[$cnt]['last_post_url'] . '#pid' . $subscriptions[$cnt]['topic_last_post_id'];
            }
        }
    
        return $subscriptions;
    }


    
    /**
     * subscribe_topic
     *
     * @param array $args The argument array.
     *
     * @deprecated since 4.0.0
     *
     * @return boolean
     */
    public function subscribe_topic($args)
    {
        return ModUtil::apiFunc($this->name, 'Topic', 'subscribe', $args);
    }
    
    /**
     * unsubscribe_topic
     *
     * @param array $args The argument array.
     *
     * @deprecated since 4.0.0
     *
     * @return boolean
     */
    public function unsubscribe_topic($args)
    {
        return ModUtil::apiFunc($this->name, 'Topic', 'unsubscribe', $args);
    }
    

       
    /**
     * unsubscribe_topic_by_id
     *
     * @params $args['forum_id'] int the forums id, if empty then we unsubscribe all forums
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     * @returns void
     */
    public function unsubscribe_topic_by_id($id)
    {
        $subscription = $this->entityManager->find('Dizkus_Entity_TopicSubscriptions', $id);
        $this->entityManager->remove($subscription);
        $this->entityManager->flush();
    }
    

     
   

    /**
     * splittopic
     *
     * param array $args The argument array.
     * @params $args['post'] array with posting data as returned from readpost()
     *
     * @deprecated since 4.0.0
     *
     * @return int id of the new topic
     */
    public function splittopic($args)
    {
        return ModUtil::apiFunc($this->name, 'Forum', 'unsubscribeById', $id);

        $post = $args['post'];

        // before we do anything we will read the topic_last_post_id because we will need
        // this one later (it will become the topic_last_post_id of the new thread)
        // DBUtil:: read complete topic
        $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopci0', $post['topic_id']);
    
        //  insert values into topics-table
        $newtopic = array('topic_title'  => $post['topic_subject'],
                          'topic_poster' => $post['poster_data']['uid'],
                          'forum_id'     => $post['forum_id'],
                          'topic_time'   => DateUtil::getDatetime('', '%Y-%m-%d %H:%M:%S'));
        $newtopic = DBUtil::insertObject($newtopic, 'dizkus_topics', 'topic_id');
    
        // increment topics count by 1
        DBUtil::incrementObjectFieldById('dizkus_forums', 'forum_topics', $post['forum_id'], 'forum_id');
    
        // now we need to change the postings:
        // first step: count the number of posting we have to move
        $where = 'WHERE topic_id = '.(int)DataUtil::formatForStore($post['topic_id']).'
                  AND post_id >= '.(int)DataUtil::formatForStore($post['post_id']);
        $posts_to_move = DBUtil::selectObjectCount('dizkus_posts', $where);



        // update the topic_id in the postings
        // starting with $post['post_id'] and then all post_id's where topic_id = $post['topic_id'] and
        // post_id > $post['post_id']
        $updateposts = array('topic_id' => $newtopic['topic_id']);
        $where = 'WHERE post_id >= '.(int)DataUtil::formatForStore($post['post_id']).'
                  AND topic_id = '.$post['topic_id'];
        DBUtil::updateObject($updateposts, 'dizkus_posts', $where, 'post_id');
    
        // get the new topic_last_post_id of the old topic
        $where = 'WHERE topic_id='.(int)DataUtil::formatForStore($post['topic_id']).'
                  ORDER BY post_time DESC';
        $lastpost = DBUtil::selectObject('dizkus_posts', $where);

        $oldtopic = ModUtil::apiFunc($this->name, 'Topic', 'read0', $post['topic_id']);

        // update the new topic
        $newtopic['topic_replies']      = (int)$posts_to_move - 1;
        $newtopic['topic_last_post_id'] = $post['topic_last_post_id'];
        DBUtil::updateObject($newtopic, 'dizkus_topics', null, 'topic_id');


        // update the old topic
        $oldtopic['topic_replies']      = $oldtopic['topic_replies'] - $posts_to_move;
        $oldtopic['topic_last_post_id'] = $lastpost['post_id'];
        $oldtopic['topic_time']         = $lastpost['post_time'];
        DBUtil::updateObject($oldtopic, 'dizkus_topics', null, 'topic_id');

        return $newtopic['topic_id'];
    }
    
    /**
     * get_previous_or_next_topic_id
     * returns the next or previous topic_id in the same forum of a given topic_id
     *
     * @params $args['topic_id'] int the reference topic_id
     * @params $args['view']     string either "next" or "previous"
     * @returns int topic_id maybe the same as the reference id if no more topics exist in the selectd direction
     */
    public function get_previous_or_next_topic_id($args)
    {
        if (!isset($args['topic_id']) || !isset($args['view']) ) {
            return LogUtil::registerArgsError();
        }
    
        switch ($args['view'])
        {
            case 'previous':
                $math = '<';
                $sort = 'DESC';
                break;
    
            case 'next':
                $math = '>';
                $sort = 'ASC';
                break;
    
            default:
                return LogUtil::registerArgsError();
        }
    
        $ztable = DBUtil::getTables();
    
        // integrate contactlist's ignorelist here
        $whereignorelist = '';
        $ignorelist_setting = ModUtil::apiFunc('Dizkus', 'user', 'get_settings_ignorelist',array('uid' => UserUtil::getVar('uid')));
        if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
            // get user's ignore list
            $ignored_users = ModUtil::apiFunc('ContactList', 'user', 'getallignorelist',array('uid' => UserUtil::getVar('uid')));
            $ignored_uids = array();
            foreach ($ignored_users as $item) {
                $ignored_uids[]=(int)$item['iuid'];
            }
            if (count($ignored_uids) > 0) {
                $whereignorelist = " AND t1.topic_poster NOT IN (".implode(',',$ignored_uids).")";
            }
        }
    
        $sql = 'SELECT t1.topic_id
                FROM '.$ztable['dizkus_topics'].' AS t1,
                     '.$ztable['dizkus_topics'].' AS t2
                WHERE t2.topic_id = '.(int)DataUtil::formatForStore($args['topic_id']).'
                  AND t1.topic_time '.$math.' t2.topic_time
                  AND t1.forum_id = t2.forum_id
                  AND t1.sticky = 0
                  '.$whereignorelist.'
                ORDER BY t1.topic_time '.$sort;
    
        $res      = DBUtil::executeSQL($sql, -1, 1);
        $newtopic = DBUtil::marshallObjects($res, array('topic_id'));
    
        return isset($newtopic[0]['topic_id']) ? $newtopic[0]['topic_id'] : 0;
    }

/**
     * get_topic_by_postmsgid
     * gets a topic_id from the postings msgid
     *
     * @params $args['msgid'] string the msgid
     * @returns int topic_id or false if not found
     *
     */
    public function get_topic_by_postmsgid($args)
    {
        if (!isset($args['msgid']) || empty($args['msgid'])) {
            return LogUtil::registerArgsError();
        }
    
        return DBUtil::selectFieldByID('dizkus_posts', 'topic_id', $args['msgid'], 'post_msgid');
    }
    
    /**
     * get_topicid_by_postid
     * gets a topic_id from the post_id
     *
     * @params $args['post_id'] string the post_id
     * @returns int topic_id or false if not found
     *
     */
    public function get_topicid_by_postid($args)
    {
        if (!isset($args['post_id']) || empty($args['post_id'])) {
            return LogUtil::registerArgsError();
        }
    
        return DBUtil::selectFieldByID('dizkus_posts', 'topic_id', $args['post_id'], 'post_id');
    }

      /**
     * get_last_topic_page
     * returns the number of the last page of the topic if more than posts_per_page entries
     * eg. for use as the start parameter in urls
     *
     * @params $args['topic_id'] int the topic id
     * @returns int the page number
     */
    public function get_last_topic_page($args)
    {
        // get some enviroment
        $posts_per_page = ModUtil::getVar('Dizkus', 'posts_per_page');
        $post_sort_order = ModUtil::getVar('Dizkus', 'post_sort_order');
    
        if (!isset($args['topic_id']) || !is_numeric($args['topic_id'])) {
            return LogUtil::registerArgsError();
        }
    
        if ($post_sort_order == 'ASC') {
            $num_postings = DBUtil::selectFieldByID('dizkus_topics', 'topic_replies', $args['topic_id'], 'topic_id');
            // add 1 for the initial posting as we deal with the replies here
            $num_postings++;
            $last_page = floor($num_postings / $posts_per_page);
        } else {
            // DESC = latest topic is on top = page 0 anyway...
            $last_page = 0;
        }
    
        return $last_page;
    }
    
    /**
     * jointopics
     * joins two topics together
     *
     * @params $args['from_topic_id'] int this topic get integrated into to_topic
     * @params $args['to_topic_id'] int   the target topic that will contain the post from from_topic
     */
    public function jointopics($args)
    {
        // check if from_topic exists. this function will return an error if not
        $from_topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $args['from_topic_id'], 'complete' => false, 'count' => false));
        if (!allowedtomoderatecategoryandforum($from_topic['cat_id'], $from_topic['forum_id'])) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
        // check if to_topic exists. this function will return an error if not
        $to_topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $args['to_topic_id'], 'complete' => false, 'count' => false));
        if (!allowedtomoderatecategoryandforum($to_topic['cat_id'], $to_topic['forum_id'])) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
        $ztable = DBUtil::getTables();
        
        // join topics: update posts with from_topic['topic_id'] to contain to_topic['topic_id']
        // and from_topic['forum_id'] to to_topic['forum_id']
        $post_temp = array('topic_id' => $to_topic['topic_id'],
                           'forum_id' => $to_topic['forum_id']);
        $where = 'WHERE topic_id='.(int)DataUtil::formatForStore($from_topic['topic_id']);
        DBUtil::updateObject($post_temp, 'dizkus_posts', $where, 'post_id');                         
    
        // to_topic['topic_replies'] must be incremented by from_topic['topic_replies'] + 1 (initial
        // posting
        // update to_topic['topic_time'] and to_topic['topic_last_post_id']
        // get new topic_time and topic_last_post_id
        $where = 'WHERE topic_id='.(int)DataUtil::formatForStore($to_topic['topic_id']).'
                  ORDER BY post_time DESC';
        $res = DBUtil::selectObject('dizkus_posts', $where);
        $new_last_post_id = $res['post_id'];
        $new_post_time    = $res['post_time'];
    
        // update to_topic
        $to_topic_temp = array('topic_id'           => $to_topic['topic_id'],
                               'topic_replies'      => $to_topic['topic_replies'] + $from_topic['topic_replies'] + 1,
                               'topic_last_post_id' => $new_last_post_id,
                               'topic_time'         => $new_post_time);
        DBUtil::updateObject($to_topic_temp, 'dizkus_topics', null, 'topic_id');  
    
        // delete from_topic from dizkus_topics
        DBUtil::deleteObjectByID('dizkus_topics', $from_topic['topic_id'], 'topic_id');
    
        // update forums table
        // get topics count: decrement from_topic['forum_id']'s topic count by 1
        DBUtil::decrementObjectFieldById('dizkus_forums', 'forum_topics', $from_topic['forum_id'], 'forum_id');
    
        // get posts count: if both topics are in the same forum, we just have to increment
        // the post count by 1 for the initial posting that is now part of the to_topic,
        // if they are in different forums, we have to decrement the post count
        // in from_topic's forum and increment it in to_topic's forum by from_topic['topic_replies'] + 1
        // for the initial posting
        // get last_post: if both topics are in the same forum, everything stays
        // as-is, if not, we update both, even if it is not necessary
    
        if ($from_topic['forum_id'] == $to_topic['forum_id']) {
            // same forum, post count in the forum doesn't change
        } else {
            // different forum
            // get last post in forums
            $where = 'WHERE forum_id='.(int)DataUtil::formatForStore($from_topic['forum_id']).'
                      ORDER BY post_time DESC';
            $res = DBUtil::selectObject('dizkus_posts', $where);
            $from_forum_last_post_id = $res['post_id'];
    
            $where = 'WHERE forum_id='.(int)DataUtil::formatForStore($to_topic['forum_id']).'
                      ORDER BY post_time DESC';
            $res = DBUtil::selectObject('dizkus_posts', $where);
            $to_forum_last_post_id = $res['post_id'];
            
            // calculate posting count difference
            $post_count_difference = (int)DataUtil::formatForStore($from_topic['topic_replies']+1);
            // decrement from_topic's forum post_count
            $sql = "UPDATE ".$ztable['dizkus_forums']."
                    SET forum_posts = forum_posts - $post_count_difference,
                        forum_last_post_id = '" . (int)DataUtil::formatForStore($from_forum_last_post_id) . "'
                    WHERE forum_id='".(int)DataUtil::formatForStore($from_topic['forum_id'])."'";
            DBUtil::executeSQL($sql);
    
            // increment o_topic's forum post_count
            $sql = "UPDATE ".$ztable['dizkus_forums']."
                    SET forum_posts = forum_posts + $post_count_difference,
                        forum_last_post_id = '" . (int)DataUtil::formatForStore($to_forum_last_post_id) . "'
                    WHERE forum_id='".(int)DataUtil::formatForStore($to_topic['forum_id'])."'";
            DBUtil::executeSQL($sql);
        }
        return $to_topic['topic_id'];
    }

     /**
     * get_topicid_by_reference
     * gets a topic reference as parameter and delivers the internal topic id
     * used for Dizkus as comment module
     *
     * @params $args['reference'] string the refernce
     */
    public function get_topicid_by_reference($args)
    {
        if (!isset($args['reference']) || empty($args['reference'])) {
            return LogUtil::registerArgsError();
        }
    
        $topic = $this->entityManager->getRepository('Dizkus_Entity_Topics')
                      ->findOneBy(array('topic_reference' => $args['reference']));    
        return $topic->toArray();
    }
    
     /**
     * getTopicSubscriptions
     *
     * @params none
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     * @returns array with topic ids, may be empty
     */
    public function getForumSubscriptions($uid)
    {
        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_ForumSubscriptionsJoin')
                                   ->findBy(array('user_id' => $uid));
    
        return $subscriptions;
    }
    
        /**
     * toggle new topic subscription
     *
     */
    public function togglenewtopicsubscription($args)
    {
        $user_id = (isset($args['user_id'])) ? $args['user_id'] : UserUtil::getVar('uid');
        if (is_null($user_id)) {
            $user_id = 1;
        }
        
        $asmode = (int)UserUtil::getVar('dizkus_autosubscription', $user_id);
        $asmode = ($asmode == 0) ? 1 : 0;
        UserUtil::setVar('dizkus_autosubscription', $asmode, $user_id);
        return $asmode;
    }
    
        /**
     * get_page_from_topic_replies
     * Uses the number of topic_replies and the posts_per_page settings to determine the page
     * number of the last post in the thread. This is needed for easier navigation.
     *
     * @params $args['topic_replies'] int number of topic replies
     * @return int page number of last posting in the thread
     */
    public function get_page_from_topic_replies($args)
    {
        if (!isset($args['topic_replies']) || !is_numeric($args['topic_replies']) || $args['topic_replies'] < 0 ) {
            return LogUtil::registerArgsError();
        }
    
        // get some enviroment
        $posts_per_page  = ModUtil::getVar('Dizkus', 'posts_per_page');
        $post_sort_order = ModUtil::getVar('Dizkus', 'post_sort_order');
    
        $last_page = 0;
        if ($post_sort_order == 'ASC') {
            // +1 for the initial posting
            $last_page = floor(($args['topic_replies'] + 1) / $posts_per_page);
        }
    
        // if not ASC then DESC which means latest topic is on top anyway...
        return $last_page;
    }
    

}