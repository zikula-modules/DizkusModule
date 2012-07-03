<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Category extends Zikula_AbstractApi {
   
    /**
     * readcategorytree
     * read all catgories and forums the recent user has access to
     *
     * @params $args['last_visit'] string the users last visit date as returned from setcookies() function
     * @returns array of categories with an array of forums in the catgories
     *
     */
    public function readcategorytree($args)
    {
        extract($args);
        if(empty($last_visit)) {
            $last_visit = 0;
        }

        static $tree;
    
        $dizkusvars = ModUtil::getVar('Dizkus');
    
        // if we have already called this once during the script
        if (isset($tree)) {
            return $tree;
        }
    
        $ztable = DBUtil::getTables();
        $cattable    = $ztable['dizkus_categories'];
        $forumstable = $ztable['dizkus_forums'];
        $poststable  = $ztable['dizkus_posts'];
        $topicstable = $ztable['dizkus_topics'];
        $userstable  = $ztable['users'];
    


        if(!empty($cat_id)) {
            $cat = ' AND '.$forumstable.'.cat_id='.$cat_id;
        } else {
            $cat = '';
        }


        $sql = 'SELECT ' . $cattable . '.cat_id AS cat_id,
                       ' . $cattable . '.cat_title AS cat_title,
                       ' . $cattable . '.cat_order AS cat_order,
                       ' . $forumstable . '.forum_id AS forum_id,
                       ' . $forumstable . '.forum_name AS forum_name,
                       ' . $forumstable . '.forum_desc AS forum_desc,
                       ' . $forumstable . '.forum_topics AS forum_topics,
                       ' . $forumstable . '.forum_posts AS forum_posts,
                       ' . $forumstable . '.forum_last_post_id AS forum_last_post_id,
                       ' . $forumstable . '.forum_moduleref AS forum_moduleref,
                       ' . $forumstable . '.forum_pntopic AS forum_pntopic,
                       ' . $topicstable . '.topic_title AS topic_title,
                       ' . $topicstable . '.topic_replies AS topic_replies,
                       ' . $userstable . '.uname AS pn_uname,
                       ' . $userstable . '.uid AS pn_uid,
                       ' . $poststable . '.topic_id AS topic_id,
                       ' . $poststable . '.post_time AS post_time
                FROM ' . $cattable . '
                LEFT JOIN ' . $forumstable . ' ON ' . $forumstable . '.cat_id=' . $cattable . '.cat_id
                AND '.$forumstable.'.is_subforum=0'.$cat.' 
                LEFT JOIN ' . $poststable . ' ON ' . $poststable . '.post_id=' . $forumstable . '.forum_last_post_id
                LEFT JOIN ' . $topicstable . ' ON ' . $topicstable . '.topic_id=' . $poststable . '.topic_id
                LEFT JOIN ' . $userstable . ' ON ' . $userstable . '.uid=' . $poststable . '.poster_id
                ORDER BY ' . $cattable . '.cat_order, ' . $forumstable . '.forum_order, ' . $forumstable . '.forum_name';
        $res = DBUtil::executeSQL($sql);
        $colarray = array('cat_id', 'cat_title', 'cat_order', 'forum_id', 'forum_name', 'forum_desc', 'forum_topics', 'forum_posts',
                          'forum_last_post_id', 'forum_moduleref', 'forum_pntopic', 'topic_title', 'topic_replies', 'pn_uname', 'pn_uid', 
                          'topic_id', 'post_time');
        $result = DBUtil::marshallObjects($res, $colarray);

    
        $posts_per_page = ModUtil::getVar('Dizkus', 'posts_per_page');
    
        $tree = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $row) {
                $cat   = array();
                $forum = array();
                $cat['last_post'] = array(); // get the last post in this category, this is an array
                $cat['new_posts'] = false;
                $cat['forums'] = array();
                $cat['cat_id']                = $row['cat_id'];
                $cat['cat_title']             = $row['cat_title'];
                $cat['cat_order']             = $row['cat_order'];
                $forum['forum_id']            = $row['forum_id'];
                $forum['forum_name']          = $row['forum_name'];
                $forum['forum_desc']          = $row['forum_desc'];
                $forum['forum_topics']        = $row['forum_topics'];
                $forum['forum_posts']         = $row['forum_posts'];
                $forum['forum_last_post_id']  = $row['forum_last_post_id'];
                $forum['forum_moduleref']     = $row['forum_moduleref'];
                $forum['forum_pntopic']       = $row['forum_pntopic'];
                $topic_title                  = $row['topic_title'];
                $topic_replies                = $row['topic_replies'];
                $forum['pn_uname']            = $row['pn_uname']; // fixme
                $forum['pn_uid']              = $row['pn_uid'];  // fixme
                $forum['uname']               = $row['pn_uname'];
                $forum['uid']                 = $row['pn_uid'];
                $forum['topic_id']            = $row['topic_id'];
                $forum['post_time']           = $row['post_time'];
            
                if (allowedtoseecategoryandforum($cat['cat_id'], $forum['forum_id'])) {
                    if (!array_key_exists($cat['cat_title'], $tree)) {
                        $tree[$cat['cat_title']] = $cat;
                    }
                    $last_post_data = array();
                    if (!empty($forum['forum_id'])) {
                        if ($forum['forum_topics'] != 0) {
                            // are there new topics since last_visit?
                            if ($forum['post_time'] > $last_visit) {
                                $forum['new_posts'] = true;
                                // we have new posts
                            } else {
                                // no new posts
                                $forum['new_posts'] = false;
                            }
            
                            $posted_unixtime= strtotime($forum['post_time']);
                            $posted_ml = DateUtil::formatDatetime($posted_unixtime, 'datetimebrief');
                            if ($posted_unixtime) {
                                if ($forum['uid']==1) {
                                    $username = ModUtil::getVar('Users', 'anonymous');
                                } else {
                                    $username = $forum['uname'];
                                }
            
                                $last_post = DataUtil::formatForDisplay($this->__f('%1$s<br />by %2$s', array($posted_ml, $username)));
                                $last_post = $last_post.' <a href="' . ModUtil::url('Dizkus','user','viewtopic', array('topic' => $forum['topic_id'])). '">
                                                          <img src="modules/Dizkus/images/icon_latest_topic.gif" alt="' . $posted_ml . ' ' . $username . '" height="9" width="18" /></a>';
                                // new in 2.0.2 - no more preformattd output
                                $last_post_data['name']     = $username;
                                $last_post_data['subject']  = $topic_title;
                                $last_post_data['time']     = $posted_ml;
                                $last_post_data['unixtime'] = $posted_unixtime;
                                $last_post_data['topic']    = $forum['topic_id'];
                                $last_post_data['post']     = $forum['forum_last_post_id'];
                                $last_post_data['url']      = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                       array('topic' => $forum['topic_id'],
                                                                             'start' => (ceil(($topic_replies + 1)  / $posts_per_page) - 1) * $posts_per_page));
                                $last_post_data['url_anchor'] = $last_post_data['url'] . '#pid' . $forum['forum_last_post_id'];
                            } else {
                                // no posts in forum
                                $last_post = $this->__('No posts');
                                $last_post_data['name']       = '';
                                $last_post_data['subject']    = '';
                                $last_post_data['time']       = '';
                                $last_post_data['unixtime']   = '';
                                $last_post_data['topic']      = '';
                                $last_post_data['post']       = '';
                                $last_post_data['url']        = '';
                                $last_post_data['url_anchor'] = '';
                            }
                            $forum['last_post_data'] = $last_post_data;
                        } else {
                            // there are no posts in this forum
                            $forum['new_posts']= false;
                            $last_post = $this->__('No posts');
                        }
                        $forum['last_post']  = $last_post;
                        $forum['forum_mods'] = $this->get_moderators(array('forum_id' => $forum['forum_id']));
            
                        // is the user subscribed to the forum?
                        $forum['is_subscribed'] = 0;
                        if ($this->get_forum_subscription_status(array('user_id' => UserUtil::getVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
                            $forum['is_subscribed'] = 1;
                        }
            
                        // is this forum in the favorite list?
                        $forum['is_favorite'] = 0;
                        if ($dizkusvars['favorites_enabled'] == 'yes') {
                            if ($this->get_forum_favorites_status(array('user_id' => UserUtil::getVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
                                $forum['is_favorite'] = 1;
                            }
                        }
            
                        // set flag if new postings in category
                        if ($tree[$cat['cat_title']]['new_posts'] == false) {
                            $tree[$cat['cat_title']]['new_posts'] = $forum['new_posts'];
                        }
            
                        // make sure that the most recent posting is stored in the category too
                        if ((count($tree[$cat['cat_title']]['last_post']) == 0)
                          || (isset($last_post_data['unixtime']) && $tree[$cat['cat_title']]['last_post']['unixtime'] < $last_post_data['unixtime'])) {
                            // nothing stored before or a is older than b (a < b for timestamps)
                            $tree[$cat['cat_title']]['last_post'] = $last_post_data;
                        }
            
                        array_push($tree[$cat['cat_title']]['forums'], $forum);
                    }
                }
            }
        }
    
        // sort the array by cat_order
        uasort($tree, 'cmp_catorder');
    
        return $tree;
    }

  
}