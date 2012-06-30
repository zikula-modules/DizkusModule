<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Forum extends Zikula_AbstractApi {
    

    
    /**
     * Get forum subscription status
     *
     * @param array $args The argument array.
     *        int $args['user_id'] The users uid.
     *        int $args['forum_id'] The forums id.
     *
     * @return boolean True if the user is subscribed or false if not
     */
    public function getSubscriptionStatus($args)
    {
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(s.msg_id)')
           ->from('Dizkus_Entity_ForumSubscriptions', 's')
           ->where('s.user_id = :user')
           ->setParameter('user', $args['user_id'])
           ->andWhere('s.forum_id = :forum')
           ->setParameter('forum', $args['forum_id'])
           ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();

        return $count > 0;

    }


    /**
     * subscribe
     *
     * @param array $args The argument array.
     *       int $args['forum_id'] The forums id.
     *       int $args['user_id'] The users id (needs ACCESS_ADMIN).
     *
     * @return boolean
     */
    public function subscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }

        $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
            array('forum_id' => $args['forum_id']));
        if (!allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
            return LogUtil::registerPermissionError();
        }

        if ($this->getSubscriptionStatus($args) == false) {
            // add user only if not already subscribed to the forum
            // we can use the args parameter as-is
            $item = new Dizkus_Entity_ForumSubscriptions();
            $data = array('user_id' => $args['user_id'], 'forum_id' => $args['forum_id']);
            $item->merge($data);
            $this->entityManager->persist($item);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }


    /**
     * unsubscribe
     *
     * Unsubscribe a forum
     *
     * @param array $args The argument array.
     *        int $args['forum_id'] The forums id, if empty then we unsubscribe all forums.
     *        int $args['user_id'] The users id (needs ACCESS_ADMIN).
     *
     * @return boolean
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

        if (empty($args['forum_id'])) {
            return LogUtil::registerArgsError();
        }

        $subscription = $this->entityManager
                             ->getRepository('Dizkus_Entity_ForumSubscriptions')
                             ->findOneBy(array('user_id' => $args['user_id'], 'forum_id' => $args['forum_id'])
        );
        $this->entityManager->remove($subscription);
        $this->entityManager->flush();

        return true;
    }


    /**
     * unsubscribeById
     *
     * Unsubscribe a forum by forum id.
     *
     * @param int $id The topic id.
     *
     * @return boolean
     */
    public function unsubscribeById($id)
    {
        $subscription = $this->entityManager->find('Dizkus_Entity_ForumSubscriptions', $id);
        $this->entityManager->remove($subscription);
        $this->entityManager->flush();
        return true;
    }


    /**
     * getCategory
     *
     * Determines the category that a forum belongs to.
     *
     * @param int $forum_id The forum id to find the category of.
     *
     * @return int|boolean on success, false on failure
     */
    public function getCategory($forum_id)
    {
        if (!is_numeric($forum_id)) {
            return false;
        }
        return (int)$this->entityManager->find('Dizkus_Entity_Forums', $forum_id)->getcat_id();
    }
    
      /**
     * getCategory
     *
     * Determines the category that a forum belongs to.
     *
     * @param int $forum_id The forum id to find the category of.
     *
     * @return int|boolean on success, false on failure
     */
    public function getForum($forum_id)
    {
        if (!is_numeric($forum_id)) {
            return false;
        }
        return $this->entityManager->find('Dizkus_Entity_Forums', $forum_id)->toArray();
    }
    
        /**
     * get_last_post_in_forum
     * gets the last post in a forum, false if no posts
     *
     * @params $args['forum_id'] int the forums id
     * @params $args['id_only'] boolean if true, only return the id, not the complete post information
     * @returns array with post information of false
     */
    public function get_last_post_in_forum($args)
    {
        if (!empty($args['forum_id']) && is_numeric($args['forum_id'])) {
            $ztable = DBUtil::getTables();
            $post_id = DBUtil::selectfieldMax('dizkus_posts', 'post_id', 'MAX', $ztable['dizkus_posts_column']['forum_id'].' = '.(int)$args['forum_id']);
    
            if (isset($args['id_only']) && $args['id_only'] == true) {
                return $post_id;
            }
    
            return $this->readpost(array('post_id' => $post_id));
        }
    
        return false;
    }

    
      /**
     * readforum
     * reads the forum information and the last posts_per_page topics incl. poster data
     *
     * @params $args['forum_id'] int the forums id
     * @params $args['start'] int number of topic to start with (if on page 1+)
     * @params $args['last_visit'] string users last visit date
     * @params $args['last_visit_unix'] string users last visit date as timestamp
     * @params $args['topics_per_page'] int number of topics to read, -1 = all topics
     * @returns array Very complex array, see {debug} for more information
     */
    public function readforum($args)
    {
        $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                              array('forum_id' => $args['forum_id'],
                                    'permcheck' => 'nocheck' ));
        if ($forum == false) {
            return LogUtil::registerError($this->__('Error! The forum or topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        if (!allowedtoseecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
            return LogUtil::registerPermissionError();
        }
    
        $ztable = DBUtil::getTables();
    
        $posts_per_page  = ModUtil::getVar('Dizkus', 'posts_per_page');
        if (empty($args['topics_per_page'])) {
            $args['topics_per_page'] = ModUtil::getVar('Dizkus', 'topics_per_page');
        }
        $hot_threshold   = ModUtil::getVar('Dizkus', 'hot_threshold');
        $post_sort_order = ModUtil::apiFunc('Dizkus','user','get_user_post_order');
    
        // read moderators
        $forum['forum_mods'] = $this->get_moderators(array('forum_id' => $forum['forum_id']));
        $forum['last_visit'] = $args['last_visit'];
    
        $forum['topic_start'] = (!empty ($args['start'])) ? $args['start'] : 0;
    
        // is the user subscribed to the forum?
        $forum['is_subscribed'] = 0;
        if ($this->get_forum_subscription_status(array('user_id' => UserUtil::getVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
            $forum['is_subscribed'] = 1;
        }
    
        // is this forum in the favorite list?
        $forum['is_favorite'] = 0;
        if ($this->get_forum_favorites_status(array('user_id' => UserUtil::getVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
            $forum['is_favorite'] = 1;
        }
    
        // if user can write into Forum, set a flag
        $forum['access_comment'] = allowedtowritetocategoryandforum($forum['cat_id'], $forum['forum_id']);
    
        // if user can moderate Forum, set a flag
        $forum['access_moderate'] = allowedtomoderatecategoryandforum($forum['cat_id'], $forum['forum_id']);
    
        // forum_pager is obsolete, inform the user about this
        $forum['forum_pager'] = 'Error! Deprecated \'$forum.forum_pager\' data field used. Please update the template to incorporate the forum pager plug-in.';
    
        // integrate contactlist's ignorelist here
        $whereignorelist = '';
        $ignorelist_setting = ModUtil::apiFunc('Dizkus','user','get_settings_ignorelist',array('uid' => UserUtil::getVar('uid')));
        if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
            // get user's ignore list
            $ignored_users = ModUtil::apiFunc('ContactList','user','getallignorelist',array('uid' => UserUtil::getVar('uid')));
            $ignored_uids = array();
            foreach ($ignored_users as $item) {
                $ignored_uids[]=(int)$item['iuid'];
            }
            if (count($ignored_uids) > 0) {
                $whereignorelist = " AND t.topic_poster NOT IN (".implode(',',$ignored_uids).")";
            }
        }
    
        $sql = 'SELECT t.topic_id,
                       t.topic_title,
                       t.topic_views,
                       t.topic_replies,
                       t.sticky,
                       t.topic_status,
                       t.topic_last_post_id,
                       t.topic_poster,
                       u.uname,
                       u2.uname as last_poster,
                       p.post_time,
                       t.topic_poster
                FROM ' . $ztable['dizkus_topics'] . ' AS t
                LEFT JOIN ' . $ztable['users'] . ' AS u ON t.topic_poster = u.uid
                LEFT JOIN ' . $ztable['dizkus_posts'] . ' AS p ON t.topic_last_post_id = p.post_id
                LEFT JOIN ' . $ztable['users'] . ' AS u2 ON p.poster_id = u2.uid
                WHERE t.forum_id = ' .(int)DataUtil::formatForStore($forum['forum_id']) . '
                ' . $whereignorelist . '
                ORDER BY t.sticky DESC, p.post_time DESC';
                //ORDER BY t.sticky DESC"; // RNG
                //ORDER BY t.sticky DESC, p.post_time DESC";
                //FC ORDER BY t.sticky DESC"; // RNG
                //FC //ORDER BY t.sticky DESC, p.post_time DESC";
    
        $res = DBUtil::executeSQL($sql, $args['start'], $args['topics_per_page']);
        $colarray = array('topic_id', 'topic_title', 'topic_views', 'topic_replies', 'sticky', 'topic_status',
                          'topic_last_post_id', 'topic_poster', 'uname', 'last_poster', 'post_time');
        $result    = DBUtil::marshallObjects($res, $colarray);
    
        //    $forum['forum_id'] = $forum['forum_id'];
        $forum['topics']   = array();
    
        if (is_array($result) && !empty($result)) {
            foreach ($result as $topic) {
                if ($topic['last_poster'] == 'Anonymous') {
                    $topic['last_poster'] = ModUtil::getVar('Users', 'anonymous');
                }
                if ($topic['uname'] == 'Anonymous') {
                    $topic['uname'] = ModUtil::getVar('Users', 'anonymous');
                }
                $topic['total_posts'] = $topic['topic_replies'] + 1;
            
                $topic['post_time_unix'] = strtotime($topic['post_time']);
                $posted_ml = DateUtil::formatDatetime($topic['post_time_unix'], 'datetimebrief');
                $topic['last_post'] = DataUtil::formatForDisplay($this->__f('%1$s<br />by %2$s', array($posted_ml, $topic['last_poster'])));
            
                // does this topic have enough postings to be hot?
                $topic['hot_topic'] = ($topic['topic_replies'] >= $hot_threshold) ? true : false;
                // does this posting have new postings?
                $topic['new_posts'] = ($topic['post_time'] < $args['last_visit']) ? false : true;
            
                // pagination
                $pagination = '';
                $lastpage = 0;
                if ($topic['topic_replies']+1 > $posts_per_page)
                {
                    if ($post_sort_order == 'ASC') {
                        $hc_dlink_times = 0;
                        if (($topic['topic_replies']+1-$posts_per_page) >= 0) {
                            $hc_dlink_times = 0;
                            for ($x = 0; $x < $topic['topic_replies']+1-$posts_per_page; $x+= $posts_per_page) {
                                $hc_dlink_times++;
                            }
                        }
                        $topic['last_page_start'] = $hc_dlink_times * $posts_per_page;
                    } else {
                        // latest topic is on top anyway...
                        $topic['last_page_start'] = 0;
                    }
            
                    $pagination .= '&nbsp;&nbsp;&nbsp;<span class="z-sub">(' . DataUtil::formatForDisplay($this->__('Go to page')) . '&nbsp;';
                    $pagenr = 1;
                    $skippages = 0;
                    for ($x = 0; $x < $topic['topic_replies'] + 1; $x += $posts_per_page)
                    {
                        $lastpage = (($x + $posts_per_page) >= $topic['topic_replies'] + 1);
            
                        if ($lastpage) {
                            $args['start'] = $x;
                        } elseif ($x != 0) {
                            $args['start'] = $x;
                        }
            
                        if ($pagenr > 3 && $skippages != 1 && !$lastpage) {
                            $pagination .= ', ... ';
                            $skippages = 1;
                        }
            
                        if(empty($start)) {
                            $start = 0;
                        }

                        if ($skippages != 1 || $lastpage) {
                            if ($x!=0) $pagination .= ', ';
                            $pagination .= '<a href="' . ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic['topic_id'], 'start' => $start)) . '" title="' . $topic['topic_title'] . ' ' . DataUtil::formatForDisplay($this->__('Page #')) . ' ' . $pagenr . '">' . $pagenr . '</a>';
                        }
            
                        $pagenr++;
                    }
                    $pagination .= ')</span>';
                }
                $topic['pagination'] = $pagination;
                // we now create the url to the last post in the thread. This might
                // on site 1, 2 or what ever in the thread, depending on topic_replies
                // count and the posts_per_page setting
            
                // we keep this for backwardscompatibility
                $topic['last_post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                   array('topic' => $topic['topic_id'],
                                                         'start' => (ceil(($topic['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page));
                $topic['last_post_url_anchor'] = $topic['last_post_url'] . '#pid' . $topic['topic_last_post_id'];
            
                array_push($forum['topics'], $topic);
            }
        }
    
        $topics_start = $args['start']; // FIXME is this still correct?
    
        //usort ($forum['topics'], 'cmp_forumtopicsort'); // RNG
    
        return $forum;
    }

 /**
     * subscribe_forum
     *
     * @param array $args The argument array.
     *
     * @deprecated since 4.0.0
     *
     * @return boolean
     */
    public function subscribe_forum($args)
    {
        ModUtil::apiFunc($this->name, 'Forum', 'subscribe', $args);
    }
    
    /**
     * unsubscribe_forum
     *
     * @param array $args The argument array.
     *
     * @deprecated since 4.0.0
     *
     * @return boolean
     */
    public function unsubscribe_forum($args)
    {
        return ModUtil::apiFunc($this->name, 'Forum', 'unsubscribe', $args);
    }
    

    /**
     * unsubscribe_forum_by_id
     *
     * @param array $args The argument array.
     *
     * @deprecated since 4.0.0
     *
     * @return boolean
     */
    public function unsubscribe_forum_by_id($id)
    {
        return ModUtil::apiFunc($this->name, 'Forum', 'unsubscribeById', $id);
    }


 
    
    /**
     * add_favorite_forum
     *
     * @params $args['forum_id'] int the forums id
     * @params $args['user_id'] int - Optional - the user id
     * @returns void
     */
    public function add_favorite_forum($args)
    {
        if (!isset($args['user_id'])) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }
    
        $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                              array('forum_id' => $args['forum_id']));
    
        if (!allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
            return LogUtil::registerPermissionError();
        }
    
        if ($this->get_forum_favorites_status($args) == false) {
            // add user only if not already a favorite
            // we can use the args parameter as-is
            $favorite = new Dizkus_Entity_Favorites();
            $favorite->merge($args);
            $this->entityManager->persist($favorite);
            $this->entityManager->flush();            
        }
    
        return true;
    }
    
    /**
     * remove_favorite_forum
     *
     * @params $args['forum_id'] int the forums id
     * @params $args['user_id'] int - Optional - the user id
     * @returns bool
     */
    public function remove_favorite_forum($args)
    {        
        if (!isset($args['user_id'])) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }
    
        // remove from favorites - no need to check the favorite status, we delete it anyway
        $user_id  = (int)DataUtil::formatForStore($args['user_id']);
        $forum_id = (int)DataUtil::formatForStore($args['forum_id']);
                
        $favorite = $this->entityManager->getRepository('Dizkus_Entity_Favorites')
                                   ->findOneBy(array('user_id' => $user_id, 'forum_id' => $forum_id));  
        $this->entityManager->remove($favorite);
        $this->entityManager->flush();
        return true;
    }

 /**
     * get_forum_category
     *
     * @param forum_id - The forum id to find the category of
     *
     * @deprecated since 4.0.0
     *
     * @return int|boolean on success, false on failure
     */
    public function get_forum_category($args)
    {
        if (!isset($args['forum_id'])) {
            return false;
        }
        return ModUtil::apiFunc($this->name, 'Forum', 'getCategory', $args['forum_id']);
    }
    
     
    /**
     * get_forum_subscriptions
     *
     * @params none
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     * @returns array with forum ids, may be empty
     */
    public function get_forum_subscriptions($args)
    {
        if (isset($args['user_id'])) {
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
    
        $ztable = DBUtil::getTables();
    
        // read the topic ids
        $sql = 'SELECT f.' . $ztable['dizkus_forums_column']['forum_id'] . ',
                       f.' . $ztable['dizkus_forums_column']['forum_name'] . ',
                       c.' . $ztable['dizkus_categories_column']['cat_id'] . ',
                       c.' . $ztable['dizkus_categories_column']['cat_title'] . '
                FROM ' . $ztable['dizkus_subscription'] . ' AS fs,
                     ' . $ztable['dizkus_forums'] . ' AS f,
                     ' . $ztable['dizkus_categories'] . ' AS c 
                WHERE fs.' . $ztable['dizkus_subscription_column']['user_id'] . '=' . (int)DataUtil::formatForStore($args['user_id']) . '
                  AND f.' . $ztable['dizkus_forums_column']['forum_id'] . '=fs.' . $ztable['dizkus_subscription_column']['forum_id'] . '
                  AND c.' . $ztable['dizkus_categories_column']['cat_id'] . '=f.' . $ztable['dizkus_forums_column']['cat_id']. '
                ORDER BY c.' . $ztable['dizkus_categories_column']['cat_order'] . ', f.' . $ztable['dizkus_forums_column']['forum_order'];
    
        $res           = DBUtil::executeSQL($sql);
        $colarray      = array('forum_id', 'forum_name', 'cat_id', 'cat_title');
        $subscriptions = DBUtil::marshallObjects($res, $colarray);
    
        return $subscriptions;
    }
    
    public function readSubForums()
    {   
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('s')
           ->from('Dizkus_Entity_Subforums', 's')
           ->where('s.is_subforum > 0')
           ->orderBy('s.forum_name', 'DESC');
        
        return $qb->getQuery()->getArrayResult();
    }

     /**
    * helper function to extract forum_ids from forum array
    */
    function _get_forum_ids($f)
    {
        return $f['forum_id'];
    }

    /**
    * helper function
    */
    function _get_favorites($f)
    {
        return (int)$f['forum_id'];
    }

}
