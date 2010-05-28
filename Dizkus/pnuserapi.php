<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

Loader::includeOnce('modules/Dizkus/common.php');

/**
 * get_userdata_from_id
 * This function dynamically reads all fields of the <prefix>_users and <prefix>_dizkus_users
 * tables. When ever data fields are added there, they will be read too without any change here.
 *
 * @params $args{'userid'] int the users id (pn_uid)
 * @returns array of userdata information
 */
function Dizkus_userapi_get_userdata_from_id($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $userid = $args['userid'];

    static $usersarray;

    //if (isset($usersarray) && is_array($usersarray) && array_key_exists($userid, $usersarray)) {
    if (is_array($usersarray) && isset($usersarray[$userid])) {
        return $usersarray[$userid];
    } else {
        // init array
        $usersarray = array();
    }

    $makedummy = false;
    // get the core user data
    $userdata = pnUserGetVars($userid);
    if ($userdata == false) {
        // create a dummy user basing on Anonymous
        // necessary for some socks :-)
        $userdata  = pnUserGetVars(1);
        $makedummy = true;
    }

    $pntable = pnDBGetTables();

    $dizkus_userdata = DBUtil::selectObjectByID('dizkus_users', $userid, 'user_id');
    
    if (is_array($dizkus_userdata)) {
        $userdata = array_merge($userdata, $dizkus_userdata);

        // set some basic data
        $userdata['moderate'] = false;
        $userdata['reply'] = false;
        $userdata['seeip'] = false;

        //
        // get the users group membership
        //
        /*
        $userdata['groups'] = pnModAPIFunc('Groups', 'user', 'getusergroups',
                                            array('uid' => $userdata['pn_uid']));
        */
        $userdata['groups'] = array();

        //
        // get the users rank
        //
        if ($userdata['user_rank'] != 0) {
            $rank = DBUtil::selectObjectByID('dizkus_ranks', $userdata['user_rank'], 'rank_id');

        } elseif ($userdata['user_posts'] != 0) {
            $where =        $pntable['dizkus_ranks_column']['rank_min'].' <= '.(int)DataUtil::formatForStore($userdata['user_posts']).'
                      AND '.$pntable['dizkus_ranks_column']['rank_max'].' >= '.(int)DataUtil::formatForStore($userdata['user_posts']);

            $rank = DBUtil::selectObject('dizkus_ranks', $where);
        }

        if (is_array($rank)) {
            $userdata = array_merge($userdata, $rank);
            $userdata['rank'] = $userdata['rank_title']; // backwards compatibility
            $userdata['rank_link'] = (substr($userdata['rank_desc'], 0, 7) == 'http://') ? $userdata['rank_desc'] : '';
            if ($userdata['rank_image']) {
                $userdata['rank_image']      = pnModGetVar('Dizkus', 'url_ranks_images') . '/' . $userdata['rank_image'];
                $userdata['rank_image_attr'] = function_exists('getimagesize') ? @getimagesize($userdata['rank_image']) : null;
            }
        }
        
        //
        // user name
        //
        if ($userdata['pn_uid'] != 1) {
            // user is logged in, display some info
            $activetime = DateUtil::getDateTime(time() - (pnConfigGetVar('secinactivemins') * 60));
            $where = $pntable['session_info_column']['uid']." = '".$userdata['pn_uid']."'
                      AND pn_lastused > '".DataUtil::formatForStore($activetime)."'";

            $sessioninfo =  DBUtil::selectObject('session_info', $where);         
            $userdata['online'] = ($sessioninfo['uid'] == $userdata['pn_uid']) ? true : false; 

        } else {
            // user is anonymous
            $userdata['pn_uname'] = pnModGetVar('Users', 'anonymous');
        }
    }

    if ($makedummy == true) {
        // we create a dummy user, so we need to adjust some of the information
        // gathered so far
        $userdata['pn_name']   = __('**unknown user**', $dom);
        $userdata['pn_uname']  = __('**unknown user**', $dom);
        $userdata['pn_email']  = '';
        $userdata['pn_femail'] = '';
        $userdata['pn_url']    = '';
        $userdata['name']      = __('**unknown user**', $dom);
        $userdata['uname']     = __('**unknown user**', $dom);
        $userdata['email']     = '';
        $userdata['femail']    = '';
        $userdata['url']       = '';
        $userdata['groups']    = array();
    } else {
        $usersarray[$userid] = $userdata;
    }

    return $userdata;
}

/**
 * Returns the total number of posts in the whole system, a forum, or a topic
 * Also can return the number of users on the system.
 *
 * @params $args['id'] int the id, depends on 'type' parameter
 * @params $args['type'] string, defines the id parameter
 * @returns int (depending on type and id)
 */
function Dizkus_userapi_boardstats($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $id   = isset($args['id']) ? $args['id'] : null;
    $type = isset($args['type']) ? $args['type'] : null;

    static $cache = array();

    switch ($type)
    {
        case 'all':
        case 'allposts':
            if (!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_posts');
            }
            
            return $cache[$type];
            break;

        case 'category':
            if (!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_categories');
            }
            
            return  $cache[$type];
            break;

        case 'forum':
            if (!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_forums');
            }
            
            return $cache[$type];
            break;

        case 'topic':
            if (!isset($cache[$type][$id])){
               $cache[$type][$id] = DBUtil::selectObjectCount('dizkus_posts', 'WHERE topic_id = ' .(int)DataUtil::formatForStore($id));
            }
            
            return  $cache[$type][$id];
            break;

        case 'forumposts':
            if (!isset($cache[$type][$id])){
               $cache[$type][$id] = DBUtil::selectObjectCount('dizkus_posts', 'WHERE forum_id = ' .(int)DataUtil::formatForStore($id));
            }
            
            return  $cache[$type][$id];
            break;

        case 'forumtopics':
            if (!isset($cache[$type][$id])){
               $cache[$type][$id] = DBUtil::selectObjectCount('dizkus_topics', 'WHERE forum_id = ' .(int)DataUtil::formatForStore($id));
            }
            
            return  $cache[$type][$id];
            break;

        case 'alltopics':
            if (!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_topics');
            }
            
            return  $cache[$type];
            break;

        case 'allmembers':
            if (!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_users');
            }
            
            return  $cache[$type];
            break;

        case 'lastmember':
        case 'lastuser':
            if (!isset($cache[$type])){
                $res = DBUtil::selectObjectArray('users', null, 'uid DESC', 1, 1);
                $cache[$type] = $res[0]['uname'];
            }
            
            return  $cache[$type];
            break;

        default:
            return showforumerror(__("Error! Wrong parameters in 'Dizkus_userapi_boardstats()'.", $dom), __FILE__, __LINE__);
    }
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
function Dizkus_userapi_get_firstlast_post_in_topic($args)
{
    if (!empty($args['topic_id']) && is_numeric($args['topic_id'])) {
        $pntable = pnDBGetTables();
        $option  = (isset($args['first']) && $args['first'] == true) ? 'MIN' : 'MAX';
        $post_id = DBUtil::selectFieldMax('dizkus_posts', 'post_id', $option, $pntable['dizkus_posts_column']['topic_id'].' = '.(int)$args['topic_id']);

        if ($post_id <> false) {
            if (isset($args['id_only']) && $args['id_only'] == true) {
                return $post_id;
            }
            return Dizkus_userapi_readpost(array('post_id' => $post_id));
        }
    }

    return false;
}

/**
 * get_last_post_in_forum
 * gets the last post in a forum, false if no posts
 *
 * @params $args['forum_id'] int the forums id
 * @params $args['id_only'] boolean if true, only return the id, not the complete post information
 * @returns array with post information of false
 */
function Dizkus_userapi_get_last_post_in_forum($args)
{
    if (!empty($args['forum_id']) && is_numeric($args['forum_id'])) {
        $pntable = pnDBGetTables();
        $post_id = DBUtil::selectfieldMax('dizkus_posts', 'post_id', 'MAX', $pntable['dizkus_posts_column']['forum_id'].' = '.(int)$args['forum_id']);

        if (isset($args['id_only']) && $args['id_only'] == true) {
            return $post_id;
        }

        return Dizkus_userapi_readpost(array('post_id' => $post_id));
    }

    return false;
}

/**
 * readcategorytree
 * read all catgories and forums the recent user has access to
 *
 * @params $args['last_visit'] string the users last visit date as returned from setcookies() function
 * @returns array of categories with an array of forums in the catgories
 *
 */
function Dizkus_userapi_readcategorytree($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $last_visit = (isset($args['last_visit'])) ? $args['last_visit'] : 0;

    static $tree;

    $dizkusvars = pnModGetVar('Dizkus');

    // if we have already called this once during the script
    if (isset($tree)) {
        return $tree;
    }

    $pntable = pnDBGetTables();
    $cattable    = $pntable['dizkus_categories'];
    $forumstable = $pntable['dizkus_forums'];
    $poststable  = $pntable['dizkus_posts'];
    $topicstable = $pntable['dizkus_topics'];
    $userstable  = $pntable['users'];

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
                   ' . $userstable . '.pn_uname AS pn_uname,
                   ' . $userstable . '.pn_uid AS pn_uid,
                   ' . $poststable . '.topic_id AS topic_id,
                   ' . $poststable . '.post_time AS post_time
            FROM ' . $cattable . '
            LEFT JOIN ' . $forumstable . ' ON ' . $forumstable . '.cat_id=' . $cattable . '.cat_id
            LEFT JOIN ' . $poststable . ' ON ' . $poststable . '.post_id=' . $forumstable . '.forum_last_post_id
            LEFT JOIN ' . $topicstable . ' ON ' . $topicstable . '.topic_id=' . $poststable . '.topic_id
            LEFT JOIN ' . $userstable . ' ON ' . $userstable . '.pn_uid=' . $poststable . '.poster_id
            ORDER BY ' . $cattable . '.cat_order, ' . $forumstable . '.forum_order, ' . $forumstable . '.forum_name';
    $res = DBUtil::executeSQL($sql);
    $colarray = array('cat_id', 'cat_title', 'cat_order', 'forum_id', 'forum_name', 'forum_desc', 'forum_topics', 'forum_posts',
                      'forum_last_post_id', 'forum_moduleref', 'forum_pntopic', 'topic_title', 'topic_replies', 'pn_uname', 'pn_uid', 
                      'topic_id', 'post_time');
    $result = DBUtil::marshallObjects($res, $colarray);

    $posts_per_page = pnModGetVar('Dizkus', 'posts_per_page');

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
            $forum['pn_uname']            = $row['pn_uname'];
            $forum['pn_uid']              = $row['pn_uid'];
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
        
                        $posted_unixtime= dzk_str2time($forum['post_time']); // strtotime ($forum['post_time']);
                        $posted_ml = DateUtil::formatDatetime($posted_unixtime, 'datetimebrief');
                        if ($posted_unixtime) {
                            if ($forum['pn_uid']==1) {
                                $username = pnModGetVar('Users', 'anonymous');
                            } else {
                                $username = $forum['pn_uname'];
                            }
        
                            $last_post = DataUtil::formatForDisplay(__f('%1$s<br />by %2$s', array($posted_ml, $username), $dom));
                            $last_post = $last_post.' <a href="' . pnModURL('Dizkus','user','viewtopic', array('topic' => $forum['topic_id'])). '">
                                                      <img src="modules/Dizkus/pnimages/icon_latest_topic.gif" alt="' . $posted_ml . ' ' . $username . '" height="9" width="18" /></a>';
                            // new in 2.0.2 - no more preformattd output
                            $last_post_data['name']     = $username;
                            $last_post_data['subject']  = $topic_title;
                            $last_post_data['time']     = $posted_ml;
                            $last_post_data['unixtime'] = $posted_unixtime;
                            $last_post_data['topic']    = $forum['topic_id'];
                            $last_post_data['post']     = $forum['forum_last_post_id'];
                            $last_post_data['url']      = pnModURL('Dizkus', 'user', 'viewtopic',
                                                                   array('topic' => $forum['topic_id'],
                                                                         'start' => (ceil(($topic_replies + 1)  / $posts_per_page) - 1) * $posts_per_page));
                            $last_post_data['url_anchor'] = $last_post_data['url'] . '#pid' . $forum['forum_last_post_id'];
                        } else {
                            // no posts in forum
                            $last_post = __('No posts', $dom);
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
                        $last_post = __('No posts', $dom);
                    }
                    $forum['last_post']  = $last_post;
                    $forum['forum_mods'] = Dizkus_userapi_get_moderators(array('forum_id' => $forum['forum_id']));
        
                    // is the user subscribed to the forum?
                    $forum['is_subscribed'] = 0;
                    if (Dizkus_userapi_get_forum_subscription_status(array('userid' => pnUserGetVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
                        $forum['is_subscribed'] = 1;
                    }
        
                    // is this forum in the favorite list?
                    $forum['is_favorite'] = 0;
                    if ($dizkusvars['favorites_enabled'] == 'yes') {
                        if (Dizkus_userapi_get_forum_favorites_status(array('userid' => pnUserGetVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
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

/**
 * Returns an array of all the moderators of a forum
 *
 * @params $args['forum_id'] int the forums id
 * @returns array containing the pn_uid as index and the users name as value
 */
function Dizkus_userapi_get_moderators($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $forum_id = isset($args['forum_id']) ? $args['forum_id'] : null;

    $pntable = pnDBGetTables();

    if (!empty($forum_id)) {
        $sql = 'SELECT u.pn_uname, u.pn_uid
                FROM '.$pntable['users'].' u, '.$pntable['dizkus_forum_mods'].' f
                WHERE f.forum_id = '.DataUtil::formatForStore($forum_id).' AND u.pn_uid = f.user_id
                AND f.user_id < 1000000';
    } else {
        $sql = 'SELECT u.pn_uname, u.pn_uid
                FROM '.$pntable['users'].' u, '.$pntable['dizkus_forum_mods'].' f
                WHERE u.pn_uid = f.user_id
                AND f.user_id < 1000000
                GROUP BY f.user_id';
    }
    $res = DBUtil::executeSQL($sql);
    $colarray = array('uname', 'uid');
    $result = DBUtil::marshallObjects($res, $colarray);

    $mods = array();
    if (is_array($result) && !empty($result)) {
        foreach ($result as $user) {
            $mods[$user['uid']] = $user['uname'];
        }
    }

    if (!empty($forum_id)) {
        $sql = 'SELECT g.pn_name, g.pn_gid
                FROM '.$pntable['groups'].' g, '.$pntable['dizkus_forum_mods']." f
                WHERE f.forum_id = '".DataUtil::formatForStore($forum_id)."' AND g.pn_gid = f.user_id-1000000
                AND f.user_id > 1000000";
    } else {
        $sql = 'SELECT g.pn_name, g.pn_gid
                FROM '.$pntable['groups'].' g, '.$pntable['dizkus_forum_mods'].' f
                WHERE g.pn_gid = f.user_id-1000000
                AND f.user_id > 1000000
                GROUP BY f.user_id';
    }
    $res = DBUtil::executeSQL($sql);
    $colarray = array('gname', 'gid');
    $result = DBUtil::marshallObjects($res, $colarray);

    if (is_array($result) && !empty($result)) {
        foreach ($result as $group) {
            $mods[$group['gid'] + 1000000] = $group['gname'];
        }
    }

    return $mods;
}

/**
 * setcookies
 * reads the cookie, updates it and returns the last visit date in readable (%Y-%m-%d %H:%M)
 * and unix time format
 *
 * @params none
 * @returns array of (readable last visits data, unix time last visit date)
 *
 */
function Dizkus_userapi_setcookies()
{
    /**
     * set last visit cookies and get last visit time
     * set LastVisit cookie, which always gets the current time and lasts one year
     */
    $path = pnGetBaseURI();
    if (empty($path)) {
        $path = '/';
    } elseif (substr($path, -1, 1) != '/') {
        $path .= '/';
    }

    setcookie('DizkusLastVisit', time(), time()+31536000, $path);

    if (!isset($_COOKIE['DizkusLastVisitTemp'])){
        $temptime = isset($_COOKIE['DizkusLastVisit']) ? $_COOKIE['DizkusLastVisit'] : '';
    } else {
        $temptime = $_COOKIE['DizkusLastVisitTemp'];
    }

    if (empty($temptime)) {
        // check for old Cookies
        // TO-DO: remove this code in 3.2 or a bit later
        if (!isset($_COOKIE['phpBBLastVisitTemp'])){
            $temptime = isset($_COOKIE['phpBBLastVisit']) ? $_COOKIE['phpBBLastVisit'] : '';
        } else {
            $temptime = $_COOKIE['phpBBLastVisitTemp'];
        }
    }

    if (empty($temptime)) {
        $temptime = 0;
    }

    // set LastVisitTemp cookie, which only gets the time from the LastVisit and lasts for 30 min
    setcookie('DizkusLastVisitTemp', $temptime, time()+1800, $path);

    // set vars for all scripts
    $last_visit = DateUtil::formatDatetime($temptime, '%Y-%m-%d %H:%M');

    return array($last_visit, $temptime);
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
 * @returns very complex array, see <!--[ debug ]--> for more information
 */
function Dizkus_userapi_readforum($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                          array('forum_id' => $args['forum_id'],
                                'permcheck' => 'nocheck' ));
    if ($forum == false) {
        return showforumerror(__('Error! The forum or topic you selected was not found. Please go back and try again.', $dom), __FILE__, __LINE__, '404 Not Found');
    }

    if (!allowedtoseecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(getforumerror('auth_overview',$forum['forum_id'], 'forum', __('Error! You do not have authorisation to view this category or forum.', $dom)), __FILE__, __LINE__);
    }

    $pntable = pnDBGetTables();

    $posts_per_page  = pnModGetVar('Dizkus', 'posts_per_page');
    if (empty($args['topics_per_page'])) {
        $args['topics_per_page'] = pnModGetVar('Dizkus', 'topics_per_page');
    }
    $hot_threshold   = pnModGetVar('Dizkus', 'hot_threshold');
    $post_sort_order = pnModAPIFunc('Dizkus','user','get_user_post_order');

    // read moderators
    $forum['forum_mods'] = Dizkus_userapi_get_moderators(array('forum_id' => $forum['forum_id']));
    $forum['last_visit'] = $args['last_visit'];

    $forum['topic_start'] = (!empty ($args['start'])) ? $args['start'] : 0;

    // is the user subscribed to the forum?
    $forum['is_subscribed'] = 0;
    if (Dizkus_userapi_get_forum_subscription_status(array('userid' => pnUserGetVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
        $forum['is_subscribed'] = 1;
    }

    // is this forum in the favorite list?
    $forum['is_favorite'] = 0;
    if (Dizkus_userapi_get_forum_favorites_status(array('userid' => pnUserGetVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
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
    $ignorelist_setting = pnModAPIFunc('Dizkus','user','get_settings_ignorelist',array('uid' => pnUserGetVar('uid')));
    if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
        // get user's ignore list
        $ignored_users = pnModAPIFunc('ContactList','user','getallignorelist',array('uid' => pnUserGetVar('uid')));
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
                   u.pn_uname,
                   u2.pn_uname as last_poster,
                   p.post_time,
                   t.topic_poster
            FROM ' . $pntable['dizkus_topics'] . ' AS t
            LEFT JOIN ' . $pntable['users'] . ' AS u ON t.topic_poster = u.pn_uid
            LEFT JOIN ' . $pntable['dizkus_posts'] . ' AS p ON t.topic_last_post_id = p.post_id
            LEFT JOIN ' . $pntable['users'] . ' AS u2 ON p.poster_id = u2.pn_uid
            WHERE t.forum_id = ' .(int)DataUtil::formatForStore($forum['forum_id']) . '
            ' . $whereignorelist . '
            ORDER BY t.sticky DESC, p.post_time DESC';
            //ORDER BY t.sticky DESC"; // RNG
            //ORDER BY t.sticky DESC, p.post_time DESC";
//FC            ORDER BY t.sticky DESC"; // RNG
//FC            //ORDER BY t.sticky DESC, p.post_time DESC";

    $res = DBUtil::executeSQL($sql, $args['start'], $args['topics_per_page']);
    $colarray = array('topic_id', 'topic_title', 'topic_views', 'topic_replies', 'sticky', 'topic_status',
                      'topic_last_post_id', 'topic_poster', 'pn_uname', 'last_poster', 'post_time');
    $result    = DBUtil::marshallObjects($res, $colarray);

//    $forum['forum_id'] = $forum['forum_id'];
    $forum['topics']   = array();

    if (is_array($result) && !empty($result)) {
        foreach ($result as $topic) {
            if ($topic['last_poster'] == 'Anonymous') {
                $topic['last_poster'] = pnModGetVar('Users', 'anonymous');
            }
            if ($topic['pn_uname'] == 'Anonymous') {
                $topic['pn_uname'] = pnModGetVar('Users', 'anonymous');
            }
            $topic['total_posts'] = $topic['topic_replies'] + 1;
        
            $topic['post_time_unix'] = dzk_str2time($topic['post_time']); //strtotime ($topic['post_time']);
            $posted_ml = DateUtil::formatDatetime($topic['post_time_unix'], 'datetimebrief');
            $topic['last_post'] = DataUtil::formatForDisplay(__f('%1$s<br />by %2$s', array($posted_ml, $topic['last_poster']), $dom));
        
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
        
                $pagination .= '&nbsp;&nbsp;&nbsp;<span class="z-sub">(' . DataUtil::formatForDisplay(__('Go to page', $dom)) . '&nbsp;';
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
        
                    if ($skippages != 1 || $lastpage) {
                        if ($x!=0) $pagination .= ', ';
                        $pagination .= '<a href="' . pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic['topic_id'], 'start' => $start)) . '" title="' . $topic['topic_title'] . ' ' . DataUtil::formatForDisplay(__('Page #', $dom)) . ' ' . $pagenr . '">' . $pagenr . '</a>';
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
            $topic['last_post_url'] = pnModURL('Dizkus', 'user', 'viewtopic',
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

// RNG
function cmp_forumtopicsort($a, $b)
{
    return strcmp($a['post_time_unix'], $b['post_time_unix']) * -1;
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
 * @returns very complex array, see <!--[ debug ]--> for more information
 */
function Dizkus_userapi_readtopic($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $dizkusvars      = pnModGetVar('Dizkus');
    $posts_per_page  = $dizkusvars['posts_per_page'];
    $topics_per_page = $dizkusvars['topics_per_page'];

    $post_sort_order = pnModAPIFunc('Dizkus','user','get_user_post_order');

    $complete = (isset($args['complete'])) ? $args['complete'] : false;
    $count    = (isset($args['count'])) ? $args['count'] : false;
    $start    = (isset($args['start'])) ? $args['start'] : -1;
    $hooks    = (isset($args['nohook']) && $args['nohook'] == false) ? false : true;

    $currentuserid = pnUserGetVar('uid');
    $now = time();
    $timespanforchanges = !empty($dizkusvars['timespanforchanges']) ? $dizkusvars['timespanforchanges'] : 24;
    $timespansecs = $timespanforchanges * 60 * 60;

    $pntable = pnDBGetTables();

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
            FROM  '.$pntable['dizkus_topics'].' t
            LEFT JOIN '.$pntable['dizkus_forums'].' f ON f.forum_id = t.forum_id
            LEFT JOIN '.$pntable['dizkus_categories'].' AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '.(int)DataUtil::formatForStore($args['topic_id']);

    $res = DBUtil::executeSQL($sql);
    $colarray = array('topic_title', 'topic_poster', 'topic_status', 'forum_id', 'sticky', 'topic_time', 'topic_replies',
                      'topic_last_post_id', 'forum_name', 'cat_id', 'forum_pop3_active', 'cat_title');
    $result    = DBUtil::marshallObjects($res, $colarray);

    // integrate contactlist's ignorelist here (part 1/2)
    $ignored_uids = array();
    $ignorelist_setting = pnModAPIFunc('Dizkus','user','get_settings_ignorelist',array('uid' => pnUserGetVar('uid')));
    if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
        // get user's ignore list
        $ignored_users = pnModAPIFunc('ContactList','user','getallignorelist',array('uid' => pnUserGetVar('uid')));
        $ignored_uids = array();
        foreach ($ignored_users as $item) {
            $ignored_uids[] = (int)$item['iuid'];
        }
    }

    if (is_array($result) && !empty($result)) {
        $topic = $result[0];
        $topic['topic_id'] = $args['topic_id'];
        $topic['start'] = $start;
        $topic['topic_unixtime'] = dzk_str2time($topic['topic_time']); //strtotime ($topic['topic_time']);
        $topic['post_sort_order'] = $post_sort_order;

        // pop3_active contains the external source (if any), create the correct var name
        // 0 - no external source
        // 1 - mail
        // 2 - rss
        $topic['externalsource'] = $topic['forum_pop3_active'];
        // kill the wrong var
        unset($topic['forum_pop3_active']);

        if (!allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id'])) {
            return showforumerror(getforumerror('auth_read',$topic['forum_id'], 'forum', __('Error! You do not have authorisation to read the content in this category or forum.', $dom)), __FILE__, __LINE__);
        }

        $topic['forum_mods'] = Dizkus_userapi_get_moderators(array('forum_id' => $topic['forum_id']));

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
            $topic['access_topicsubjectedit'] = (pnUserGetVar('uid') == $topic['topic_poster']);
        }

        // get the next and previous topic_id's for the next / prev button
        $topic['next_topic_id'] = Dizkus_userapi_get_previous_or_next_topic_id(array('topic_id' => $topic['topic_id'], 'view'=>'next'));
        $topic['prev_topic_id'] = Dizkus_userapi_get_previous_or_next_topic_id(array('topic_id' => $topic['topic_id'], 'view'=>'previous'));

        // get the users topic_subscription status to show it in the quick repliy checkbox
        // correctly
        if (Dizkus_userapi_get_topic_subscription_status(array('userid'   => $currentuserid,
                                                               'topic_id' => $topic['topic_id'])) == true) {
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
        $topic['total_posts'] = Dizkus_userapi_boardstats(array('id' => $topic['topic_id'], 'type' => 'topic'));

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

        if (is_array($result2) && !empty($result2)){
            foreach ($result2 as $post) {
                $post['topic_id'] = $topic['topic_id'];
            
                // check if array_key_exists() with poster _id in $userdata
                //if (!array_key_exists($post['poster_id'], $userdata)) {
                if (!isset($userdata[$post['poster_id']])) {
                    // not in array, load the data now...
                    $userdata[$post['poster_id']] = Dizkus_userapi_get_userdata_from_id(array('userid' => $post['poster_id']));
                }
                // we now have the data and use them
                $post['poster_data'] = $userdata[$post['poster_id']];
                $post['posted_unixtime'] = dzk_str2time($post['post_time']); //strtotime ($post['post_time']);
                // we use br2nl here for backwards compatibility
                //$message = phpbb_br2nl($message);
                //$post['post_text'] = phpbb_br2nl($post['post_text']);
            
                $post['post_text'] = Dizkus_replacesignature($post['post_text'], $post['poster_data']['_SIGNATURE']);
            
                if ($hooks == true) {
                    // call hooks for $message
                    list($post['post_text']) = pnModCallHooks('item', 'transform', $post['post_id'], array($post['post_text']));
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
                    //pnModGetVar('Dizkus', 'log_ip') == 'yes') {
                    // user is allowed to see ip
                    $post['poster_data']['seeip'] = true;
                }
                
                $post['poster_data']['moderate'] = false;
                $post['poster_data']['edit'] = false;
                if ($topic['access_moderate'] || $topic['access_admin']) {
                    // user is allowed to moderate
                    $post['poster_data']['moderate'] = true;
                    $post['poster_data']['edit'] = true;
                } elseif ($post['poster_data']['pn_uid'] == $currentuserid) {
                    // user is allowed to moderate || own post
                    // if the timespanforchanges (in hrs!) setting allows it
                    // timespanforchanges is in hours, but we need seconds:
                    if (($now - $post['posted_unixtime']) <= $timespansecs ) {
                        $post['poster_data']['edit'] = true;
                    }
                }
            
                // integrate contactlist's ignorelist here (part 2/2)
                // the added variable will be handled in templates
                if (in_array($post['poster_id'], $ignored_uids)) $post['contactlist_ignored'] = 1;
            
                array_push($topic['posts'], $post);
            }
        }
        unset($userdata);
    } else {
        // no results - topic does not exist
        return showforumerror(__('Error! The topic you selected was not found. Please go back and try again.', $dom), __FILE__, __LINE__, '404 Not Found');
    }

    return $topic;
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
 * @returns very complex array, see <!--[ debug ]--> for more information
 */
function Dizkus_userapi_preparereply($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();

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
                       u.pn_uname
                FROM '.$pntable['dizkus_forums'].' AS f,
                     '.$pntable['dizkus_topics'].' AS t,
                     '.$pntable['dizkus_posts'].' AS p,
                     '.$pntable['users'].' AS u
                WHERE (p.post_id = '.(int)DataUtil::formatForStore($args['post_id']).')
                AND (t.forum_id = f.forum_id)
                AND (p.topic_id = t.topic_id)
                AND (p.poster_id = u.pn_uid)';
        $colarray = array('forum_id', 'cat_id', 'topic_id', 'topic_title', 'topic_status', 'post_text', 'post_time', 'pn_uname');
    } else {
        // No post id, just check topic.
        // reply without quote
        $sql = 'SELECT f.forum_id,
                       f.cat_id,
                       t.topic_id,
                       t.topic_title,
                       t.topic_status
                FROM '.$pntable['dizkus_forums'].' AS f,
                     '.$pntable['dizkus_topics'].' AS t
                WHERE (t.topic_id = '.(int)DataUtil::formatForStore($args['topic_id']).')
                AND (t.forum_id = f.forum_id)';
        $colarray = array('forum_id', 'cat_id', 'topic_id', 'topic_title', 'topic_status');
    }
    $res = DBUtil::executeSQL($sql);
    $result = DBUtil::marshallObjects($res, $colarray);

    if (!is_array($result) || empty($result)) {
        return showforumerror(__('Error! The topic you selected was not found. Please go back and try again.', $dom), __FILE__, __LINE__);
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
        $reply['message'] = '[quote='.$reply['pn_uname'].']'.$text.'[/quote]';
    } else {
        $reply['message'] = '';
    }

    // anonymous user has uid=0, but needs pn_uid=1
    // also check subscription status here
    if (!pnUserLoggedin()) {
        $pn_uid = 1;
        $reply['attach_signature'] = false;
        $reply['subscribe_topic'] = false;
    } else {
        $pn_uid = pnUserGetVar('uid');
        // get the users topic_subscription status to show it in the quick repliy checkbox
        // correctly
        if ($args['reply_start']==true) {
            $reply['attach_signature'] = true;
            $reply['subscribe_topic'] = false;
            $is_subscribed = Dizkus_userapi_get_topic_subscription_status(array('userid'   => $pn_uid,
                                                                                'topic_id' => $reply['topic_id']));

            if ($is_subscribed == true || pnModGetVar('Dizkus', 'autosubscribe') == 'yes') {
                $reply['subscribe_topic'] = true;
            } else {
                $reply['subscribe_topic'] = false;
            }
        } else {
            $reply['attach_signature'] = $args['attach_signature'];
            $reply['subscribe_topic'] = $args['subscribe_topic'];
        }
    }
    $reply['poster_data'] = Dizkus_userapi_get_userdata_from_id(array('userid' => $pn_uid));

    if ($reply['topic_status'] == 1) {
        return showforumerror(__('Error! You cannot post a message under this topic. It has been locked.', $dom), __FILE__, __LINE__);
    }

    if (!allowedtowritetocategoryandforum($reply['cat_id'], $reply['forum_id'])) {
        return showforumerror( __('Error! You do not have authorisation to post in this category or forum.', $dom), __FILE__, __LINE__);
    }

    // Topic review (show last 10)
    $sql = 'SELECT p.post_id,
                   p.poster_id,
                   p.post_time,
                   p.post_text,
                   t.topic_title
            FROM '.$pntable['dizkus_posts'].' p
            LEFT JOIN '.$pntable['dizkus_topics'].' t ON t.topic_id=p.topic_id
            WHERE p.topic_id = ' . (int)DataUtil::formatForStore($reply['topic_id']) . ' 
            ORDER BY p.post_id DESC';

    $res = DBUtil::executeSQL($sql, -1, 10);
    $colarray = array('post_id', 'poster_id', 'post_time', 'post_text', 'topic_title');
    $result    = DBUtil::marshallObjects($res, $colarray);

    $reply['topic_review'] = array();
    if (is_array($result) && !empty($result)) {
        foreach ($result as $review) {
            $review['user_name'] = pnUserGetVar('uname', $review['poster_id']);
            if ($review['user_name'] == '') {
                // user deleted from the db?
                $review['poster_id'] = 1;
            }
        
            $review['poster_data'] = Dizkus_userapi_get_userdata_from_id(array('userid' => $review['poster_id']));
        
            // TODO extract unixtime directly from MySql
            $review['post_unixtime'] = dzk_str2time($review['post_time']); //strtotime ($review['post_time']);
            $review['post_ml'] = DateUtil::formatDatetime($review['post_unixtime'], 'datetimebrief');
        
            $message = $review['post_text'];
            // we use br2nl here for backward compatibility
            $message = phpbb_br2nl($message);
            // Before we insert the sig, we have to strip its HTML if HTML is disabled by the admin.
        
            // We do this _before_ pn_bbencode(), otherwise we'd kill the bbcode's html.
            $message = Dizkus_replacesignature($message, $review['poster_data']['_SIGNATURE']);
        
            // call hooks for $message
            list($message) = pnModCallHooks('item', 'transform', $review['post_id'], array($message));
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
function Dizkus_userapi_storereply($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    list($forum_id, $cat_id) = pnModAPIFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                            array('topic_id' => $args['topic_id']));

    if (!allowedtowritetocategoryandforum($cat_id, $forum_id)) {
        return showforumerror(__('Error! You do not have authorisation to post in this category or forum.', $dom));
    }

    if (trim($args['message']) == '') {
        return showforumerror(__('Error! You tried to post a blank message. Please go back and try again.', $dom), __FILE__, __LINE__);
    }

    /*
    it's a submitted page and message is not empty
    */

    // grab message for notification
    // without html-specialchars, bbcode, smilies <br> and [addsig]
    $posted_message = stripslashes($args['message']);

    // signature is always on, except anonymous user
    // anonymous user has uid=0, but needs pn_uid=1
    $islogged = pnUserLoggedIn();
    if ($islogged) {
        if ($args['attach_signature'] == 1) {
            $args['message'] .= '[addsig]';
        }
        $pn_uid = pnUserGetVar('uid');
    } else {
        $pn_uid = 1;
    }

    // sync the current user, so that new users
    // get into the Dizkus database
    pnModAPIFunc('Dizkus', 'admin', 'sync', array('id' => $pn_uid, 'type' => 'user')); 

    if (pnModGetVar('Dizkus', 'log_ip') == 'no') {
        // for privacy issues ip logging can be deactivated
        $poster_ip = '127.0.0.1';
    } else {
        // some enviroment for logging ;)
        $poster_ip = pnServerGetVar('HTTP_X_FORWARDED_FOR');
        if (empty($poster_ip)){
            $poster_ip = pnServerGetVar('REMOTE_ADDR');
        }
    }

    // Prep for DB is done by DBUtil
    $obj['post_time']  = date('Y-m-d H:i');
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
        // user logged in we have to update users-table
        DBUtil::incrementObjectFieldByID('dizkus_users', 'user_posts', $obj['poster_id'], 'user_id');

        // update subscription
        if ($args['subscribe_topic']==1) {
            // user wants to subscribe the topic
            Dizkus_userapi_subscribe_topic(array('topic_id' => $obj['topic_id']));
        } else {
            // user wants not to subscribe the topic
            Dizkus_userapi_unsubscribe_topic(array('topic_id' => $obj['topic_id'],
                                                   'silent'   => true));
        }
    }

    // update forums table
    $fobj['forum_last_post_id'] = $obj['post_id'];
    $fobj['forum_id']           = $obj['forum_id'];
    DBUtil::updateObject($fobj, 'dizkus_forums', null, 'forum_id');
    DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_posts', $obj['forum_id'], 'forum_id');

    // get the last topic page
    $start = Dizkus_userapi_get_last_topic_page(array('topic_id' => $obj['topic_id']));

    // Let any hooks know that we have created a new item.
    //pnModCallHooks('item', 'create', $this_post, array('module' => 'Dizkus'));
    pnModCallHooks('item', 'update', $obj['topic_id'], array('module' => 'Dizkus',
                                                      'post_id' => $obj['post_id']));

    Dizkus_userapi_notify_by_email(array('topic_id' => $obj['topic_id'], 'poster_id' => $obj['poster_id'], 'post_message' => $posted_message, 'type' => '2'));

    return array($start, $obj['post_id']);
}

/**
 * get_topic_subscription_status
 *
 * @params $args['userid'] int the users pn_uid
 * @params $args['topic_id'] int the topic id
 * @returns bool true if the user is subscribed or false if not
 */
function Dizkus_userapi_get_topic_subscription_status($args)
{
    $pntables = pnDBGetTables();
    $tsubcolumn = $pntables['dizkus_topic_subscription_column'];

    $where = ' WHERE ' . $tsubcolumn['user_id'] . '=' . (int)DataUtil::formatForStore($args['userid']) . 
             ' AND '   . $tsubcolumn['topic_id'] . '=' . (int)DataUtil::formatForStore($args['topic_id']);

    $count = DBUtil::selectObjectCount('dizkus_topic_subscription', $where);
    return $count > 0;
}

/**
 * get_forum_subscription_status
 *
 * @params $args['userid'] int the users pn_uid
 * @params $args['forum_id'] int the forums id
 * @returns bool true if the user is subscribed or false if not
 */
function Dizkus_userapi_get_forum_subscription_status($args)
{
    static $cache = array();

    if (!isset($cache[$args['userid']])) {
        $pntables = pnDBGetTables();
        $subcolumn = $pntables['dizkus_subscription_column'];

        $where = $subcolumn['user_id'] . '=' . (int)DataUtil::formatForStore($args['userid']);
        $cache[$args['userid']] = DBUtil::selectFieldMaxArray ('dizkus_subscription', 'msg_id', 'COUNT', $where, 'forum_id');
    }

    $count = isset($cache[$args['userid']][$args['forum_id']]) ? $cache[$args['userid']][$args['forum_id']] : 0;

    return $count > 0;
}

/**
 * get_forum_favorites_status
 *
 * @params $args['userid'] int the users pn_uid
 * @params $args['forum_id'] int the forums id
 * @returns bool true if the user is subscribed or false if not
 */
function Dizkus_userapi_get_forum_favorites_status($args)
{
    static $cache = array();

    if (!isset($cache[$args['userid']])){
        $pntables = pnDBGetTables();
        $subcolumn = $pntables['dizkus_subscription_column'];

        $where = $subcolumn['user_id'] . '=' . (int)DataUtil::formatForStore($args['userid']);
        $cache[$args['userid']] = DBUtil::selectFieldMaxArray ('dizkus_forum_favorites', 'forum_id', 'COUNT', $where, 'forum_id');
    }

    $count = isset($cache[$args['userid']][$args['forum_id']]) ? $cache[$args['userid']][$args['forum_id']] : 0;

    return $count > 0;
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
function Dizkus_userapi_preparenewtopic($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();

    $newtopic = array();
    $newtopic['forum_id'] = $args['forum_id'];

    // select forum name and cat title based on forum_id
    $sql = "SELECT f.forum_name,
                   c.cat_id,
                   c.cat_title
            FROM ".$pntable['dizkus_forums']." AS f,
                ".$pntable['dizkus_categories']." AS c
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
        return showforumerror(__('Error! You do not have authorisation to post in this category or forum.', $dom), __FILE__, __LINE__);
    }

    $newtopic['poster_data'] = Dizkus_userapi_get_userdata_from_id(array('userid' => pnUserGetVar('uid')));

    $newtopic['subject'] = $args['subject'];
    $newtopic['message'] = $args['message'];
    $newtopic['message_display'] = $args['message']; // phpbb_br2nl($args['message']);

    list($newtopic['message_display']) = pnModCallHooks('item', 'transform', '', array($newtopic['message_display']));
    $newtopic['message_display'] = nl2br($newtopic['message_display']);

    if (pnUserLoggedIn()) {
        if ($args['topic_start'] == true) {
            $newtopic['attach_signature'] = true;
            $newtopic['subscribe_topic']  = (pnModGetVar('Dizkus', 'autosubscribe')=='yes') ? true : false;
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
function Dizkus_userapi_storenewtopic($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $cat_id = Dizkus_userapi_get_forum_category(array('forum_id' => $args['forum_id']));
    if (!allowedtowritetocategoryandforum($cat_id, $args['forum_id'])) {
        return showforumerror(__('Error! You do not have authorisation to post in this category or forum.', $dom), __FILE__, __LINE__);
    }

    if (trim($args['message']) == '' || trim($args['subject']) == '') {
        // either message or subject is empty
        return showforumerror(__('Error! You tried to post a blank message. Please go back and try again.', $dom), __FILE__, __LINE__);
    }

    /*
    it's a submitted page and message and subject are not empty
    */

    //  grab message for notification
    //  without html-specialchars, bbcode, smilies <br /> and [addsig]
    $posted_message = stripslashes($args['message']);

    //  anonymous user has uid=0, but needs pn_uid=1
    if (isset($args['post_as']) && !empty($args['post_as']) && is_numeric($args['post_as'])) {
        $pn_uid = $args['post_as'];
    } else {
        if (pnUserLoggedin()) {
            if ($args['attach_signature'] == 1) {
                $args['message'] .= '[addsig]';
            }
            $pn_uid = pnUserGetVar('uid');
        } else  {
            $pn_uid = 1;
        }
    }

    // sync the current user, so that new users
    // get into the Dizkus database
    pnModAPIFunc('Dizkus', 'admin', 'sync', array('id' => $pn_uid, 'type' => 'user'));

    // some enviroment for logging ;)
    if (pnServerGetVar('HTTP_X_FORWARDED_FOR')){
        $poster_ip = pnServerGetVar('HTTP_X_FORWARDED_FOR');
    } else {
        $poster_ip = pnServerGetVar('REMOTE_ADDR');
    }
    // for privavy issues ip logging can be deactivated
    if (pnModGetVar('Dizkus', 'log_ip') == 'no') {
        $poster_ip = '127.0.0.1';
    }

    $time = (isset($args['time'])) ? $args['time'] : DateUtil::getDatetime('', '%Y-%m-%d %H:%M');

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
        pnModCallHooks('item', 'create', $obj['topic_id'], array('module' => 'Dizkus'));
    }

    if (pnUserLoggedin()) {
        // user logged in we have to update users-table
        DBUtil::incrementObjectFieldByID('dizkus_users', 'user_posts', $obj['topic_poster'], 'user_id');

        // update subscription
        if ($args['subscribe_topic'] == 1) {
            // user wants to subscribe the new topic
            Dizkus_userapi_subscribe_topic(array('topic_id' => $topic_id));
        }
    }

    // update forums-table
    $fobj['forum_id']           = $obj['forum_id'];
    $fobj['forum_last_post_id'] = $pobj['post_id'];
    DBUtil::updateObject($fobj, 'dizkus_forums', null, 'forum_id');
    DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_posts',  $obj['forum_id'], 'forum_id');
    DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_topics', $obj['forum_id'], 'forum_id');

    // notify for newtopic
    Dizkus_userapi_notify_by_email(array('topic_id' => $obj['topic_id'], 'poster_id' => $obj['topic_poster'], 'post_message' => $posted_message, 'type' => '0'));

    // delete temporary session var
    SessionUtil::delVar('topic_started');

    //  switch to topic display
    return $obj['topic_id'];
}


/**
 * readpost
 * reads a single posting
 *
 * @params $args['post_id'] int the postings id
 * @returns array with posting information...
 */
function Dizkus_userapi_readpost($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();
    $postscols = DBUtil::_getAllColumnsQualified('dizkus_posts', 'p');

    $sql = 'SELECT '. $postscols .',
                      t.topic_title,
                      t.topic_replies,
                      f.forum_name,
                      c.cat_title,
                      c.cat_id
            FROM '.$pntable['dizkus_posts'].' p
            LEFT JOIN '.$pntable['dizkus_topics'].' t ON t.topic_id = p.topic_id
            LEFT JOIN '.$pntable['dizkus_forums'].' f ON f.forum_id = t.forum_id
            LEFT JOIN '.$pntable['dizkus_categories'].' c ON c.cat_id = f.cat_id
            WHERE p.post_id = '.(int)DataUtil::formatForStore($args['post_id']);

    $result = DBUtil::executeSQL($sql);
    if ($result === false) {
        return showforumerror(__('Error! The topic you selected was not found. Please go back and try again.', $dom), __FILE__, __LINE__, '404 Not Found');
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
        return showforumerror(getforumerror('auth_read',$post['forum_id'], 'forum', __('Error! You do not have authorisation to read the content in this category or forum.', $dom)), __FILE__, __LINE__);
    }
    
    $post['post_id']      = DataUtil::formatForDisplay($post['post_id']);
    $post['post_time']    = DataUtil::formatForDisplay($post['post_time']);
    $message              = $post['post_text'];
    $post['has_signature']= (substr($message, -8, 8)=='[addsig]');
    $post['post_rawtext'] = Dizkus_replacesignature($message, '');
    $post['post_rawtext'] = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $post['post_rawtext']);
    $post['post_rawtext'] = str_replace('<br />', '', $post['post_rawtext']);

    $post['topic_id']     = DataUtil::formatForDisplay($post['topic_id']);
    $post['topic_rawsubject']= strip_tags($post['topic_title']);
    $post['topic_subject']= DataUtil::formatForDisplay($post['topic_title']);
    $post['topic_replies']= DataUtil::formatForDisplay($post['topic_replies']);
    $post['forum_id']     = DataUtil::formatForDisplay($post['forum_id']);
    $post['forum_name']   = DataUtil::formatForDisplay($post['forum_name']);
    $post['cat_title']    = DataUtil::formatForDisplay($post['cat_title']);
    $post['cat_id']       = DataUtil::formatForDisplay($post['cat_id']);
    $post['poster_data'] = Dizkus_userapi_get_userdata_from_id(array('userid' => $post['poster_id']));

    // create unix timestamp
    $post['post_unixtime'] = dzk_str2time($post['post_time']); //strtotime ($post['post_time']);
    $post['posted_unixtime'] = $post['post_unixtime'];

    $pn_uid = pnUserGetVar('uid');
    $post['moderate'] = false;
    if (allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        $post['moderate'] = true;
    }

    $post['poster_data']['edit'] = false;
    $post['poster_data']['reply'] = false;
    $post['poster_data']['seeip'] = false;
    $post['poster_data']['moderate'] = false;

    if ($post['poster_data']['pn_uid']==$pn_uid) {
        // user is allowed to moderate || own post
        $post['poster_data']['edit'] = true;
    }
    if (allowedtowritetocategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is allowed to reply
        $post['poster_data']['reply'] = true;
    }

    if (allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id']) &&
        pnModGetVar('Dizkus', 'log_ip') == 'yes') {
        // user is allowed to see ip
        $post['poster_data']['seeip'] = true;
    }
    if (allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is allowed to moderate
        $post['poster_data']['moderate'] = true;
        $post['poster_data']['edit'] = true;
    }

    $post['post_textdisplay'] = phpbb_br2nl($message);
    $post['post_textdisplay'] = Dizkus_replacesignature($post['post_textdisplay'], $post['poster_data']['_SIGNATURE']);

    // call hooks for $message_display ($message remains untouched for the textarea)
    list($post['post_textdisplay']) = pnModCallHooks('item', 'transform', $post['post_id'], array($post['post_textdisplay']));
    $post['post_textdisplay'] = dzkVarPrepHTMLDisplay($post['post_textdisplay']);
    $post['post_text'] = $post['post_textdisplay'];

    // allow to edit the subject if first post
    $post['first_post'] = Dizkus_userapi_is_first_post(array('topic_id' => $post['topic_id'], 'post_id' => $post['post_id']));

    return $post;
}

/**
 * Check if this is the first post in a topic.
 *
 * @params $args['topic_id'] int the topics id
 * @params $args['post_id'] int the postings id
 * @returns boolean
 */
function Dizkus_userapi_is_first_post($args)
{
    // compare the given post_id with the lowest post_id in the topic
    $minpost = pnModAPIFunc('Dizkus', 'user', 'get_firstlast_post_in_topic', 
                             array('topic_id' => $args['topic_id'],
                                   'first'    => true,
                                   'id_only'  => true)); 

    return ($minpost == $args['post_id']) ? true : false;
}

/**
 * update post
 * updates a posting in the db after editing it
 *
 * @params $args['post_id'] int the postings id
 * @params $args['topic_id'] int the topic id (might be empty!!!)
 * @params $args['subject'] string the subject
 * @params $args['message'] string the text
 * @params $args['delete'] boolean true if the posting is to be deleted
 * @params $args['attach_signature'] boolean true if the addsig place holder has to be appended
 * @returns string url to redirect to after action (topic of forum if the (last) posting has been deleted)
 */
function Dizkus_userapi_updatepost($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!isset($args['topic_id']) || empty($args['topic_id']) || !is_numeric($args['topic_id'])) {
        $args['topic_id'] = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_postid', array('post_id' => $args['post_id']));
    }

    $pntable = pnDBGetTables();

    $sql = "SELECT p.poster_id,
                   p.forum_id,
                   t.topic_status,
                   f.cat_id
            FROM  ".$pntable['dizkus_posts']." as p,
                  ".$pntable['dizkus_topics']." as t,
                  ".$pntable['dizkus_forums']." as f
            WHERE (p.post_id = '".(int)DataUtil::formatForStore($args['post_id'])."')
              AND (t.topic_id = p.topic_id)
              AND (f.forum_id = p.forum_id)";
    $res = DBUtil::executeSQL($sql);
    $colarray = array('poster_id', 'forum_id', 'topic_status', 'cat_id');
    $result = DBUtil::marshallObjects($res, $colarray);
    $row = $result[0];

    if (!is_array($row)) {
        return showforumerror(__('Error! The topic you selected was not found. Please go back and try again.', $dom), __FILE__, __LINE__);
    }

    if (trim($args['message']) == '') {
        // no message
        return showforumerror( __('Error! You tried to post a blank message. Please go back and try again.', $dom), __FILE__, __LINE__);
    }

    if ((($row['poster_id'] != pnUserGetVar('uid')) || ($row['topic_status'] == 1)) &&
        !allowedtomoderatecategoryandforum($row['cat_id'], $row['forum_id'])) {
        // user is not allowed to edit post
        return showforumerror(getforumerror('auth_mod', $row['forum_id'], 'forum', __('Error! You do not have authorisation to moderate this category or forum.', $dom)), __FILE__, __LINE__);
    }


    if (empty($args['delete'])) {

        // update the posting
        if (!allowedtoadmincategoryandforum($row['cat_id'], $row['forum_id'])) {
            // if not admin then add a edited by line
            // If it's been edited more than once, there might be old "edited by" strings with
            // escaped HTML code in them. We want to fix this up right here:
            $args['message'] = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $args['message']);
            // who is editing?
            $edit_name  = pnUserLoggedIn() ? pnUserGetVar('uname') : pnModGetVar('Users', 'anonymous');
            $edit_date = DateUtil::formatDatetime('', 'datetimebrief');
            $args['message'] .= '<br /><br /><!-- editby --><br /><br /><em>' . __f('Edited by %1$s on %2$s.', array($edit_name, $edit_date), $dom) . '</em><!-- end editby --> ';
        }

        // add signature placeholder
        if ($row['poster_id'] <> 1 && $args['attach_signature'] == true){
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
        //pnModCallHooks('item', 'update', $post_id, array('module' => 'Dizkus'));
        pnModCallHooks('item', 'update', $args['post_id'], array('module'  => 'Dizkus',
                                                                  'post_id' => $args['post_id']));

        // update done, return now
        return pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $args['topic_id']));

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
        $topic = DBUtil::selectObjectById('dizkus_topics', $args['topic_id'], 'topic_id');
        // read the raw forum
        $forum = DBUtil::selectObjectById('dizkus_forums', $firstpost['forum_id'], 'forum_id');
        
        if ($args['post_id'] == $lastpost['post_id']) {
            // posting is the last one in the array
            // if it is the first one too, delete the topic
            if ($args['post_id'] == $firstpost['post_id']) {
                // ... and it is also the first posting in the topic, so we can simply
                // delete the complete topic
                // this also adjusts the counters
                pnModAPIFunc('Dizkus', 'user', 'deletetopic', array('topic_id' => $args['topic_id']));
                // cannot return to the topic, must return to the forum
                return pnRedirect(pnModURL('Dizkus', 'user', 'viewforum', array('forum' => $row['forum_id'])));
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
        DBUtil::decrementObjectFieldByID('dizkus_users', 'user_posts', $post_to_delete['poster_id'], 'user_id');
         
         // update forum       
        DBUtil::updateObject($forum, 'dizkus_forums', null, 'forum_id');
        
        // update topic
        DBUtil::updateObject($topic, 'dizkus_topics', null, 'topic_id');

        if(SessionUtil::getVar('zk_ajax_call', '')  <> 'ajax') {
            return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
                              array('topic' => $topic['topic_id'])));
        }
    }

    // we should not get here, but who knows...
    return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
}

/**
 * get_viewip_data
 *
 * @params $args['post_id] int the postings id
 * @returns array with informstion ...
 */
function Dizkus_userapi_get_viewip_data($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();

    $viewip['poster_ip'] = DBUtil::selectField('dizkus_posts', 'poster_ip', 'post_id='.DataUtil::formatForStore($args['post_id']));
    $viewip['poster_host'] = gethostbyaddr($viewip['poster_ip']);

    $sql = "SELECT pn_uid, pn_uname, count(*) AS postcount
            FROM ".$pntable['dizkus_posts']." p, ".$pntable['users']." u
            WHERE poster_ip='".DataUtil::formatForStore($viewip['poster_ip'])."' && p.poster_id = u.pn_uid
            GROUP BY pn_uid";
    $res       = DBUtil::executeSQL($sql);
    $colarray  = array('uid', 'uname', 'postcount');
    $viewip['users'] = DBUtil::marshallObjects($res, $colarray);

    return $viewip;
}

/**
 * lockunlocktopic
 *
 * @params $args['topic_id'] int the topics id
 * @params $args['mode']     string lock or unlock
 * @returns void
 */
function Dizkus_userapi_lockunlocktopic($args)
{
    if (isset($args['topic_id']) && is_numeric($args['topic_id']) && isset($args['mode'])) {
        $tobj['topic_id']     = $args['topic_id'];
        $tobj['topic_status'] = ($args['mode']=='lock') ? 1 : 0;
        DBUtil::updateObject($tobj, 'dizkus_topics', '', 'topic_id');
    }
    return;
}

/**
 * stickyunstickytopic
 *
 * @params $args['topic_id'] int the topics id
 * @params $args['mode']     string sticky or unsticky
 * @returns void
 */
function Dizkus_userapi_stickyunstickytopic($args)
{
    if (isset($args['topic_id']) && is_numeric($args['topic_id']) && isset($args['mode'])) {
        $tobj['topic_id'] = $args['topic_id'];
        $tobj['sticky']   = ($args['mode']=='sticky') ? 1 : 0;
        DBUtil::updateObject($tobj, 'dizkus_topics', '', 'topic_id');
    }
    return;
}

/**
 * get_forumid_and categoryid_from_topicid
 * used for permission checks
 *
 * @params $args['topic_id'] int the topics id
 * @returns array(forum_id, category_id)
 */
function Dizkus_userapi_get_forumid_and_categoryid_from_topicid($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();

    // we know about the topic_id, let's find out the forum and catgeory name for permission checks
    $sql = "SELECT f.forum_id,
                   c.cat_id
            FROM  ".$pntable['dizkus_topics']." t
            LEFT JOIN ".$pntable['dizkus_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['dizkus_categories']." AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)DataUtil::formatForStore($args['topic_id'])."'";

    $res = DBUtil::executeSQL($sql);
    if ($res === false) {
        return showforumerror(__('Error! The topic you selected was not found. Please go back and try again.', $dom), __FILE__, __LINE__, '404 Not Found');
    }

    $colarray = array('forum_id', 'cat_id');
    $objarray = DBUtil::marshallObjects ($res, $colarray);
    return array_values($objarray[0]); // forum_id, cat_id
}

/**
 * readuserforums
 * reads all forums the recent users is allowed to see
 *
 * @params $args['cat_id'] int a category id (optional, if set, only reads the forums in this category)
 * @params $args['forum_id'] int a forums id (optional, if set, only reads this category
 * @returns array of forums, maybe empty
 */
function Dizkus_userapi_readuserforums($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $where = '';
    if (isset($args['forum_id'])) {
        $where = 'WHERE tbl.forum_id=' . DataUtil::formatForStore($args['forum_id']) . ' ';
    } elseif (isset($args['cat_id'])) {
        $where = 'WHERE a.cat_id=' . DataUtil::formatForStore($args['cat_id']) . ' ';
    }

    $joinInfo = array();
    $joinInfo[] = array('join_table'          =>  'dizkus_categories',
                        'join_field'          =>  'cat_title',
                        'object_field_name'   =>  'cat_title',
                        'compare_field_table' =>  'cat_id',
                        'compare_field_join'  =>  'cat_id');

    $permFilter = array();
    $permFilter[]  = array ('component_left'   =>  'Dizkus',
                            'component_middle' =>  '',
                            'component_right'  =>  '',
                            'instance_left'    =>  'cat_id',
                            'instance_middle'  =>  'forum_id',
                            'instance_right'   =>  '',
                            'level'            =>  ACCESS_READ);

    // retrieve the admin module object array
    $forums = DBUtil::selectExpandedObjectArray('dizkus_forums', $joinInfo, $where, 'forum_id', -1, -1, 'forum_id', $permFilter);

    if ($forums === false) {
        return showforumerror(__('Error! The forum or topic you selected was not found. Please go back and try again.', $dom), __FILE__, __LINE__, '404 Not Found');
    }

    if (isset($args['forum_id']) && isset($forums[$args['forum_id']])) {
        return $forums[$args['forum_id']];
    }

    return $forums;
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
function Dizkus_userapi_movetopic($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();

    // get the old forum id and old post date
    $topic = DBUtil::selectObjectById('dizkus_topics', $args['topic_id'], 'topic_id');

    if ($topic['forum_id'] <> $args['forum_id']) {
        // set new forum id
        $newtopic['forum_id'] = $args['forum_id'];
        DBUtil::updateObject($newtopic, 'dizkus_topics', 'topic_id='.(int)DataUtil::formatForStore($args['topic_id']), 'topic_id');

        $newpost['forum_id'] = $args['forum_id'];
        DBUtil::updateObject($newpost, 'dizkus_posts', 'topic_id='.(int)DataUtil::formatForStore($args['topic_id']), 'post_id');

        if ($args['shadow'] == true) {
            // user wants to have a shadow topic
            $message = __f('The original posting has been moved <a title="moved" href="%s">here</a>.', pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $args['topic_id'])),$dom);
            $subject = __f("*** The original posting '%s' has been moved", $topic['topic_title'], $dom);

            Dizkus_userapi_storenewtopic(array('subject'  => $subject,
                                                'message'  => $message,
                                                'forum_id' => $topic['forum_id'],
                                                'time'     => $topic['topic_time'],
                                                'no_sig'   => true));
        }
        pnModAPIFunc('Dizkus', 'admin', 'sync', array('id' => $args['forum_id'], 'type' => 'forum'));
        pnModAPIFunc('Dizkus', 'admin', 'sync', array('id' => $topic['forum_id'], 'type' => 'forum'));
    }

    return;
}

/**
 * deletetopic
 *
 * @params $args['topic_id'] int the topics id
 * @returns int the forums id for redirecting
 */
function Dizkus_userapi_deletetopic($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    list($forum_id, $cat_id) = Dizkus_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id' => $args['topic_id']));
    if (!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        return showforumerror(getforumerror('auth_mod', $forum_id, 'forum', __('Error! You do not have authorisation to read the content in this category or forum.', $dom)), __FILE__, __LINE__);
    }

    $pntable = pnDBGetTables();

    // Update the users's post count, this might be slow on big topics but it makes other parts of the
    // forum faster so we win out in the long run.
    
    // step #1: get all post ids and posters ids
    $where = $pntable['dizkus_posts_column']['topic_id'] .'=' . (int)DataUtil::formatForStore($args['topic_id']);
    $postsarray = DBUtil::selectObjectArray('dizkus_posts', $where);

    // step #2 go through the posting array and decrement the posting counter
    // TO-DO: for larger topics use IN(..., ..., ...) with 50 or 100 posting ids per sql
    foreach ($postsarray as $posting) {
        DBUtil::decrementObjectFieldByID('dizkus_users', 'user_posts', $posting['poster_id'], 'user_id');
    }

   // now delete postings
    // we will use the same $where as before!
    DBUtil::deleteWhere('dizkus_posts', $where);

    // now delete the topic itself
    DBUtil::deleteObjectByID('dizkus_topics', $args['topic_id'], 'topic_id');

    // remove topic subscriptions
    $where = $pntable['dizkus_topic_subscription_column']['topic_id'] .'=' . (int)DataUtil::formatForStore($args['topic_id']);
    DBUtil::deleteWhere('dizkus_topic_subscription', $where);

    // get forum info for adjustments
    $forum = DBUtil::selectObjectById('dizkus_forums', $forum_id, 'forum_id');
    // decrement forum_topics counter
    $forum['forum_topics']--;
    // decrement forum_posts counter
    $forum['forum_posts'] = $forum['forum_posts'] - count($postsarray);
    DBUtil::updateObject($forum, 'dizkus_forums', null, 'forum_id');
    
    // Let any hooks know that we have deleted an item (topic).
    pnModCallHooks('item', 'delete', $args['topic_id'], array('module' => 'Dizkus'));

    pnModAPIFunc('Dizkus', 'admin', 'sync', array('id' => $forum_id, 'type' => 'forum'));
    return $forum_id;
}

/**
 * Sending notify e-mail to users subscribed to the topic of the forum
 *
 * @params $args['topic_id'] int the topics id
 * @params $args['poster_id'] int the users pn_uid
 * @params $args['post_message'] string the text
 * @params $args['type'] int, 0=new message, 2=reply
 * @returns void
 */
function Dizkus_userapi_notify_by_email($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();

    setlocale (LC_TIME, pnConfigGetVar('locale'));
    $modinfo = pnModGetInfo(pnModGetIDFromName(pnModGetName()));

    // generate the mailheader info
    $email_from = pnModGetVar('Dizkus', 'email_from');
    if ($email_from == '') {
        // nothing in forumwide-settings, use PN adminmail
        $email_from = pnConfigGetVar('adminmail');
    }

    // normal notification
    $sql = 'SELECT t.topic_title,
                   t.topic_poster,
                   t.topic_time,
                   f.cat_id,
                   c.cat_title,
                   f.forum_name,
                   f.forum_id
            FROM  '.$pntable['dizkus_topics'].' t
            LEFT JOIN '.$pntable['dizkus_forums'].' f ON t.forum_id = f.forum_id
            LEFT JOIN '.$pntable['dizkus_categories'].' c ON f.cat_id = c.cat_id
            WHERE t.topic_id = '.(int)DataUtil::formatForStore($args['topic_id']);

    $res = DBUtil::executeSQL($sql);
    $colarray = array('topic_title', 'topic_poster', 'topic_time', 'cat_id', 'cat_title', 'forum_name', 'forum_id');
    $myrow    = DBUtil::marshallObjects($res, $colarray);

    if (!is_array($myrow)) {
        // no results - topic does not exist
        return showforumerror(__('Error! The topic you selected was not found. Please go back and try again.', $dom), __FILE__, __LINE__);
    }

    $topic_unixtime= dzk_str2time($myrow[0]['topic_time']); //strtotime ($myrow['topic_time']);
    $topic_time_ml = DateUtil::formatDatetime($topic_unixtime, 'datetimebrief');

    $poster_name = pnUserGetVar('uname',$args['poster_id']);

    $forum_id      = DataUtil::formatForDisplay($myrow[0]['forum_id']);
    $forum_name    = DataUtil::formatForDisplay($myrow[0]['forum_name']);
    $category_name = DataUtil::formatForDisplay($myrow[0]['cat_title']);
    $topic_subject = DataUtil::formatForDisplay($myrow[0]['topic_title']);

    $subject = ($args['type'] == 2) ? 'Re: ' : '';
    $subject .= $category_name . ' :: ' . $forum_name . ' :: ' . $topic_subject;

    // we do not want to notify the sender = the recent user
    $thisuser = pnUserGetVar('uid');
    // anonymous does not have uid, so we need a sql to exclude real users
    $fs_wherenotuser = '';
    $ts_wherenotuser = '';
    if (!empty($thisuser)) {
        $fs_wherenotuser = ' AND fs.user_id <> ' . DataUtil::formatForStore($thisuser);
        $ts_wherenotuser = ' AND ts.user_id <> ' . DataUtil::formatForStore($thisuser);
    }

    //  get list of forum subscribers with non-empty emails
    $sql = 'SELECT DISTINCT fs.user_id,
                            c.cat_id
            FROM ' . $pntable['dizkus_subscription'] . ' as fs,
                 ' . $pntable['dizkus_forums'] . ' as f,
                 ' . $pntable['dizkus_categories'] . ' as c
            WHERE fs.forum_id='.DataUtil::formatForStore($forum_id).'
              ' . $fs_wherenotuser . '
              AND f.forum_id = fs.forum_id
              AND c.cat_id = f.cat_id';

    $res = DBUtil::executeSQL($sql);
    $colarray = array('uid', 'cat_id');
    $result   = DBUtil::marshallObjects($res, $colarray);

    $recipients = array();
    // check if list is empty - then do nothing
    // we create an array of recipients here
    if (is_array($result) && !empty($result)) {
        foreach ($result as $resline) {
            // check permissions
            if (SecurityUtil::checkPermission('Dizkus::', $resline['cat_id'].':'.$forum_id.':', ACCESS_READ, $resline['uid'])) {
                $emailaddress = pnUserGetVar('email', $resline['uid']);
                if (empty($emailaddress)) {
                    continue;
                }
                $email['name']    = pnUserGetVar('uname', $resline['uid']);
                $email['address'] = $emailaddress;
                $email['uid']     = $resline['uid'];
                $recipients[$email['name']] = $email;
            }
        }
    }

    //  get list of topic_subscribers with non-empty emails
    $sql = 'SELECT DISTINCT ts.user_id,
                            c.cat_id,
                            f.forum_id
            FROM ' . $pntable['dizkus_topic_subscription'] . ' as ts,
                 ' . $pntable['dizkus_forums'] . ' as f,
                 ' . $pntable['dizkus_categories'] . ' as c,
                 ' . $pntable['dizkus_topics'] . ' as t
            WHERE ts.topic_id='.DataUtil::formatForStore($args['topic_id']).'
              ' . $ts_wherenotuser . '
              AND t.topic_id = ts.topic_id
              AND f.forum_id = t.forum_id
              AND c.cat_id = f.cat_id';

    $res = DBUtil::executeSQL($sql);
    $colarray = array('uid', 'cat_id', 'forum_id');
    $result   = DBUtil::marshallObjects($res, $colarray);

    if (is_array($result) && !empty($result)) {
        foreach ($result as $resline) {
            // check permissions
            if (SecurityUtil::checkPermission('Dizkus::', $resline['cat_id'] . ':' . $resline['forum_id'] . ':', ACCESS_READ, $resline['uid'])) {
                $emailaddress = pnUserGetVar('email', $resline['uid']);
                if (empty($emailaddress)) {
                    continue;
                }
                $email['name']    = pnUserGetVar('uname', $resline['uid']);
                $email['address'] = $emailaddress;
                $email['uid']     = $resline['uid'];
                $recipients[$email['name']] = $email;
            }
        }
    }

    if (count($recipients) > 0) {
        $sitename = pnConfigGetVar('sitename');
    
        $render = pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('sitename', $sitename);
        $render->assign('category_name', $category_name);
        $render->assign('forum_name', $forum_name);
        $render->assign('topic_subject', $topic_subject);
        $render->assign('poster_name', $poster_name);
        $render->assign('topic_time_ml', $topic_time_ml);
        $render->assign('post_message', $args['post_message']);
        $render->assign('topic_id', $args['topic_id']);
        $render->assign('forum_id', $forum_id);
        $render->assign('reply_url', pnModURL('Dizkus', 'user', 'reply', array('topic' => $args['topic_id'], 'forum' => $forum_id), null, null, true));
        $render->assign('topic_url', pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $args['topic_id']), null, null, true));
        $render->assign('subscription_url', pnModURL('Dizkus', 'user', 'prefs', array(), null, null, true));
        $render->assign('base_url', pnGetBaseURL());
        $message = $render->fetch('dizkus_mail_notifyuser.txt');
      
        foreach ($recipients as $subscriber) {
            // integrate contactlist's ignorelist here
            $ignorelist_setting = pnModAPIFunc('Dizkus','user','get_settings_ignorelist',array('uid' => $subscriber['uid']));
            if (pnModAvailable('ContactList') && 
                (in_array($ignorelist_setting, array('medium', 'strict'))) && 
                pnModAPIFunc('ContactList', 'user', 'isIgnored', array('uid' => $subscriber['uid'], 'iuid' => pnUserGetVar('uid')))) {
                $send = false;
            } else {
                $send = true;
            }
            if ($send) {
                $args = array( 'fromname'    => $sitename,
                               'fromaddress' => $email_from,
                               'toname'      => $subscriber['name'],
                               'toaddress'   => $subscriber['address'],
                               'subject'     => $subject,
                               'body'        => $message,
                               'headers'     => array('X-UserID: ' . md5($uid),
                                                      'X-Mailer: Dizkus v' . $modinfo['version'],
                                                      'X-DizkusTopicID: ' . $args['topic_id']));
    
                pnModAPIFunc('Mailer', 'user', 'sendmessage', $args);
            }
        }
    }

    return true;
}

/**
 * get_topic_subscriptions
 *
 * @params none
 * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 * @returns array with topic ids, may be empty
 */
function Dizkus_userapi_get_topic_subscriptions($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();

    if (isset($args['user_id'])) {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return showforumerror(__('Error! No permission for this action.', $dom));
        }
    } else {
        $args['user_id'] = pnUserGetVar('uid');
    }

    // read the topic ids
    $sql = 'SELECT ts.topic_id,
                   t.topic_title,
                   t.topic_poster,
                   t.topic_time,
                   t.topic_replies,
                   t.topic_last_post_id,
                   u.pn_uname,
                   f.forum_id,
                   f.forum_name
            FROM '.$pntable['dizkus_topic_subscription'].' AS ts,
                 '.$pntable['dizkus_topics'].' AS t,
                 '.$pntable['users'].' AS u,
                 '.$pntable['dizkus_forums'].' AS f
            WHERE (ts.user_id='.(int)DataUtil::formatForStore($args['user_id']).'
              AND t.topic_id=ts.topic_id
              AND u.pn_uid=ts.user_id
              AND f.forum_id=t.forum_id)
            ORDER BY f.forum_id, ts.topic_id';

    $res = DBUtil::executeSQL($sql);
    $colarray = array('topic_id', 'topic_title', 'topic_poster', 'topic_time', 'topic_replies', 'topic_last_post_id', 'poster_name',
                      'forum_id', 'forum_name');
    $subscriptions    = DBUtil::marshallObjects($res, $colarray);

    $post_sort_order = pnModAPIFunc('Dizkus', 'user', 'get_user_post_order', array('user_id' => $args['user_id']));
    $posts_per_page  = pnModGetVar('Dizkus', 'posts_per_page');

    if (is_array($subscriptions) && !empty($subscriptions)) {
        for($cnt=0;$cnt<count($subscriptions);$cnt++) {
             
            if ($post_sort_order == 'ASC') {
                $start = ((ceil(($subscriptions[$cnt]['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page);
            } else {
                // latest topic is on top anyway...
                $start = 0;
            }
            // we now create the url to the last post in the thread. This might
            // on site 1, 2 or what ever in the thread, depending on topic_replies
            // count and the posts_per_page setting
            $subscriptions[$cnt]['last_post_url'] = DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewtopic',
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
 * @params $args['topic_id'] int the topics id
 * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 * @returns void
 */
function Dizkus_userapi_subscribe_topic($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    } else {
        $args['user_id'] = pnUserGetVar('uid');
    }

    list($forum_id, $cat_id) = Dizkus_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id' => $args['topic_id']));
    if (!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        return showforumerror(getforumerror('auth_read', $forum_id, 'forum', __('Error! You do not have authorisation to read the content in this category or forum.', $dom)), __FILE__, __LINE__);
    }

    if (Dizkus_userapi_get_topic_subscription_status(array('userid' => $args['user_id'], 'topic_id' => $args['topic_id'])) == false) {
        // add user only if not already subscribed to the topic
        $sobj['topic_id'] = $args['topic_id'];
        $sobj['user_id'] =  $args['user_id'];
        DBUtil::insertObject($sobj, 'dizkus_topic_subscription');
    }
    return;
}

/**
 * unsubscribe_topic
 *
 * @params $args['topic_id'] int the topics id, if not set we unsubscribe all topics
 * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 * @params $args['silent'] bool true=no error message when not subscribed, simply return void (obsolete)
 * @returns void
 */
function Dizkus_userapi_unsubscribe_topic($args)
{
    if (isset($args['user_id'])) {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    } else {
        $args['user_id'] = pnUserGetVar('uid');
    }

    $pntable = pnDBGetTables();

    $where = 'WHERE ' . $pntable['dizkus_topic_subscription_column']['user_id'] . '=' . (int)DataUtil::formatForStore($args['user_id']);
    if (!empty($args['topic_id'])) {
        $where .= ' AND ' . $pntable['dizkus_topic_subscription_column']['topic_id'] . '=' . (int)DataUtil::formatForStore($args['topic_id']);
    }

    return DBUtil::deleteWhere('dizkus_topic_subscription', $where);
}

/**
 * subscribe_forum
 *
 * @params $args['forum_id'] int the forums id
 * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 * @returns void
 */
function Dizkus_userapi_subscribe_forum($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    } else {
        $args['user_id'] = pnUserGetVar('uid');
    }

    $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                          array('forum_id' => $args['forum_id']));
    if (!allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(getforumerror('auth_read',$forum['forum_id'], 'forum', __('Error! You do not have authorisation to read the content in this category or forum.', $dom)), __FILE__, __LINE__);
    }

    if (Dizkus_userapi_get_forum_subscription_status($args) == false) {
        // add user only if not already subscribed to the forum
        // we can use the args parameter as-is
        DBUtil::insertObject($args, 'dizkus_subscription');
    }

    return true;
}

/**
 * unsubscribe_forum
 *
 * @params $args['forum_id'] int the forums id, if empty then we unsubscribe all forums
 * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 * @returns void
 */
function Dizkus_userapi_unsubscribe_forum($args)
{
    if (isset($args['user_id'])) {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    } else {
        $args['user_id'] = pnUserGetVar('uid');
    }

    $pntable = pnDBGetTables();

    $where = $pntable['dizkus_subscription_column']['user_id'] . '=' . (int)DataUtil::formatForStore($args['user_id']);
    if (!empty($args['forum_id'])) {
        $where .= ' AND ' . $pntable['dizkus_subscription_column']['forum_id'] . '=' . (int)DataUtil::formatForStore($args['forum_id']);
    }

    return DBUtil::deleteWhere('dizkus_subscription', $where);
}

/**
 * add_favorite_forum
 *
 * @params $args['forum_id'] int the forums id
 * @params $args['user_id'] int - Optional - the user id
 * @returns void
 */
function Dizkus_userapi_add_favorite_forum($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!isset($args['user_id'])) {
        $args['user_id'] = (int)pnUserGetVar('uid');
    }

    $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                          array('forum_id' => $args['forum_id']));

    if (!allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(getforumerror('auth_read', $forum['forum_id'], 'forum', __('Error! You do not have authorisation to read the content in this category or forum.', $dom)), __FILE__, __LINE__);
    }

    if (Dizkus_userapi_get_forum_favorites_status($args) == false) {
        // add user only if not already a favorite
        // we can use the args parameter as-is
        DBUtil::insertObject($args, 'dizkus_forum_favorites');
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
function Dizkus_userapi_remove_favorite_forum($args)
{
    if (!isset($args['user_id'])) {
        $args['user_id'] = (int)pnUserGetVar('uid');
    }

    // remove from favorites - no need to check the favorite status, we delete it anyway
    $where = "user_id='".(int)DataUtil::formatForStore($args['user_id'])."'
              AND forum_id='".(int)DataUtil::formatForStore($args['forum_id'])."'";

    return DBUtil::deleteWhere('dizkus_forum_favorites', $where);
}

/**
 * emailtopic
 *
 * @params $args['sendto_email'] string the recipients email address
 * @params $args['message'] string the text
 * @params $args['subject'] string the subject
 * @returns bool
 */
function Dizkus_userapi_emailtopic($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $sender_name = pnUserGetVar('uname');
    $sender_email = pnUserGetVar('email');
    if (!pnUserLoggedIn()) {
        $sender_name = pnModGetVar('Users', 'anonymous');
        $sender_email = pnModGetVar('Dizkus', 'email_from');
    }

    $args2 = array( 'fromname'    => $sender_name,
                    'fromaddress' => $sender_email,
                    'toname'      => $args['sendto_email'],
                    'toaddress'   => $args['sendto_email'],
                    'subject'     => $args['subject'],
                    'body'        => $args['message'],
                    'headers'     => array('X-Mailer: Dizkus v' . $modinfo['version']));

    return pnModAPIFunc('Mailer', 'user', 'sendmessage', $args2);
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
function Dizkus_userapi_get_latest_posts($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();

    // init some arrays
    $posts = array();
    $m2fposts = array();
    $rssposts = array();

    if (!isset($args['limit']) || empty($args['limit']) || ($args['limit'] < 0) || ($args['limit'] > 100)) {
        $args['limit'] = 100;
    }

    $dizkusvars      = pnModGetVar('Dizkus');
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
                   $text = __('Today', $dom);
                   break;
        case '3' : // yesterday
                   $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) = 1 ";
                   $text = __('Yesterday', $dom);
                   break;
        case '4' : // lastweek
                   $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) < 8 ";
                   $text= __('Last week', $dom);
                   break;
        case '5' : // last x hours
                   $wheretime  = " AND t.topic_time > DATE_SUB(NOW(), INTERVAL " . DataUtil::formatForStore($args['nohours']) . " HOUR) ";
                   $text = DataUtil::formatForDisplay(__f('Last %s hours', $args['nohours'], $dom));
                   break;
        case '6' : // last visit
                   $wheretime = " AND t.topic_time > '" . DataUtil::formatForStore($args['last_visit']) . "' ";
                   $text = DataUtil::formatForDisplay(__f('Last visit: %s', DateUtil::formatDatetime($args['last_visit_unix'], 'datetimebrief'), $dom));
                   break;
        case '1' :
        default:   // last 24 hours
                   $wheretime = " AND t.topic_time > DATE_SUB(NOW(), INTERVAL 1 DAY) ";
                   $text  =__('Last 24 hours', $dom);
                   break;
    }

    // get all forums the user is allowed to read
    $userforums = pnModAPIFunc('Dizkus', 'user', 'readuserforums');
    if (!is_array($userforums) || count($userforums) == 0) {
        // error or user is not allowed to read any forum at all
        // return empty result set without even doing a db access
        return array($posts, $m2fposts, $rssposts, $text);
    }

    // now create a very simple array of forum_ids only. we do not need
    // all the other stuff in the $userforums array entries
    $allowedforums = array_map('_get_forum_ids', $userforums);
    $whereforum = ' f.forum_id IN (' . DataUtil::formatForStore(implode(',', $allowedforums)) . ') ';

    // integrate contactlist's ignorelist here
    $whereignorelist = '';
    if ((isset($dizkusvars['ignorelist_options']) && $dizkusvars['ignorelist_options'] <> 'none') && pnModAvailable('ContactList')) {
        $ignorelist_setting = pnModAPIFunc('Dizkus', 'user', 'get_settings_ignorelist', array('uid' => pnUserGetVar('uid')));
        if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
            // get user's ignore list
            $ignored_users = pnModAPIFunc('ContactList', 'user', 'getallignorelist', array('uid' => pnUserGetVar('uid')));
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
                 FROM '.$pntable['dizkus_topics'].' AS t,
                      '.$pntable['dizkus_forums'].' AS f,
                      '.$pntable['dizkus_categories'].' AS c,
                      '.$pntable['dizkus_posts'].' AS p
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
            $post['poster_name'] = pnModGetVar('Users', 'anonymous');
        } else {
            $post['poster_name'] = pnUserGetVar('uname', $post['poster_id']);
        }

        $post['posted_unixtime'] = dzk_str2time($post['post_time']); // strtotime ($post['post_time']);
        $post['post_time'] = DateUtil::formatDatetime($post['posted_unixtime'], 'datetimebrief');

        $post['last_post_url'] = DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewtopic',
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
 * helper function to extract forum_ids from forum array
 */
function _get_forum_ids($f)
{
    return $f['forum_id'];
}

/**
 * usersync
 * stub function for syncing new pn users to Dizkus
 *
 * @params none
 * @returns api call result
 */
function Dizkus_userapi_usersync()
{
    return pnModAPIFunc('Dizkus', 'admin', 'sync', array('type' => 'all users'));
}

/**
 * splittopic
 *
 * @params $args['post'] array with posting data as returned from readpost()
 * @returns int id of the new topic
 */
function Dizkus_userapi_splittopic($args)
{
    $post = $args['post'];

    $pntable = pnDBGetTables();

    // before we do anything we will read the topic_last_post_id because we will need
    // this one later (it will become the topic_last_post_id of the new thread)
    // DBUtil:: read complete topic
    $oldtopic = DBUtil::selectObjectByID('dizkus_topics', $post['topic_id'],'topic_id');

    //  insert values into topics-table
    $newtopic = array('topic_title'  => $post['topic_subject'],
                      'topic_poster' => $post['poster_data']['pn_uid'],
                      'forum_id'     => $post['forum_id'],
                      'topic_time'   => DateUtil::getDatetime('', '%Y-%m-%d %H:%M'));
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

    // update the new topic
    $newtopic['topic_replies']      = (int)$posts_to_move - 1;
    $newtopic['topic_last_post_id'] = $oldtopic['topic_last_post_id'];
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
function Dizkus_userapi_get_previous_or_next_topic_id($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

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

    $pntable = pnDBGetTables();

    // integrate contactlist's ignorelist here
    $whereignorelist = '';
    $ignorelist_setting = pnModAPIFunc('Dizkus', 'user', 'get_settings_ignorelist',array('uid' => pnUserGetVar('uid')));
    if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
        // get user's ignore list
        $ignored_users = pnModAPIFunc('ContactList', 'user', 'getallignorelist',array('uid' => pnUserGetVar('uid')));
        $ignored_uids = array();
        foreach ($ignored_users as $item) {
            $ignored_uids[]=(int)$item['iuid'];
        }
        if (count($ignored_uids) > 0) {
            $whereignorelist = " AND t1.topic_poster NOT IN (".implode(',',$ignored_uids).")";
        }
    }

    $sql = 'SELECT t1.topic_id
            FROM '.$pntable['dizkus_topics'].' AS t1,
                 '.$pntable['dizkus_topics'].' AS t2
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
 * getfavorites
 * return the list of favorite forums for this user
 *
 * @params $args['user_id'] -Optional- the user id of the person who we want the favorites for
 * @params $args['last_visit'] timestamp date of last visit
 * @returns array of categories with an array of forums in the catgories
 *
 */
function Dizkus_userapi_getfavorites($args)
{
    static $tree;

    // if we have already gone through this once then don't do it again
    // if we have a favorites block displayed and are looking at the
    // forums this will get called twice.
    if (isset($tree)) {
        return $tree;
    }

    // lets get all the forums just like we would a normal display
    // we'll figure out which ones aren't needed further down.
    $tree = pnModAPIFunc('Dizkus', 'user', 'readcategorytree', array('last_visit' => $args['last_visit'] ));

    // if they are anonymous they can't have favorites
    if (!pnUserLoggedIn()) {
        return $tree;
    }

    if (!isset($args['user_id'])) {
        $args['user_id'] = (int)pnUserGetVar('uid');
    }

    $pntable = pnDBGetTables();
    $objarray = DBUtil::selectObjectArray('dizkus_forum_favorites', $pntable['dizkus_forum_favorites_column']['user_id'] - '=' . (int)DataUtil::formatForStore($args['user_id']));
    $favorites = array_map('_get_favorites', $objarray);

    // categoryCount is needed since the categories aren't stored as numerical
    // indexes.  They are stored as associative arrays.
    $categoryCount=0;
    // loop through all the forums and delete all forums that aren't part of
    // the favorites.
    $deleteMe = array();
    foreach ($tree as $categoryIndex => $category)
    {
        // $count is needed because the index changes as we splice the array
        // but the foreach is working on a copy of the array so the $forumIndex
        // value will point to non-existent elements in the modified array.
        $count = 0;
        foreach ($category['forums'] as $forumIndex => $forum)
        {
            // if this isn't one of our favorites then we need to remove it
            if (!in_array((int)$forum['forum_id'], $favorites, true)){
                // remove the forum that isn't one of the favorites
                array_splice($tree[$categoryIndex]['forums'], ($forumIndex - $count) , 1);
                // increment $count because we will need to subtract this number
                // from the index the next time around since this many entries\
                // has been removed from the original array.
                $count++;
            }
        }
        // lets see if the category is empty.  If it is we don't want to
        // display it in the favorites
        if (count($tree[$categoryIndex]['forums']) === 0) {
            $deleteMe[] = $categoryCount;
        }
        // increase the index number to keep track of where we are in the array
        $categoryCount++;
    }

    // reverse the order so we don't need to do all the crazy subtractions
    // that we had to do above
    $deleteMe = array_reverse($deleteMe);
    foreach ($deleteMe as $category) {
        // remove the empyt category from the array
        array_splice($tree, $category , 1);
    }

    // return the modified array
    return $tree;
}

/**
 * helper function
 */
function _get_favorites($f)
{
    return (int)$f['forum_id'];
}

/**
 * get_favorite_status
 *
 * read the flag from the users table that indicates the users last choice: show all forum (0) or favorites only (1)
 * @params $args['user_id'] int the users id
 * @returns boolean
 *
 */
function Dizkus_userapi_get_favorite_status($args)
{
    if (!isset($args['user_id'])) {
        $args['user_id'] = (int)pnUserGetVar('uid');
    }

    $obj = DBUtil::selectObjectByID('dizkus_users', $args['user_id'], 'user_id', null, null, null, false);

    return (bool)$obj['user_favorites'];
}

/**
 * change_favorite_status
 *
 * changes the flag in the users table that indicates the users last choice: show all forum (0) or favorites only (1)
 * @params $args['user_id'] int the users id
 * @returns 0 or 1
 *
 */
function Dizkus_userapi_change_favorite_status($args)
{
    if (!isset($args['user_id'])) {
        $args['user_id'] = (int)pnUserGetVar('uid');
    }

    $recentstatus = Dizkus_userapi_get_favorite_status(array('user_id' => $args['user_id']));
    $args['user_favorites'] = ($recentstatus==true) ? 0 : 1;
    DBUtil::updateObject($args, 'dizkus_users', '', 'user_id');

    return (bool)$args['user_favorites'];
}

/**
 * get_user_post_order
 * Determines the users desired post order for topics.
 * Either Newest First or Oldest First
 * Returns 'ASC' or 'DESC' on success, false on failure.
 *
 * @params user_id - The user id of the person who's order we
 *                  are trying to determine
 * @returns string on success, false on failure
 */
function Dizkus_userapi_get_user_post_order($args)
{
    $loggedIn = pnUserLoggedIn();

    // if we are passed the user_id then lets use it
    if (isset($args['user_id'])) {
        // we got passed the id but it is the anonymous user
        // and the user isn't logged in, so we return the default order.
        // We use this check because we may want to call this function
        // from another module or function as an admin, moderator, etc
        // so the logged in user may not be the person we want the info about.
        if ($args['user_id'] < 2 || !$loggedIn) {
            return pnModGetVar('Dizkus', 'post_sort_order');
        }
    } else {
        // we didn't get a user_id passed into the function so if
        // the user is logged in then lets use their id.  If not
        // then return th default order.
        if ($loggedIn) {
            $args['user_id'] = pnUserGetVar('uid');
        } else {
            return pnModGetVar('Dizkus', 'post_sort_order');
        }
    }

    $obj = DBUtil::selectObjectByID('dizkus_users', $args['user_id'], 'user_id', null, null, null, false);

    if (is_array($obj)) {
        $post_order = ($obj['user_post_order']) ? 'DESC' : 'ASC';
    } else {
        $post_order = pnModGetVar('Dizkus', 'post_sort_order');
    }

    return $post_order;
}

/**
 * change_user_post_order
 *
 * changes the flag in the users table that indicates the users preferred post order: Oldest First (0) or Newest First (1)
 * @params $args['user_id'] int the users id
 * @returns bool - true on success, false on failure
 *
 */
function Dizkus_userapi_change_user_post_order($args)
{
    // if we didn't get a user_id and the user isn't logged in then
    // return false because there is no database entry to update
    if (!isset($args['user_id']) && pnUserLoggedIn()) {
        $args['user_id'] = (int)pnUserGetVar('uid');
    }

    $post_order = pnModAPIFunc('Dizkus','user','get_user_post_order');
    $args['user_post_order'] = ($post_order == 'DESC') ? 0 : 1;

    return DBUtil::updateObject($args, 'dizkus_users', '', 'user_id');
}

/**
 * get_forum_category
 * Determines the category that a forum belongs to.
 *
 * @params forum_id - The forum id to find the category of
 * @returns int on success, false on failure
 */
function Dizkus_userapi_get_forum_category($args)
{
    if (!isset($args['forum_id']) || !is_numeric($args['forum_id'])) {
        return false;
    }

    return (int)DBUtil::selectFieldByID('dizkus_forums', 'cat_id', $args['forum_id'], 'forum_id');
}

/**
 * get_page_from_topic_replies
 * Uses the number of topic_replies and the posts_per_page settings to determine the page
 * number of the last post in the thread. This is needed for easier navigation.
 *
 * @params $args['topic_replies'] int number of topic replies
 * @return int page number of last posting in the thread
 */
function Dizkus_userapi_get_page_from_topic_replies($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!isset($args['topic_replies']) || !is_numeric($args['topic_replies']) || $args['topic_replies'] < 0 ) {
        return LogUtil::registerArgsError();
    }

    // get some enviroment
    $posts_per_page  = pnModGetVar('Dizkus', 'posts_per_page');
    $post_sort_order = pnModGetVar('Dizkus', 'post_sort_order');

    $last_page = 0;
    if ($post_sort_order == 'ASC') {
        // +1 for the initial posting
        $last_page = floor(($args['topic_replies'] + 1) / $posts_per_page);
    }

    // if not ASC then DESC which means latest topic is on top anyway...
    return $last_page;
}

/**
 * cron
 *
 * @params $args['forum'] array with forum information
 * @params $args['force'] boolean if true force connection no matter of active setting or interval
 * @params $args['debug'] boolean indicates debug mode on/off
 * @returns none
 */
function Dizkus_userapi_mailcron($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (pnModGetVar('Dizkus', 'm2f_enabled') <> 'yes') {
        return;
    }

    $force = (isset($args['force'])) ? (boolean)$args['force'] : false;
    $forum = $args['forum'];

    Loader::includeOnce('modules/Dizkus/pnincludes/pop3.php');
    if ( (($forum['pop3_active'] == 1) && ($forum['pop3_last_connect'] <= time()-($forum['pop3_interval']*60)) ) || ($force == true) ) {
        mailcronecho('found active: ' . $forum['forum_id'] . ' = ' . $forum['forum_name'] . "\n", $args['debug']);
        // get new mails for this forum
        $pop3 = new pop3_class;
        $pop3->hostname = $forum['pop3_server'];
        $pop3->port     = $forum['pop3_port'];
        $error = '';

        // open connection to pop3 server
        if (($error = $pop3->Open()) == '') {
            mailcronecho("Connected to the POP3 server '".$pop3->hostname."'.\n", $args['debug']);
            // login to pop3 server
            if (($error = $pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0)) == '') {
                mailcronecho( "User '" . $forum['pop3_login'] . "' logged into POP3 server '".$pop3->hostname."'.\n", $args['debug']);
                // check for message
                if (($error = $pop3->Statistics($messages,$size)) == '') {
                    mailcronecho("There are $messages messages in the mailbox, amounting to a total of $size bytes.\n", $args['debug']);
                    // get message list...
                    $result = $pop3->ListMessages('', 1);
                    if (is_array($result) && count($result) > 0) {
                        // logout the currentuser
                        mailcronecho("Logging out '" . pnUserGetVar('uname') . "'.\n", $args['debug']);
                        pnUserLogOut();
                        // login the correct user
                        if (pnUserLogIn($forum['pop3_pnuser'], base64_decode($forum['pop3_pnpassword']), false)) {
                            mailcronecho('Done! User ' . pnUserGetVar('uname') . ' successfully logged in.', $args['debug']);
                            if (!allowedtowritetocategoryandforum($forum['cat_id'], $forum['forum_id'])) {
                                mailcronecho("Error! Insufficient permissions for " . pnUserGetVar('uname') . " in forum " . $forum['forum_name'] . "(id=" . $forum['forum_id'] . ").", $args['debug']);
                                pnUserLogOut();
                                mailcronecho('Done! User ' . pnUserGetVar('uname') . ' logged out.', $args['debug']);
                                return false;
                            }
                            mailcronecho("Adding new posts as user '" . pnUserGetVar('uname') . "'.\n", $args['debug']);
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
                                        if (strpos($header, 'subject:') === 0) {
                                            $subject = trim(strip_tags(substr($header, 8)));
                                        }
                                        // get sender
                                        if (strpos($header, 'from:') === 0) {
                                            $from = trim(strip_tags(substr($header, 5)));
                                            // replace @ and . to make it harder for email harvesers,
                                            // credits to Teb for this idea
                                            $from = str_replace(array('@','.'),array(' (at) ',' (dot) '), $from);
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
                                        $subject = DataUtil::formatForDisplay(__('Error! The post has no subject line.', $dom));
                                    }

                                    // check if subject matches our matchstring
                                    if (empty($original_topic_id)) {
                                        if (empty($forum['pop3_matchstring']) || (preg_match($forum['pop3_matchstring'], $subject) <> 0)) {
                                            $message = '[code=htmlmail,user=' . $from . ']' . implode("\n", $body) . '[/code]';
                                            if (!empty($replyto)) {
                                                // this seems to be a reply, we find the original posting
                                                // and store this mail in the same thread
                                                $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topic_by_postmsgid',
                                                                         array('msgid' => $replyto));
                                                if (is_bool($topic_id) && $topic_id == false) {
                                                    // msgid not found, we clear replyto to create a new topic
                                                    $replyto = '';
                                                } else {
                                                    // topic_id found, add this posting as a reply there
                                                    list($start,
                                                         $post_id ) = pnModAPIFunc('Dizkus', 'user', 'storereply',
                                                                                   array('topic_id'         => $topic_id,
                                                                                         'message'          => $message,
                                                                                         'attach_signature' => 1,
                                                                                         'subscribe_topic'  => 0,
                                                                                         'msgid'            => $msgid));
                                                    mailcronecho("added new post '$subject' (post=$post_id) to topic $topic_id\n", $args['debug']);
                                                }
                                            }

                                            // check again for replyto and create a new topic
                                            if (empty($replyto)) {
                                                // store message in forum
                                                $topic_id = pnModAPIFunc('Dizkus', 'user', 'storenewtopic',
                                                                         array('subject'          => $subject,
                                                                               'message'          => $message,
                                                                               'forum_id'         => $forum['forum_id'],
                                                                               'attach_signature' => 1,
                                                                               'subscribe_topic'  => 0,
                                                                               'msgid'            => $msgid ));
                                                mailcronecho("Added new topic '$subject' (topic ID $topic_id) to '".$forum['forum_name'] ."' forum.\n", $args['debug']);
                                            }
                                        } else {
                                            mailcronecho("Warning! Message subject  line '$subject' does not match requirements and will be ignored.", $args['debug']);
                                        }
                                    } else {
                                        mailcronecho("Warning! The message subject line '$subject' is a possible loop and will be ignored.", $args['debug']);
                                    }
                                    // mark message for deletion
                                    $pop3->DeleteMessage($cnt);
                                }
                            }
                            // logout the mail2forum user
                            if (pnUserLogOut()) {
                                mailcronecho('Done! User ' . $forum['pop3_pnuser'] . ' logged out.', $args['debug']);
                            }
                        } else {
                            mailcronecho("Error! Could not log user '". $forum['pop3_pnuser'] ."' in.\n");
                        }
                        // close pop3 connection and finally delete messages
                        if ($error == '' && ($error=$pop3->Close()) == '') {
                            mailcronecho("Disconnected from POP3 server '".$pop3->hostname."'.\n");
                        }
                    } else {
                        $error = $result;
                    }
                }
            }
        }
        if (!empty($error)) {
            mailcronecho( "error: ",htmlspecialchars($error) . "\n");
        }

        // store the timestamp of the last connection to the database
        $fobj['forum_pop3_lastconnect'] = time();
        $fobj['forum_id']               = $forum['forum_id'];
        DBUtil::updateObject($fobj, 'dizkus_forums', '', 'forum_id');
    }

    return;
}

/**
 * testpop3connection
 *
 * @params $args['forum_id'] int the id of the forum to test the pop3 connection
 * @returns array of messages from pop3 connection test
 *
 */
function Dizkus_userapi_testpop3connection($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!isset($args['forum_id']) || !is_numeric($args['forum_id'])) {
        return LogUtil::registerArgsError();
    }

    $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                          array('forum_id' => $args['forum_id']));
    Loader::includeOnce('modules/Dizkus/pnincludes/pop3.php');

    $pop3 = new pop3_class;
    $pop3->hostname = $forum['pop3_server'];
    $pop3->port     = $forum['pop3_port'];

    $error = '';
    $pop3messages = array();
    if (($error=$pop3->Open()) == '') {
        $pop3messages[] = "connected to the POP3 server '".$pop3->hostname."'";
        if (($error=$pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0))=='') {
            $pop3messages[] = "user '" . $forum['pop3_login'] . "' logged in";
            if (($error=$pop3->Statistics($messages, $size))=='') {
                $pop3messages[] = "There are $messages messages in the mailbox, amounting to a total of $size bytes.";
                $result=$pop3->ListMessages('',1);
                if (is_array($result) && count($result)>0) {
                    for ($cnt = 1; $cnt <= count($result); $cnt++) {
                        if (($error=$pop3->RetrieveMessage($cnt, $headers, $body, -1)) == '') {
                            foreach ($headers as $header) {
                                if (strpos(strtolower($header), 'subject:') === 0) {
                                    $subject = trim(strip_tags(substr($header, 8)));
                                }
                            }
                        }
                    }
                    if ($error == '' && ($error=$pop3->Close()) == '') {
                        $pop3messages[] = "Disconnected from POP3 server '".$pop3->hostname."'.\n";
                    }
                } else {
                    $error=$result;
                }
            }
        }
    }
    if (!empty($error)) {
        $pop3messages[] = 'error: ' . htmlspecialchars($error);
    }

    return $pop3messages;
}

/**
 * get_topic_by_postmsgid
 * gets a topic_id from the postings msgid
 *
 * @params $args['msgid'] string the msgid
 * @returns int topic_id or false if not found
 *
 */
function Dizkus_userapi_get_topic_by_postmsgid($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

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
function Dizkus_userapi_get_topicid_by_postid($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!isset($args['post_id']) || empty($args['post_id'])) {
        return LogUtil::registerArgsError();
    }

    return DBUtil::selectFieldByID('dizkus_posts', 'topic_id', $args['post_id'], 'post_id');
}

/**
 * movepost
 *
 * @params $args['post'] array with posting data as returned from readpost()
 * @params $args['to_topic']
 * @returns int id of the new topic
 */
function Dizkus_userapi_movepost($args)
{
    $post     = $args['post'];
    $to_topic = $args['to_topic'];
    
    // 1 . update topic_id, post_time in posts table
    // for post[post_id]
    // 2 . update topic_replies in nuke_dizkus_topics ( COUNT )
    // for old_topic
    // 3 . update topic_last_post_id in nuke_dizkus_topics
    // for old_topic
    // 4 . update topic_replies in nuke_dizkus_topics ( COUNT )
    // 5 . update topic_last_post_id in nuke_dizkus_topics if necessary

    $pntable = pnDBGetTables();

    // 1 . update topic_id in posts table
    $sql = 'UPDATE '.$pntable['dizkus_posts'].'
            SET topic_id='.(int)DataUtil::formatForStore($to_topic).'
            WHERE post_id = '.(int)DataUtil::formatForStore($post['post_id']);

    DBUtil::executeSQL($sql);

    // for to_topic
    // 2 . update topic_replies in dizkus_topics ( COUNT )
    // 3 . update topic_last_post_id in dizkus_topics
    // get the new topic_last_post_id of to_topic
    $sql = 'SELECT post_id, post_time
            FROM '.$pntable['dizkus_posts'].'
            WHERE topic_id = '.(int)DataUtil::formatForStore($to_topic).'
            ORDER BY post_time DESC';

    $res = DBUtil::executeSQL($sql, -1, 1);
    $colarray = array('post_id', 'post_time');
    $result    = DBUtil::marshallObjects($res, $colarray);
    $to_last_post_id = $result[0]['post_id'];
    $to_post_time    = $result[0]['post_time'];

    $sql = 'UPDATE '.$pntable['dizkus_topics'].'
            SET topic_replies = topic_replies + 1,
                topic_last_post_id='.(int)DataUtil::formatForStore($to_last_post_id).',
                topic_time=\''.DataUtil::formatForStore($to_post_time).'\'
            WHERE topic_id='.(int)DataUtil::formatForStore($to_topic);

    DBUtil::executeSQL($sql);

    // for old topic ($post[topic_id]
    // 4 . update topic_replies in nuke_dizkus_topics ( COUNT )
    // 5 . update topic_last_post_id in nuke_dizkus_topics if necessary

    // get the new topic_last_post_id of the old topic
    $sql = 'SELECT post_id, post_time
            FROM '.$pntable['dizkus_posts'].'
            WHERE topic_id = '.(int)DataUtil::formatForStore($post['topic_id']).'
            ORDER BY post_time DESC';

    $res = DBUtil::executeSQL($sql, -1, 1);
    $colarray = array('post_id', 'post_time');
    $result    = DBUtil::marshallObjects($res, $colarray);
    $old_last_post_id = $result[0]['post_id'];
    $old_post_time    = $result[0]['post_time'];

    // update
    $sql = 'UPDATE '.$pntable['dizkus_topics'].'
            SET topic_replies = topic_replies - 1,
                topic_last_post_id='.(int)DataUtil::formatForStore($old_last_post_id).',
                topic_time=\''.DataUtil::formatForStore($old_post_time).'\'
            WHERE topic_id='.(int)DataUtil::formatForStore($post['topic_id']);

    DBUtil::executeSQL($sql);

    return Dizkus_userapi_get_last_topic_page(array('topic_id' => $post['topic_id']));
}

/**
 * get_last_topic_page
 * returns the number of the last page of the topic if more than posts_per_page entries
 * eg. for use as the start parameter in urls
 *
 * @params $args['topic_id'] int the topic id
 * @returns int the page number
 */
function Dizkus_userapi_get_last_topic_page($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    // get some enviroment
    $posts_per_page = pnModGetVar('Dizkus', 'posts_per_page');
    $post_sort_order = pnModGetVar('Dizkus', 'post_sort_order');

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
function Dizkus_userapi_jointopics($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    // check if from_topic exists. this function will return an error if not
    $from_topic = pnModAPIFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $args['from_topic_id'], 'complete' => false, 'count' => false));
    if (!allowedtomoderatecategoryandforum($from_topic['cat_id'], $from_topic['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod', $from_topic['forum_id'], 'forum', __('Error! You do not have authorisation to moderate this category or forum.', $dom)), __FILE__, __LINE__);
    }

    // check if to_topic exists. this function will return an error if not
    $to_topic = pnModAPIFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $args['to_topic_id'], 'complete' => false, 'count' => false));
    if (!allowedtomoderatecategoryandforum($to_topic['cat_id'], $to_topic['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod', $to_topic['forum_id'], 'forum', __('Error! You do not have authorisation to moderate this category or forum.', $dom)), __FILE__, __LINE__);
    }

    $pntable = pnDBGetTables();
    
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
        $sql = "UPDATE ".$pntable['dizkus_forums']."
                SET forum_posts = forum_posts - $post_count_difference,
                    forum_last_post_id = '" . (int)DataUtil::formatForStore($from_forum_last_post_id) . "'
                WHERE forum_id='".(int)DataUtil::formatForStore($from_topic['forum_id'])."'";
        DBUtil::executeSQL($sql);

        // increment o_topic's forum post_count
        $sql = "UPDATE ".$pntable['dizkus_forums']."
                SET forum_posts = forum_posts + $post_count_difference,
                    forum_last_post_id = '" . (int)DataUtil::formatForStore($to_forum_last_post_id) . "'
                WHERE forum_id='".(int)DataUtil::formatForStore($to_topic['forum_id'])."'";
        DBUtil::executeSQL($sql);
    }
    return $to_topic['topic_id'];
}

/**
 * notify moderators
 *
 * @params $args['post'] array the post array
 * @returns void
 */
function Dizkus_userapi_notify_moderator($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    setlocale (LC_TIME, pnConfigGetVar('locale'));
    $modinfo = pnModGetInfo(pnModGetIDFromName(pnModGetName()));

    $mods = pnModAPIFunc('Dizkus', 'admin', 'readmoderators',
                         array('forum_id' => $args['post']['forum_id']));

    // generate the mailheader
    $email_from = pnModGetVar('Dizkus', 'email_from');
    if ($email_from == '') {
        // nothing in forumwide-settings, use PN adminmail
        $email_from = pnConfigGetVar('adminmail');
    }

    $subject .= DataUtil::formatForDisplay(__('Moderation request', $dom)) . ': ' . strip_tags($args['post']['topic_rawsubject']);
    $sitename = pnConfigGetVar('sitename');

    $recipients = array();
    // check if list is empty - then do nothing
    // we create an array of recipients here
    $admin_is_mod = false;
    if (is_array($mods) && count($mods) <> 0) {
        foreach ($mods as $mod) {
            if ($mod['uid'] > 1000000) {
                // mod_uid is gid
                $group = pnModAPIFunc('Groups', 'user', 'get', array('gid' => (int)$mod['uid'] - 1000000));
                if ($group <> false) {
                    foreach($group['members'] as $gm_uid)
                    {
                        $mod_email = pnUserGetVar('email', $gm_uid);
                        $mod_uname = pnUserGetVar('uname', $gm_uid);
                        if (!empty($mod_email)) {
                            array_push($recipients, array('uname' => $mod_uname,
                                                          'email' => $mod_email));
                        }
                        if ($gm_uid == 2) {
                            // admin is also moderator
                            $admin_is_mod = true;
                        }
                    }
                }

            } else {
                $mod_email = pnUserGetVar('email', $mod['uid']);
                //uname is alread stored in $mod['uname']
                if (!empty($mod_email)) {
                    array_push($recipients, array('uname' => $mod['uname'],
                                                  'email' => $mod_email));
                }
                if ($mod['uid'] == 2) {
                    // admin is also moderator
                    $admin_is_mod = true;
                }
            }
        }
    }
    // always inform the admin. he might be a moderator to so we check the
    // admin_is_mod flag now
    if ($admin_is_mod == false) {
        array_push($recipients, array('uname' => pnConfigGetVar('sitename'),
                                      'email' => $email_from));
    }

    $reporting_userid   = pnUserGetVar('uid');
    $reporting_username = pnUserGetVar('uname');

    $start = pnModAPIFunc('Dizkus', 'user', 'get_page_from_topic_replies',
                          array('topic_replies' => $args['post']['topic_replies'],
                                'start'         => $start));

    // FIXME Move this to a translatable template?
    $message = __f('Request for moderation on %s', pnConfigGetVar('sitename'), $dom) . "\n"
            . $args['post']['cat_title'] . '::' . $args['post']['forum_name'] . '::' . $args['post']['topic_rawsubject'] . "\n\n"
            . __f('Reporting user: %s', $reporting_username, $dom) . "\n"
            . __('Comment:', $dom) . "\n"
            . $comment . " \n\n"
            . "---------------------------------------------------------------------\n"
            . strip_tags($args['post']['post_text']) . " \n"
            . "---------------------------------------------------------------------\n\n"
            . __('Link to topic: %s', DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $args['post']['topic_id'], 'start' => $start), null, 'pid'.$args['post']['post_id'], true)), $dom) . "\n"
            . "\n";

    if (count($recipients) > 0) {
        foreach($recipients as $recipient) {
            pnModAPIFunc('Mailer', 'user', 'sendmessage',
                          array( 'fromname'    => $sitename,
                                 'fromaddress' => $email_from,
                                 'toname'      => $recipient['uname'],
                                 'toaddress'   => $recipient['email'],
                                 'subject'     => $subject,
                                 'body'        => $message,
                                 'headers'     => array('X-UserID: ' . $reporting_userid,
                                                        'X-Mailer: ' . $modinfo['name'] . ' ' . $modinfo['version'])));
        }
    }

    return;
}

/**
 * get_topicid_by_reference
 * gets a topic reference as parameter and delivers the internal topic id
 * used for Dizkus as comment module
 *
 * @params $args['reference'] string the refernce
 */
function Dizkus_userapi_get_topicid_by_reference($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!isset($args['reference']) || empty($args['reference'])) {
        return LogUtil::registerArgsError();
    }

    $topic_id = DBUtil::selectFieldByID('dizkus_topics', 'topic_id', $args['reference'], 'topic_reference');

    return $topic_id;
}

/**
 * insertrss
 *
 * @params $args['forum']    array with forum data
 * @params $args['items']    array with feed data as returned from Feeds module
 * @return boolean true or false
 */
function Dizkus_userapi_insertrss($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!$args['forum'] || !$args['items']) {
        return false;
    }

    $bbcode = pnModAvailable('bbcode');
    $boldstart = '';
    $boldend   = '';
    $urlstart  = '';
    $urlend    = '';
    if ($bbcode == true) {
        $boldstart = '[b]';
        $boldend   = '[/b]';
        $urlstart  = '[url]';
        $urlend    = '[/url]';
    }

    foreach ($args['items'] as $item)
    {
        // create the reference, we need it twice
        $dateTimestamp = $item->get_date("Y-m-d H:i");
        if (empty($dateTimestamp)) {
            $reference = md5($item->get_link());
            $dateTimestamp = date("Y-m-d H:i", time());
        } else {
            $reference = md5($item->get_link() . '-' . $dateTimestamp);
        }

        // Checking if the forum already has that news.
        $check = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_reference',
                              array('reference' => $reference));

        if ($check == false) {
            // Not found... we can add the news.
            $subject  = $item->get_title();

            // Adding little display goodies - finishing with the url of the news...
            $message  = $boldstart . __('Summary', $dom) . ' :' . $boldend . "\n\n" . $item->get_description() . "\n\n" . $urlstart . $item->get_link() . $urlend . "\n\n";

            // store message in forum
            $topic_id = pnModAPIFunc('Dizkus', 'user', 'storenewtopic',
                                     array('subject'          => $subject,
                                           'message'          => $message,
                                           'time'             => $dateTimestamp,
                                           'forum_id'         => $args['forum']['forum_id'],
                                           'attach_signature' => 0,
                                           'subscribe_topic'  => 0,
                                           'reference'        => $reference));

            if (!$topic_id) {
                // An error occured... get away before screwing more.
                return false;
            }
        }
    }

    return true;

}

/**
 * get_forum_subscriptions
 *
 * @params none
 * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 * @returns array with forum ids, may be empty
 */
function Dizkus_userapi_get_forum_subscriptions($args)
{
    if (isset($args['user_id'])) {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    } else {
        $args['user_id'] = pnUserGetVar('uid');
    }

    $pntable = pnDBGetTables();

    // read the topic ids
    $sql = 'SELECT f.' . $pntable['dizkus_forums_column']['forum_id'] . ',
                   f.' . $pntable['dizkus_forums_column']['forum_name'] . ',
                   c.' . $pntable['dizkus_categories_column']['cat_id'] . ',
                   c.' . $pntable['dizkus_categories_column']['cat_title'] . '
            FROM ' . $pntable['dizkus_subscription'] . ' AS fs,
                 ' . $pntable['dizkus_forums'] . ' AS f,
                 ' . $pntable['dizkus_categories'] . ' AS c 
            WHERE fs.' . $pntable['dizkus_subscription_column']['user_id'] . '=' . (int)DataUtil::formatForStore($args['user_id']) . '
              AND f.' . $pntable['dizkus_forums_column']['forum_id'] . '=fs.' . $pntable['dizkus_subscription_column']['forum_id'] . '
              AND c.' . $pntable['dizkus_categories_column']['cat_id'] . '=f.' . $pntable['dizkus_forums_column']['cat_id']. '
            ORDER BY c.' . $pntable['dizkus_categories_column']['cat_order'] . ', f.' . $pntable['dizkus_forums_column']['forum_order'];

    $res           = DBUtil::executeSQL($sql);
    $colarray      = array('forum_id', 'forum_name', 'cat_id', 'cat_title');
    $subscriptions = DBUtil::marshallObjects($res, $colarray);

    return $subscriptions;
}

/**
 * get_settings_ignorelist
 *
 * @params none
 * @params $args['uid']  int     the users id
 * @returns level for ignorelist handling as string
 */
function Dizkus_userapi_get_settings_ignorelist($args)
{
    // if Contactlist is not available there will be no ignore settings
    if (!pnModAvailable('ContactList')) {
        return false;
    }

    // get parameters
    $uid = (int)$args['uid'];
    if (!($uid > 1)) {
        return false;
    }

    $attr = pnUserGetVar('__ATTRIBUTES__', $uid);
    $ignorelist_myhandling = $attr['dzk_ignorelist_myhandling'];
    $default = pnModGetVar('Dizkus','ignorelist_handling');
    if (isset($ignorelist_myhandling) && ($ignorelist_myhandling != ''))
    {
        if (($ignorelist_myhandling == 'strict') && ($default != $ignorelist_myhandling)) {
            // maybe the admin value changed and the user's value is "higher" than the admin's value
            return $default;
        } else {
            // return user's value
            return $ignorelist_myhandling;
        }
    } else {
        // return admin's default value
        return $default;
    }
}
