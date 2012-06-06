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
    
        list($forum_id, $cat_id) = ModUtil::apiFunc($this->name, 'User', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $args['topic_id']));
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
        if (isset($args['user_id'])) {
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }        
        
        unset($args['silent']); // obsulet value
        
        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_TopicSubscriptions')
                                             ->findBy($args);
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
     * readtopic
     * reads a topic with the last posts_per_page answers (incl the initial posting when on page #1)
     *
     * @params $args['topic_id'] it the topics id
     * @params $args['start'] int number of posting to start with (if on page 1+)
     * @params $args['complete'] bool if true, reads the complete thread and does not care about
     *                               the posts_per_page setting, ignores 'start'
     * @params $args['count']      bool  true if we have raise the read counter, default false
     * @params $args['nohook']     bool  true if transform hooks should not modify post text
     * @returns very complex array, see {debug} for more information
     */
    public function read($args)
    {
        $dizkusvars      = ModUtil::getVar('Dizkus');
        $posts_per_page  = $dizkusvars['posts_per_page'];
        $topics_per_page = $dizkusvars['topics_per_page'];
    
        $post_sort_order = ModUtil::apiFunc('Dizkus','user','get_user_post_order');
    
        $complete = (isset($args['complete'])) ? $args['complete'] : false;
        $count    = (isset($args['count'])) ? $args['count'] : false;
        $start    = (isset($args['start'])) ? $args['start'] : -1;
        $hooks    = (isset($args['nohook']) && $args['nohook'] == false) ? false : true;
    
        $currentuserid = UserUtil::getVar('uid');
        $now = time();
        $timespanforchanges = $this->getVar('timespanforchanges', 24);
        $timespansecs = $timespanforchanges * 60 * 60;
    
        $ztable = DBUtil::getTables();
    
        $sql = 'SELECT t.topic_title,
                       t.topic_poster,
                       t.topic_status,
                       t.forum_id,
                       t.sticky,
                       t.topic_time,
                       t.topic_replies,
                       t.topic_last_post_id,
                       f.forum_name,
                       f.cat_id,
                       f.forum_pop3_active,
                       c.cat_title
                FROM  '.$ztable['dizkus_topics'].' t
                LEFT JOIN '.$ztable['dizkus_forums'].' f ON f.forum_id = t.forum_id
                LEFT JOIN '.$ztable['dizkus_categories'].' AS c ON c.cat_id = f.cat_id
                WHERE t.topic_id = '.(int)DataUtil::formatForStore($args['topic_id']);
    
        $res = DBUtil::executeSQL($sql);
        
        
        
        
        
        $colarray = array('topic_title', 'topic_poster', 'topic_status', 'forum_id', 'sticky', 'topic_time', 'topic_replies',
                          'topic_last_post_id', 'forum_name', 'cat_id', 'forum_pop3_active', 'cat_title');
        $result    = DBUtil::marshallObjects($res, $colarray);   
        
        
        //$result[0] = $this->entityManager->find('Dizkus_Entity_Topics', $args['topic_id'])->toArray();
                      
        
        
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
    
        if (is_array($result) && !empty($result)) {
            $topic = $result[0];
            $topic['topic_id'] = $args['topic_id'];
            $topic['start'] = $start;
            $topic['topic_unixtime'] = strtotime($topic['topic_time']);
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
    
            $topic['forum_mods'] = ModUtil::apiFunc($this->name, 'Users', 'get_moderators', array('forum_id' => $topic['forum_id']));
    
            $topic['access_see']      = allowedtoseecategoryandforum($topic['cat_id'], $topic['forum_id']);
            $topic['access_read']     = $topic['access_see'] && allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id'], $currentuserid);
            $topic['access_comment']  = false;
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
            $topic['next_topic_id'] = ModUtil::apiFunc($this->name, 'Users', 'get_previous_or_next_topic_id', array('topic_id' => $topic['topic_id'], 'view'=>'next'));
            $topic['prev_topic_id'] = ModUtil::apiFunc($this->name, 'Users', 'get_previous_or_next_topic_id', array('topic_id' => $topic['topic_id'], 'view'=>'previous'));
    
            // get the users topic_subscription status to show it in the quick repliy checkbox
            // correctly
            if (ModUtil::apiFunc($this->name, 'Users', 'get_topic_subscription_status', array('user_id'   => $currentuserid, 'topic_id' => $topic['topic_id'])) == true) {
                $topic['is_subscribed'] = 1;
            } else {
                $topic['is_subscribed'] = 0;
            }
    
            /**
             * update topic counter
             */
            if ($count == true) {
                DBUtil::incrementObjectFieldByID('dizkus_topics', 'topic_views', $topic['topic_id'], 'topic_id');
            }
            /**
             * more then one page in this topic?
             */
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
            $where = 'WHERE topic_id = '.(int)DataUtil::formatForStore($topic['topic_id']);
            $orderby = 'ORDER BY post_id '.DataUtil::formatForStore($post_sort_order);
            if ($complete == true) {
                //$res2 = DBUtil::executeSQL($sql2);
                $result2 = DBUtil::selectObjectArray('dizkus_posts', $where, $orderby);
            } else {
                //$res2 = DBUtil::executeSQL($sql2, $start, $posts_per_page);
                $result2 = DBUtil::selectObjectArray('dizkus_posts', $where, $orderby, $start, $posts_per_page);
            }
            
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
                        $userdata[$post['poster_id']] = ModUtil::apiFunc($this->name, 'Users', 'get_userdata_from_id',array('userid' => $post['poster_id']));
                    }
                    // we now have the data and use them
                    $post['poster_data'] = $userdata[$post['poster_id']];
                    $post['posted_unixtime'] = strtotime($post['post_time']);
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
        } else {
            // no results - topic does not exist
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        return $topic;
    }
    
}