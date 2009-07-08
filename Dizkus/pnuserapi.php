<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
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
 *@params $args{'userid'] int the users id (pn_uid)
 *@returns array of userdata information
 */
function Dizkus_userapi_get_userdata_from_id($args)
{
    $userid = $args['userid'];

    static $usersarray;

    //if(isset($usersarray) && is_array($usersarray) && array_key_exists($userid, $usersarray)) {
    if (is_array($usersarray) && isset($usersarray[$userid])) {
        return $usersarray[$userid];
    } else {
        // init array
        $usersarray = array();
    }


    $makedummy = false;
    // get the core user data
    $userdata = pnUserGetVars($userid);
    if($userdata==false) {
        // create a dummy user basing on Anonymous
        // necessary for some socks :-)
        $userdata = pnUserGetVars(1);
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
                                            array('uid'         => $userdata['pn_uid']));
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
            $userdata['rank_link'] = (substr($userdata['rank_desc'], 0, 7)=='http://') ? $userdata['rank_desc'] : '';
            if ($userdata['rank_image']) {
                $userdata['rank_image']      = pnModGetVar('Dizkus', 'url_ranks_images') . '/' . $userdata['rank_image'];
                $userdata['rank_image_attr'] = function_exists('getimagesize') ? @getimagesize($userdata['rank_image']) : null;
            }
        }
        
        //
        // user name and avatar
        //
        if($userdata['pn_uid'] != 1) {
            // user is logged in, display some info
            $activetime = DateUtil::getDateTime(time() - (pnConfigGetVar('secinactivemins') * 60));
            $where = $pntable['session_info_column']['uid']." = '".$userdata['pn_uid']."'
                      AND pn_lastused > '".DataUtil::formatForStore($activetime)."'";
            $sessioninfo =  DBUtil::selectObject('session_info', $where);         
            $userdata['online'] = ($sessioninfo['uid'] == $userdata['pn_uid']) ? true : false; 

           // avatar
            if ($userdata['_YOURAVATAR']){
                $avatarfilename = 'images/avatar/' . DataUtil::formatForOS($userdata['_YOURAVATAR']);
                $avatardata = function_exists('getimagesize') ? @getimagesize($avatarfilename) : false;
                if ($avatardata <> false) {
                    $userdata['pn_user_avatar'] = $avatarfilename;
                    $userdata['pn_user_avatar_attr'] = $avatardata;
                } else {
                    $userdata['pn_user_avatar'] = '';
                }
            }

        } else {
            // user is anonymous
            $userdata['pn_uname'] = pnModGetVar('Users', 'anonymous');
        }
    }

    if($makedummy == true) {
        // we create a dummy user, so we need to adjust some of the information
        // gathered so far
        $userdata['pn_name']   = DataUtil::formatForDisplay(_DZK_UNKNOWNUSER);
        $userdata['pn_uname']  = DataUtil::formatForDisplay(_DZK_UNKNOWNUSER);
        $userdata['pn_email']  = '';
        $userdata['pn_femail'] = '';
        $userdata['pn_url']    = '';
        $userdata['name']      = DataUtil::formatForDisplay(_DZK_UNKNOWNUSER);
        $userdata['uname']     = DataUtil::formatForDisplay(_DZK_UNKNOWNUSER);
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
 *@params $args['id'] int the id, depends on 'type' parameter
 *@params $args['type'] string, defines the id parameter
 *@returns int (depending on type and id)
 */
function Dizkus_userapi_boardstats($args)
{
    $id   = isset($args['id']) ? $args['id'] : null;
    $type = isset($args['type']) ? $args['type'] : null;
   
    static $cache = array();

    switch($type) {
        case 'all':
        case 'allposts':
            if(!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_posts');
            }
            
            return $cache[$type];
            break;
        case 'category':
            if(!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_categories');
            }
            
            return  $cache[$type];
            break;
        case 'forum':
            if(!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_forums');
            }
            
            return $cache[$type];
            break;
        case 'topic':
            if(!isset($cache[$type][$id])){
               $cache[$type][$id] = DBUtil::selectObjectCount('dizkus_posts', 'WHERE topic_id = ' .(int)DataUtil::formatForStore($id));
            }
            
            return  $cache[$type][$id];
            break;
        case 'forumposts':
            if(!isset($cache[$type][$id])){
               $cache[$type][$id] = DBUtil::selectObjectCount('dizkus_posts', 'WHERE forum_id = ' .(int)DataUtil::formatForStore($id));
            }
            
            return  $cache[$type][$id];
            break;
        case 'forumtopics':
            if(!isset($cache[$type][$id])){
               $cache[$type][$id] = DBUtil::selectObjectCount('dizkus_topics', 'WHERE forum_id = ' .(int)DataUtil::formatForStore($id));
            }
            
            return  $cache[$type][$id];
            break;
        case 'alltopics':
            if(!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_topics');
            }
            
            return  $cache[$type];
            break;
        case 'allmembers':
            if(!isset($cache[$type])){
               $cache[$type] = DBUtil::selectObjectCount('dizkus_users');
            }
            
            return  $cache[$type];
            break;
        case 'lastmember':
        case 'lastuser':
            if(!isset($cache[$type])){
                $res = DBUtil::selectObjectArray('users', null, 'uid DESC', 1, 1);
                $cache[$type] = $res[0]['uname'];
            }
            
            return  $cache[$type];
            break;
        default:
            return showforumerror(_MODARGSERROR . ' in Dizkus_userapi_boardstats()', __FILE__, __LINE__);
        }
    return $total;
}

/**
 * get_firstlast_post_in_topic
 * gets the first or last post in a topic, false if no posts
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['first']   boolean if true then get the first posting, otherwise the last
 *@params $args['id_only'] boolean if true, only return the id, not the complete post information
 *@returns array with post information or false or (int)id if id_only is true
 */
function Dizkus_userapi_get_firstlast_post_in_topic($args)
{
    if (!empty($args['topic_id']) && is_numeric($args['topic_id'])) {
        $pntable = pnDBGetTables();
        $option    = (isset($args['first']) && $args['first'] == true) ? 'MIN' : 'MAX';
        $post_id = DBUtil::selectfieldMax('dizkus_posts', 'post_id', $option, $pntable['dizkus_posts_column']['topic_id'].' = '.(int)DataUtil::formatForStore($args['topic_id']));

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
 *@params $args['forum_id'] int the forums id
 *@params $args['id_only'] boolean if true, only return the id, not the complete post information
 *@returns array with post information of false
 */
function Dizkus_userapi_get_last_post_in_forum($args)
{
    if (!empty($args['forum_id']) && is_numeric($args['forum_id'])) {
        $pntable = pnDBGetTables();
        $post_id = DBUtil::selectfieldMax('dizkus_posts', 'post_id', 'MAX', $pntable['dizkus_posts_column']['forum_id'].' = '.(int)DataUtil::formatForStore($args['forum_id']));

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
 *@params $args['last_visit'] string the users last visit date as returned from setcookies() function
 *@returns array of categories with an array of forums in the catgories
 *
 */
function Dizkus_userapi_readcategorytree($args)
{
    $last_visit = (isset($args['last_visit'])) ? $args['last_visit'] : 0;

    static $tree;

    $dizkusvars = pnModGetVar('Dizkus');

    // if we have already called this once during the script
    if (isset($tree)) {
        return $tree;
    }

    list($dbconn, $pntable) = dzkOpenDB();
    $cattable = $pntable['dizkus_categories'];
    $forumstable = $pntable['dizkus_forums'];
    $poststable = $pntable['dizkus_posts'];
    $topicstable = $pntable['dizkus_topics'];
    $userstable = $pntable['users'];

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

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $posts_per_page = pnModGetVar('Dizkus', 'posts_per_page');

    $tree = array();
    while (!$result->EOF) {
        $row   = $result->GetRowAssoc(false);
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
                    $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($posted_unixtime));
                    if ($posted_unixtime) {
                        if ($forum['pn_uid']==1) {
                            $username = pnModGetVar('Users', 'anonymous');
                        } else {
                            $username = $forum['pn_uname'];
                        }

                        $last_post = sprintf(_DZK_LASTPOSTSTRING, $posted_ml, $username);
                        $last_post = $last_post.' <a href="' . pnModURL('Dizkus','user','viewtopic', array('topic' =>$forum['topic_id'])). '">
                                                  <img src="modules/Dizkus/pnimages/icon_latest_topic.gif" alt="' . $posted_ml . ' ' . $username . '" height="9" width="18" /></a>';
                        // new in 2.0.2 - no more preformattd output
                        $last_post_data['name']     = $username;
                        $last_post_data['subject']  = $topic_title;
                        $last_post_data['time']     = $posted_ml;
                        $last_post_data['unixtime'] = $posted_unixtime;
                        $last_post_data['topic']    = $forum['topic_id'];
                        $last_post_data['post']     = $forum['forum_last_post_id'];
                        $last_post_data['url'] = pnModURL('Dizkus', 'user', 'viewtopic',
                                                                    array('topic' => $forum['topic_id'],
                                                                          'start' => (ceil(($topic_replies + 1)  / $posts_per_page) - 1) * $posts_per_page));
                        $last_post_data['url_anchor'] = $last_post_data['url'] . '#pid' . $forum['forum_last_post_id'];
                    } else {
                        // no posts in forum
                        $last_post = _DZK_NOPOSTS;
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
                    $last_post = _DZK_NOPOSTS;
                }
                $forum['last_post'] = $last_post;
                $forum['forum_mods'] = Dizkus_userapi_get_moderators(array('forum_id' => $forum['forum_id']));

                // is the user subscribed to the forum?
                $forum['is_subscribed'] = 0;
                if (Dizkus_userapi_get_forum_subscription_status(array('userid' => pnUserGetVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
                    $forum['is_subscribed'] = 1;
                }

                // is this forum in the favorite list?
                $forum['is_favorite'] = 0;
                if ($dizkusvars['favorites_enabled'] == 'yes') {
                    if(Dizkus_userapi_get_forum_favorites_status(array('userid' => pnUserGetVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
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
        $result->MoveNext();
    }
    // sort the array by cat_order
    uasort($tree, 'cmp_catorder');
    dzkCloseDB($result);
    return $tree;
}

/**
 * Returns an array of all the moderators of a forum
 *
 *@params $args['forum_id'] int the forums id
 *@returns array containing the pn_uid as index and the users name as value
 */
function Dizkus_userapi_get_moderators($args)
{
    $forum_id = isset($args['forum_id']) ? $args['forum_id'] : null;

    list($dbconn, $pntable) = dzkOpenDB();

    if (!empty($forum_id)) {
        $sql = 'SELECT u.pn_uname, u.pn_uid
                FROM '.$pntable['users'].' u, '.$pntable['dizkus_forum_mods'].' f
                WHERE f.forum_id = \''.DataUtil::formatForStore($forum_id).'\' AND u.pn_uid = f.user_id
                AND f.user_id < 1000000';
    } else {
        $sql = 'SELECT u.pn_uname, u.pn_uid
                FROM '.$pntable['users'].' u, '.$pntable['dizkus_forum_mods'].' f
                WHERE u.pn_uid = f.user_id
                AND f.user_id < 1000000
                GROUP BY f.user_id';
    }
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $mods = array();
    if ($result->RecordCount() > 0) {
        for (; !$result->EOF; $result->MoveNext()){
            list($uname, $uid) = $result->fields;
            $mods[$uid] = $uname;
        }
    }
    dzkCloseDB($result);

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
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if ($result->RecordCount() > 0) {
        for (; !$result->EOF; $result->MoveNext()) {
            list($gname, $gid) = $result->fields;
            $mods[$gid + 1000000] = $gname;
        }
    }
    dzkCloseDB($result);

    return $mods;
}

/**
 * setcookies
 * reads the cookie, updates it and returns the last visit date in readable (%Y-%m-%d %H:%M)
 * and unix time format
 *
 *@params none
 *@returns array of (readable last visits data, unix time last visit date)
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

    setcookie('phpBBLastVisit', time(), time()+31536000, $path);

    if (!isset($_COOKIE['phpBBLastVisitTemp'])){
        $temptime = isset($_COOKIE['phpBBLastVisit']) ? $_COOKIE['phpBBLastVisit'] : '';
    } else {
        $temptime = $_COOKIE['phpBBLastVisitTemp'];
    }

    if (empty($temptime)) {
        $temptime = 0;
    }

    // set LastVisitTemp cookie, which only gets the time from the LastVisit and lasts for 30 min
    setcookie('phpBBLastVisitTemp', $temptime, time()+1800, $path);

    // set vars for all scripts
    $last_visit = ml_ftime('%Y-%m-%d %H:%M',$temptime);
    return array($last_visit, $temptime);
}

/**
 * readforum
 * reads the forum information and the last posts_per_page topics incl. poster data
 *
 *@params $args['forum_id'] int the forums id
 *@params $args['start'] int number of topic to start with (if on page 1+)
 *@params $args['last_visit'] string users last visit date
 *@params $args['last_visit_unix'] string users last visit date as timestamp
 *@params $args['topics_per_page'] int number of topics to read, -1 = all topics
 *@returns very complex array, see <!--[ debug ]--> for more information
 */
function Dizkus_userapi_readforum($args)
{
    extract($args);
    unset($args);

    $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                          array('forum_id' => $forum_id,
                                'permcheck' => 'nocheck' ));
    if($forum==false) {
        return showforumerror(_DZK_FORUM_NOEXIST, __FILE__, __LINE__, '404 Not Found');
    }

    if(!allowedtoseecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(getforumerror('auth_overview',$forum['forum_id'], 'forum', _DZK_NOAUTH_TOSEE), __FILE__, __LINE__);
    }

    list($dbconn, $pntable) = dzkOpenDB();

    $posts_per_page     = pnModGetVar('Dizkus', 'posts_per_page');
    if(empty($topics_per_page)) {
        $topics_per_page    = pnModGetVar('Dizkus', 'topics_per_page');
    }
    $hot_threshold      = pnModGetVar('Dizkus', 'hot_threshold');
    $post_sort_order    = pnModAPIFunc('Dizkus','user','get_user_post_order');

    // read moderators
    $forum['forum_mods'] = Dizkus_userapi_get_moderators(array('forum_id' => $forum['forum_id']));
    $forum['last_visit'] = $last_visit;

    $forum['topic_start'] = (!empty ($start)) ? $start : 0;

    // is the user subscribed to the forum?
    $forum['is_subscribed'] = 0;
    if(Dizkus_userapi_get_forum_subscription_status(array('userid' => pnUserGetVar('uid'), 'forum_id' => $forum_id)) == true) {
        $forum['is_subscribed'] = 1;
    }

    // is this forum in the favorite list?
    $forum['is_favorite'] = 0;
    if(Dizkus_userapi_get_forum_favorites_status(array('userid' => pnUserGetVar('uid'), 'forum_id' => $forum_id)) == true) {
        $forum['is_favorite'] = 1;
    }

    // if user can write into Forum, set a flag
    $forum['access_comment'] = allowedtowritetocategoryandforum($forum['cat_id'], $forum['forum_id']);

    // if user can moderate Forum, set a flag
    $forum['access_moderate'] = allowedtomoderatecategoryandforum($forum['cat_id'], $forum['forum_id']);

    // forum_pager is obsolete, inform the user about this
    $forum['forum_pager'] = 'deprecated data field $forum.forum_pager used, please update your template using the forumpager plugin';

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

    $sql = "SELECT t.topic_id,
                   t.topic_title,
                   t.topic_views,
                   t.topic_replies,
                   t.sticky,
                   t.topic_status,
                   t.topic_last_post_id,
                   u.pn_uname,
                   u2.pn_uname as last_poster,
                   p.post_time,
                   t.topic_poster
            FROM $pntable[dizkus_topics] AS t
            LEFT JOIN $pntable[users] AS u ON t.topic_poster = u.pn_uid
            LEFT JOIN $pntable[dizkus_posts] AS p ON t.topic_last_post_id = p.post_id
            LEFT JOIN $pntable[users] AS u2 ON p.poster_id = u2.pn_uid
            WHERE t.forum_id = '".(int)DataUtil::formatForStore($forum_id)."'
            $whereignorelist
            ORDER BY t.sticky DESC, p.post_time DESC";
            //ORDER BY t.sticky DESC"; // RNG
            //ORDER BY t.sticky DESC, p.post_time DESC";
//FC            ORDER BY t.sticky DESC"; // RNG
//FC            //ORDER BY t.sticky DESC, p.post_time DESC";

    $result = dzkSelectLimit($dbconn, $sql, $topics_per_page, $start, __FILE__, __LINE__);

    $forum['forum_id'] = $forum_id;
    $forum['topics'] = array();
    while(!$result->EOF) {
        $topic = array();
        $topic = $result->GetRowAssoc(false);
        //$topic = $row;

        if ($topic['last_poster'] == 'Anonymous') {$topic['last_poster'] = pnModGetVar('Users', 'anonymous'); }
        if ($topic['pn_uname'] == 'Anonymous') {$topic['pn_uname'] = pnModGetVar('Users', 'anonymous'); }
        $topic['total_posts'] = $topic['topic_replies'] + 1;

        $topic['post_time_unix'] = dzk_str2time($topic['post_time']); //strtotime ($topic['post_time']);
        $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($topic['post_time_unix']));
        $topic['last_post'] = sprintf(_DZK_LASTPOSTSTRING, DataUtil::formatForDisplay($posted_ml), DataUtil::formatForDisplay($topic['last_poster']));

        // does this topic have enough postings to be hot?
        $topic['hot_topic'] = ($topic['topic_replies'] >= $hot_threshold) ? true : false;
        // does this posting have new postings?
        $topic['new_posts'] = ($topic['post_time'] < $last_visit) ? false : true;

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

            $pagination .= '&nbsp;&nbsp;&nbsp;<span class="pn-sub">(' . DataUtil::formatForDisplay(_DZK_GOTOPAGE) . '&nbsp;';
            $pagenr = 1;
            $skippages = 0;
            for($x = 0; $x < $topic['topic_replies'] + 1; $x += $posts_per_page) {
                $lastpage = (($x + $posts_per_page) >= $topic['topic_replies'] + 1);

                if ($lastpage) {
                    $start = $x;
                } else {
                    if ($x != 0) {
                        $start = $x;
                    }
                }

                if ($pagenr > 3 && $skippages != 1 && !$lastpage) {
                    $pagination .= ', ... ';
                    $skippages = 1;
                }

                if ($skippages != 1 || $lastpage) {
                    if ($x!=0) $pagination .= ', ';
                    $pagination .= '<a href="' . pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic['topic_id'], 'start' => $start)) . '" title="' . $topic['topic_title'] . ' ' . DataUtil::formatForDisplay(_DZK_PAGE) . ' ' . $pagenr . '">' . $pagenr . '</a>';
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

        array_push( $forum['topics'], $topic );
        $result->MoveNext();
    }
    dzkCloseDB($result);

    $topics_start = $start; // RNG: is this still correct?

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
 *@params $args['topic_id'] it the topics id
 *@params $args['start'] int number of posting to start with (if on page 1+)
 *@params $args['complete'] bool if true, reads the complete thread and does not care about
 *                               the posts_per_page setting, ignores 'start'
 *@params $args['last_visit'] string the users last visit date
 *@params $args['count']      bool  true if we have raise the read counter, default false
 *@params $args['nohook']     bool  true if transform hooks should not modify post text
 *@returns very complex array, see <!--[ debug ]--> for more information
 */
function Dizkus_userapi_readtopic($args)
{
//$time_start = microtime_float();
    extract($args);
    unset($args);

    $dizkusvars      = pnModGetVar('Dizkus');
    $posts_per_page  = $dizkusvars['posts_per_page'];
    $topics_per_page = $dizkusvars['topics_per_page'];

    $post_sort_order = pnModAPIFunc('Dizkus','user','get_user_post_order');

    $complete = (isset($complete)) ? $complete : false;
    $count    = (isset($count)) ? $count : false;
    $start    = (isset($start)) ? $start : 0;
    $hooks    = (isset($nohook) && $nohook == false) ? false : true;

    $currentuserid = pnUserGetVar('uid');
    $now = time();
    $timespanforchanges = !empty($dizkusvars['timespanforchanges']) ? $dizkusvars['timespanforchanges'] : 24;
    $timespansecs = $timespanforchanges * 60 * 60;

    list($dbconn, $pntable) = dzkOpenDB();

    $tbltopics = $pntable['dizkus_topics'];
    $tblforums = $pntable['dizkus_forums'];
    $tblcats   = $pntable['dizkus_categories'];

    $coltopics = $pntable['dizkus_topics_column'];
    $colforums = $pntable['dizkus_forums_column'];
    $colcats   = $pntable['dizkus_categories_column'];
/*
    $sql = "SELECT    $coltopics[topic_title],
                      $coltopics[topic_status],
                      $coltopics[topic_poster],
                      $coltopics[forum_id],
                      $coltopics[sticky],
                      $coltopics[topic_time],
                      $coltopics[topic_replies],
                      $coltopics[topic_last_post_id],
                      $colforums[forum_name],
                      $colforums[cat_id],
                      $colforums[forum_pop3_active],
                      $colcats[cat_title]
            FROM      $tbltopics
            LEFT JOIN $tblforums
            ON        $colforums[forum_id] = $coltopics[forum_id]
            LEFT JOIN $tblcats
            ON        $colcats[cat_id]     = $colforums[cat_id]
            WHERE     $coltopics[topic_id] = '".(int)DataUtil::formatForStore($topic_id)."'";
*/

    $pntables = pnDBGetTables();
    $topicscolumn = $pntables['dizkus_topics_column'];
/*
    $joinarray = array();
    // join for forums table
    $joinarray[] = array('join_table'          => 'dizkus_forums',
                         'join_field'          => array('forum_name', 'cat_id'),
                         'object_field_name'   => array('forum_name', 'cat_id'),
                         'compare_field_table' => 'forum_id',
                         'compare_field_join'  => 'forum_id');
    // join for categories table
    $joinarray[] = array('base_table'          => 'dizkus_forums',
                         'join_table'          => 'dizkus_categories',
                         'join_field'          => array('cat_title'),
                         'object_field_name'   => array('cat_title'),
                         'compare_field_table' => 'cat_id',
                         'compare_field_join'  => 'cat_id');
    $where = 'WHERE ' . $topicscolumn['topic_id'] . '=' . DataUtil::formatForStore($topic_id);
    $topic = DBUtil::selectExpandedObject('dizkus_topics', $joinarray, $where);
prayer($topic);
pnShutDown();
*/
    $sql = "SELECT t.topic_title,
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
            FROM  $pntable[dizkus_topics] t
            LEFT JOIN $pntable[dizkus_forums] f ON f.forum_id = t.forum_id
            LEFT JOIN $pntable[dizkus_categories] AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

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

    $topic = array();
    if (!$result->EOF) {
        $topic = $result->GetRowAssoc(false);
        $topic['topic_id'] = $topic_id;
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
            return showforumerror(getforumerror('auth_read',$topic['forum_id'], 'forum', _DZK_NOAUTH_TOREAD), __FILE__, __LINE__);
        }

        $topic['forum_mods'] = Dizkus_userapi_get_moderators(array('forum_id' => $topic['forum_id']));

        $topic['access_see']      = allowedtoseecategoryandforum($topic['cat_id'], $topic['forum_id']);
        $topic['access_read']     = $topic['access_see'] && allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id'], $currentuserid);
        $topic['access_comment']  = false;
        $topic['access_moderate'] = false;
        $topic['access_admin']    = false;
        if($topic['access_read'] == true) {
            $topic['access_comment']  = $topic['access_read'] && allowedtowritetocategoryandforum($topic['cat_id'], $topic['forum_id'], $currentuserid);
            if($topic['access_comment'] == true) {
                $topic['access_moderate'] = $topic['access_comment'] && allowedtomoderatecategoryandforum($topic['cat_id'], $topic['forum_id'], $currentuserid);
                if($topic['access_moderate'] == true) {
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
        $topic['next_topic_id'] = Dizkus_userapi_get_previous_or_next_topic_id(array('topic_id'=>$topic['topic_id'], 'view'=>'next'));
        $topic['prev_topic_id'] = Dizkus_userapi_get_previous_or_next_topic_id(array('topic_id'=>$topic['topic_id'], 'view'=>'previous'));

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
            DBUtil::incrementObjectFieldByID('dizkus_topics', 'topic_views', $topic_id, 'topic_id');
            //
            //$sql = "UPDATE ".$pntable['dizkus_topics']."
            //        SET topic_views = topic_views + 1
            //        WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";
            //$result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            //
        }
        /**
         * more then one page in this topic?
         */
        $topic['total_posts'] = Dizkus_userapi_boardstats(array('id'=>$topic_id, 'type' => 'topic'));

        if ($topic['total_posts'] > $posts_per_page) {
            $times = 0;
            for ($x = 0; $x < $topic['total_posts']; $x += $posts_per_page) {
                $times++;
            }
            $topic['pages'] = $times;
        }

        $topic['post_start'] = (!empty($start)) ? $start : 0;

        // topic_pager is obsolete, inform the user about this
        $topic['topic_pager'] = 'deprecated data field $topic.topic_pager used, please update your template using the topicpager plugin';

        $topic['posts'] = array();

        // read posts
        $sql2 = "SELECT post_id,
                        poster_id,
                        post_time,
                        post_text
                FROM ".$pntable['dizkus_posts']."
                WHERE topic_id = '".(int)DataUtil::formatForStore($topic['topic_id'])."'
                ORDER BY post_id $post_sort_order";

        if ($complete == true) {
            $result2 = dzkExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
        } elseif(isset($start)) {
            // $start is given
            $result2 = dzkSelectLimit($dbconn, $sql2, $posts_per_page, $start, __FILE__, __LINE__);
        } else {
            $result2 = dzkSelectLimit($dbconn, $sql2, $posts_per_page, false, __FILE__, __LINE__);
        }

        // performance patch:
        // we store all userdata read for the single postings in the $userdata
        // array for later use. If user A is referenced more than once in the
        // topic, we do not need to load his dat again from the db.
        // array index = userid
        // array value = array with user information
        // this increases the amount of memory used but speeds up the loading of topics
        $userdata = array();

        while (!$result2->EOF) {
            $post = $result2->GetRowAssoc(false);

            $post['topic_id'] = $topic_id;

            // check if array_key_exists() with poster _id in $userdata
            //if(!array_key_exists($post['poster_id'], $userdata)) {
            if (!isset($userdata[$post['poster_id']])) {
                // not in array, load the data now...
                $userdata[$post['poster_id']] = Dizkus_userapi_get_userdata_from_id(array('userid' => $post['poster_id']));
            }
            // we now have the data and use them
            $post['poster_data'] = $userdata[$post['poster_id']];
            $post['posted_unixtime'] = dzk_str2time($post['post_time']); //strtotime ($post['post_time']);
            $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($post['posted_unixtime']));
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
            $result2->MoveNext();
        }
        dzkCloseDB($result2);
        unset($userdata);
    } else {
        // no results - topic does not exist
        return showforumerror(_DZK_TOPIC_NOEXIST, __FILE__, __LINE__, '404 Not Found');
    }
    dzkCloseDB($result);
/*
$time_end = microtime_float();
$time_used = $time_end - $time_start;
dzkdebug('time:', $time_used);
*/
    return $topic;
}

/**
 * preparereply
 * prepare a reply to a posting by reading the last ten postign in revers order for review
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['post_id'] int the post id to reply to
 *@params $args['quote'] bool if user wants to qupte or not
 *@params $args['last_visit'] string the users last visit data
 *@params $args['reply_start'] bool true if we start a new reply
 *@params $args['attach_signature'] int 1=attach signature, otherwise no
 *@params $args['subscribe_topic'] int =subscribe topic, otherwise no
 *@returns very complex array, see <!--[ debug ]--> for more information
 */
function Dizkus_userapi_preparereply($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    $reply = array();

    if ($post_id <> 0) {
        // We have a post id, so include that in the checks
        // create a reply with quote
        $sql = "SELECT f.forum_id,
                       f.cat_id,
                       t.topic_id,
                       t.topic_title,
                       t.topic_status,
                       p.post_text,
                       p.post_time,
                       u.pn_uname
                FROM ".$pntable['dizkus_forums']." AS f,
                     ".$pntable['dizkus_topics']." AS t,
                     ".$pntable['dizkus_posts']." AS p,
                     ".$pntable['users']." AS u
                WHERE (p.post_id = '".(int)DataUtil::formatForStore($post_id)."')
                AND (t.forum_id = f.forum_id)
                AND (p.topic_id = t.topic_id)
                AND (p.poster_id = u.pn_uid)";
    } else {
        // No post id, just check topic.
        // reply without quote
        $sql = "SELECT f.forum_id,
                       f.cat_id,
                       t.topic_id,
                       t.topic_title,
                       t.topic_status
                FROM ".$pntable['dizkus_forums']." AS f,
                     ".$pntable['dizkus_topics']." AS t
                WHERE (t.topic_id = '".(int)DataUtil::formatForStore($topic_id)."')
                AND (t.forum_id = f.forum_id)";
    }
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if ($result->EOF) {
        return showforumerror(_DZK_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    dzkCloseDB($result);

    $reply['forum_id'] = DataUtil::formatForDisplay($myrow['forum_id']);
    $reply['cat_id'] = DataUtil::formatForDisplay($myrow['cat_id']);
    $reply['topic_subject'] = DataUtil::formatForDisplay($myrow['topic_title']);
    $reply['topic_status'] = DataUtil::formatForDisplay($myrow['topic_status']);
    $reply['topic_id'] = DataUtil::formatForDisplay($myrow['topic_id']);
    // the next line is only producing a valid result, if we get a post_id which
    // means we are producing a reply with quote
    if (array_key_exists('post_text', $myrow)) {
        $text = Dizkus_bbdecode($myrow['post_text']);
        $text = preg_replace('/(<br[ \/]*?>)/i', '', $text);
        // just for backwards compatibility
        $text = Dizkus_undo_make_clickable($text);
        $text = str_replace('[addsig]', '', $text);
        $reply['message'] = '[quote='.$myrow['pn_uname'].']'.$text.'[/quote]';
    } else {
        $reply['message'] = '';
    }

    // anonymous user has uid=0, but needs pn_uid=1
    // also check subscription status here
    if(!pnUserLoggedin()) {
        $pn_uid = 1;
        $reply['attach_signature'] = false;
        $reply['subscribe_topic'] = false;
    } else {
        $pn_uid = pnUserGetVar('uid');
        // get the users topic_subscription status to show it in the quick repliy checkbox
        // correctly
        if($reply_start==true) {
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
            $reply['attach_signature'] = $attach_signature;
            $reply['subscribe_topic'] = $subscribe_topic;
        }
    }
    $reply['poster_data'] = Dizkus_userapi_get_userdata_from_id(array('userid'=>$pn_uid));

    if ($reply['topic_status'] == 1) {
        return showforumerror(_DZK_NOPOSTLOCK, __FILE__, __LINE__);
    }

    if (!allowedtowritetocategoryandforum($reply['cat_id'], $reply['forum_id'])) {
        return showforumerror( _DZK_NOAUTH_TOWRITE, __FILE__, __LINE__);
    }

    // Topic review (show last 10)
    $sql = "SELECT p.post_id,
                   p.poster_id,
                   p.post_time,
                   p.post_text,
                   t.topic_title
                    FROM $pntable[dizkus_posts_text] pt, $pntable[dizkus_posts] p
                        LEFT JOIN $pntable[dizkus_topics] t ON t.topic_id=p.topic_id
                        WHERE p.topic_id = '" . (int)DataUtil::formatForStore($reply['topic_id']) . "' 
                        ORDER BY p.post_id DESC";

    $result = dzkSelectLimit($dbconn, $sql, 10, false, __FILE__, __LINE__);
    $reply['topic_review'] = array();
    while (!$result->EOF) {
        $review = array();
        $row = $result->GetRowAssoc(false);
        $review = $row;
        $review['user_name'] = pnUserGetVar('uname', $review['poster_id']);
        if ($review['user_name'] == '') {
            // user deleted from the db?
            $review['poster_id'] = 1;
        }

        $review['poster_data'] = Dizkus_userapi_get_userdata_from_id(array('userid' => $review['poster_id']));

        // TODO extract unixtime directly from MySql
        $review['post_unixtime'] = dzk_str2time($review['post_time']); //strtotime ($review['post_time']);
        $review['post_ml'] = ml_ftime(_DATETIMEBRIEF, GetUserTime($review['post_unixtime']));

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
        $result->MoveNext();
    }
    dzkCloseDB($result);

    return $reply;
}

/**
 * storereply
 * store the users reply in the database
 *
 *@params $args['message'] string the text
 *@params $args['title'] string the posting title
 *@params $args['topic_id'] int the topics id
 *@params $args['forum_id'] int the forums id
 *@params $args['attach_signature'] int 1=yes, otherwise no
 *@params $args['subscribe_topic'] int 1=yes, otherwise no
 *@returns array(int, int) start parameter and new post_id
 */
function Dizkus_userapi_storereply($args)
{
    list($forum_id, $cat_id) = pnModAPIFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                            array('topic_id' => $args['topic_id']));

    if (!allowedtowritetocategoryandforum($cat_id, $forum_id)) {
        return showforumerror(_DZK_NOAUTH_TOWRITE);
    }

    if (trim($args['message']) == '') {
        return showforumerror(_DZK_EMPTYMSG, __FILE__, __LINE__);
    }

    list($dbconn, $pntable) = dzkOpenDB();

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
        // for privavy issues ip logging can be deactivated
        $poster_ip = '127.0.0.1';
    } else {
        // some enviroment for logging ;)
        $poster_ip = pnServerGetVar('HTTP_X_FORWARDED_FOR');
        if(empty($poster_ip)){
            $poster_ip = pnServerGetVar('REMOTE_ADDR');
        }
    }

    // Prep for DB
    $obj['post_time']  = DataUtil::formatForStore(date('Y-m-d H:i'));
    $obj['topic_id']   = DataUtil::formatForStore($args['topic_id']);
    $obj['forum_id']   = (int)DataUtil::formatForStore($forum_id);
    $obj['post_text']  = DataUtil::formatForStore($args['message']);
    $obj['poster_id']  = DataUtil::formatForStore($pn_uid);
    $obj['poster_ip']  = DataUtil::formatForStore($poster_ip);
    $obj['post_title'] = DataUtil::formatForStore($args['post_title']);

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
        if($args['subscribe_topic']==1) {
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
 *@params $args['userid'] int the users pn_uid
 *@params $args['topic_id'] int the topic id
 *@returns bool true if the user is subscribed or false if not
 */
function Dizkus_userapi_get_topic_subscription_status($args)
{
    $pntables = pnDBGetTables();
    $tsubcolumn = $pntables['dizkus_topic_subscription_column'];
    
    $where = ' WHERE ' . $tsubcolumn['user_id'] . '=' . (int)DataUtil::formatForStore($args['userid']) . 
             ' AND '   . $tsubcolumn['topic_id'] . '=' . (int)DataUtil::formatForStore($args['topic_id']);
    $count = DBUtil::selectObjectCount('dizkus_topic_subscription', $where);
    return $count>0;
}

/**
 * get_forum_subscription_status
 *
 *@params $args['userid'] int the users pn_uid
 *@params $args['forum_id'] int the forums id
 *@returns bool true if the user is subscribed or false if not
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
 *@params $args['userid'] int the users pn_uid
 *@params $args['forum_id'] int the forums id
 *@returns bool true if the user is subscribed or false if not
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
 *@params $args['message'] string the text (only set when preview is selected)
 *@params $args['subject'] string the subject (only set when preview is selected)
 *@params $args['forum_id'] int the forums id
 *@params $args['topic_start'] bool true if we start a new topic
 *@params $args['attach_signature'] int 1= attach signature, otherwise no
 *@params $args['subscribe_topic'] int 1= subscribe topic, otherwise no
 *@returns array with information....
 */
function Dizkus_userapi_preparenewtopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    $newtopic = array();
    $newtopic['forum_id'] = $forum_id;

    // select forum name and cat title based on forum_id
    $sql = "SELECT f.forum_name,
                   c.cat_id,
                   c.cat_title
            FROM ".$pntable['dizkus_forums']." AS f,
                ".$pntable['dizkus_categories']." AS c
            WHERE (forum_id = '".(int)DataUtil::formatForStore($forum_id)."'
            AND f.cat_id=c.cat_id)";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    $myrow = $result->GetRowAssoc(false);
    dzkCloseDB($result);

    $newtopic['cat_id']     = $myrow['cat_id'];
    $newtopic['forum_name'] = DataUtil::formatForDisplay($myrow['forum_name']);
    $newtopic['cat_title']  = DataUtil::formatForDisplay($myrow['cat_title']);

    $newtopic['topic_unixtime'] = time();

    // need at least "comment" to add newtopic
    if(!allowedtowritetocategoryandforum($newtopic['cat_id'], $newtopic['forum_id'])) {
        // user is not allowed to post
        return showforumerror(_DZK_NOAUTH_TOWRITE, __FILE__, __LINE__);
    }
    $newtopic['poster_data'] = Dizkus_userapi_get_userdata_from_id(array('userid' => pnUserGetVar('uid')));

    $newtopic['subject'] = $subject;
    $newtopic['message'] = $message;
    $newtopic['message_display'] = $message; // phpbb_br2nl($message);

    list($newtopic['message_display']) = pnModCallHooks('item', 'transform', '', array($newtopic['message_display']));
    $newtopic['message_display'] = nl2br($newtopic['message_display']);

    if(pnUserLoggedIn()) {
        if($topic_start==true) {
            $newtopic['attach_signature'] = true;
            $newtopic['subscribe_topic']  = (pnModGetVar('Dizkus', 'autosubscribe')=='yes') ? true : false;
        } else {
            $newtopic['attach_signature'] = $attach_signature;
            $newtopic['subscribe_topic']  = $subscribe_topic;
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
 *@params $args['subject'] string the subject
 *@params $args['message'] string the text
 *@params $args['forum_id'] int the forums id
 *@params $args['time'] string (optional) the time, only needed when creating a shadow
 *                             topic
 *@params $args['attach_signature'] int 1=yes, otherwise no
 *@params $args['subscribe_topic'] int 1=yes, otherwise no
 *@params $args['reference']  string for comments feature: <modname>-<objectid>
 *@params $args['post_as']    int used id under which this topic should be posted
 *@returns int the new topics id
 */
function Dizkus_userapi_storenewtopic($args)
{
    extract($args);
    unset($args);

    $pntable = pnDBGetTables();

    $cat_id = Dizkus_userapi_get_forum_category(array('forum_id' => $forum_id));
    if (!allowedtowritetocategoryandforum($cat_id, $forum_id)) {
        return showforumerror(_DZK_NOAUTH_TOWRITE, __FILE__, __LINE__);
    }


    if (trim($message) == '' || trim($subject) == '') {
        // either message or subject is empty
        return showforumerror(_DZK_EMPTYMSG, __FILE__, __LINE__);
    }

    /*
    it's a submitted page and message and subject are not empty
    */

    //  grab message for notification
    //  without html-specialchars, bbcode, smilies <br /> and [addsig]
    $posted_message = stripslashes($message);

    //  anonymous user has uid=0, but needs pn_uid=1
    if (isset($post_as) && !empty($post_as) && is_numeric($post_as)) {
        $pn_uid = $post_as;
    } else {
        if (pnUserLoggedin()) {
            if ($attach_signature == 1) {
                $message .= '[addsig]';
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

    $time = (isset($time)) ? $time : date('Y-m-d H:i');

    // create topic
    $obj['topic_title']     = DataUtil::formatForStore($subject);
    $obj['topic_poster']    = DataUtil::formatForStore($pn_uid);
    $obj['forum_id']        = DataUtil::formatForStore($forum_id);
    $obj['topic_time']      = DataUtil::formatForStore($time);
    $obj['topic_reference'] = (isset($reference)) ? DataUtil::formatForStore($reference) : '';
    DBUtil::insertObject($obj, 'dizkus_topics', 'topic_id');
    
    // create posting
    $pobj['topic_id']   = DataUtil::formatForStore($obj['topic_id']);
    $pobj['forum_id']   = $obj['forum_id'];
    $pobj['poster_id']  = $obj['topic_poster'];
    $pobj['post_time']  = $obj['topic_time'];
    $pobj['poster_ip']  = DataUtil::formatForStore($poster_ip);
    $pobj['post_msgid'] = (isset($msgid)) ? DataUtil::formatForStore($msgid) : '';
    $pobj['post_text']  = DataUtil::formatForStore($message);
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
        if ($subscribe_topic == 1) {
            // user wants to subscribe the new topic
            Dizkus_userapi_subscribe_topic(array('topic_id' => $topic_id));
        }
    }

    //  update forums-table
    $fobj['forum_id']           = $obj['forum_id'];
    $fobj['forum_last_post_id'] = $pobj['post_id'];
    DBUtil::updateObject($fobj, 'dizkus_forums', null, 'forum_id');
    DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_posts',  $obj['forum_id'], 'forum_id');
    DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_topics', $obj['forum_id'], 'forum_id');

    //  notify for newtopic
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
 *@params $args['post_id'] int the postings id
 *@returns array with posting information...
 */
function Dizkus_userapi_readpost($args)
{
    list($dbconn, $pntable) = dzkOpenDB();

    // we know about the post_id, let's find out the forum and catgeory name for permission checks
    $sql = "SELECT p.post_id,
                    p.post_time,
                    p.post_text,
                    p.poster_id,
                    t.topic_id,
                    t.topic_title,
                    t.topic_replies,
                    f.forum_id,
                    f.forum_name,
                    c.cat_title,
                    c.cat_id
            FROM ".$pntable['dizkus_posts']." p
            LEFT JOIN ".$pntable['dizkus_topics']." t ON t.topic_id = p.topic_id
            LEFT JOIN ".$pntable['dizkus_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['dizkus_categories']." c ON c.cat_id = f.cat_id
            WHERE (p.post_id = '".(int)DataUtil::formatForStore($args['post_id'])."')";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_DZK_TOPIC_NOEXIST, __FILE__, __LINE__, '404 Not Found');
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    dzkCloseDB($result);

    $post = array();
    $post['post_id']      = DataUtil::formatForDisplay($myrow['post_id']);
    $post['post_time']    = DataUtil::formatForDisplay($myrow['post_time']);
    $message              = $myrow['post_text'];
    $post['has_signature']= (substr($message, -8, 8)=='[addsig]');
    $post['post_rawtext'] = Dizkus_replacesignature($message, '');
    $post['post_rawtext'] = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $post['post_rawtext']);
    $post['post_rawtext'] = eregi_replace('<br />', '', $post['post_rawtext']);

    $post['topic_id']     = DataUtil::formatForDisplay($myrow['topic_id']);
    $post['topic_rawsubject']= strip_tags($myrow['topic_title']);
    $post['topic_subject']= DataUtil::formatForDisplay($myrow['topic_title']);
    $post['topic_replies']= DataUtil::formatForDisplay($myrow['topic_replies']);
    $post['forum_id']     = DataUtil::formatForDisplay($myrow['forum_id']);
    $post['forum_name']   = DataUtil::formatForDisplay($myrow['forum_name']);
    $post['cat_title']    = DataUtil::formatForDisplay($myrow['cat_title']);
    $post['cat_id']       = DataUtil::formatForDisplay($myrow['cat_id']);
    $post['poster_data'] = Dizkus_userapi_get_userdata_from_id(array('userid' => $myrow['poster_id']));
    // create unix timestamp
    $post['post_unixtime'] = dzk_str2time($post['post_time']); //strtotime ($post['post_time']);
    $post['posted_unixtime'] = $post['post_unixtime'];

    if(!allowedtoreadcategoryandforum($post['cat_id'], $post['forum_id'])) {
        return showforumerror(getforumerror('auth_read',$post['forum_id'], 'forum', _DZK_NOAUTH_TOREAD), __FILE__, __LINE__);
    }

    $pn_uid = pnUserGetVar('uid');
    $post['moderate'] = false;
    if(allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        $post['moderate'] = true;
    }

    $post['poster_data']['edit'] = false;
    $post['poster_data']['reply'] = false;
    $post['poster_data']['seeip'] = false;
    $post['poster_data']['moderate'] = false;
////////// neu
    if ($post['poster_data']['pn_uid']==$pn_uid) {
        // user is allowed to moderate || own post
        $post['poster_data']['edit'] = true;
    }
    if(allowedtowritetocategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is allowed to reply
        $post['poster_data']['reply'] = true;
    }

    if(allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id']) &&
        pnModGetVar('Dizkus', 'log_ip') == 'yes') {
        // user is allowed to see ip
        $post['poster_data']['seeip'] = true;
    }
    if(allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is allowed to moderate
        $post['poster_data']['moderate'] = true;
        $post['poster_data']['edit'] = true;
    }
//////////// ende neu
    $post['post_textdisplay'] = phpbb_br2nl($message);
    $post['post_textdisplay'] = Dizkus_replacesignature($post['post_textdisplay'], $post['poster_data']['_SIGNATURE']);

    // call hooks for $message_display ($message remains untouched for the textarea)
    list($post['post_textdisplay']) = pnModCallHooks('item', 'transform', $post['post_id'], array($post['post_textdisplay']));
    $post['post_textdisplay'] = dzkVarPrepHTMLDisplay($post['post_textdisplay']);
/*
    //$message = DataUtil::formatForDisplay($message);
    //  remove [addsig]
    $message = eregi_replace("\[addsig]$", '', $message);
    //  remove <!-- editby -->
    $message = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $message);
    //  convert <br /> to \n (since nl2br only inserts additional <br /> we just need to remove them
    //$message = eregi_replace('<br />', '', $message);
    $message = phpbb_br2nl($message);
    //  convert bbcode (just for backwards compatibility)
    $message = Dizkus_bbdecode($message);
    //  convert autolinks (just for backwards compatibility)
    $message = Dizkus_undo_make_clickable($message);
    $post['post_text'] = $message;
*/
    $post['post_text'] = $post['post_textdisplay'];

    // allow to edit the subject if first post
    $post['first_post'] = Dizkus_userapi_is_first_post(array('topic_id' => $post['topic_id'], 'post_id' => $post['post_id']));

    return $post;
}

/**
 * Check if this is the first post in a topic.
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['post_id'] int the postings id
 *@returns boolean
 */
function Dizkus_userapi_is_first_post($args)
{
    // compare the given post_id with the lowest post_id in the topic
    $minpost = pnModAPIFunc('Dizkus', 'user', 'get_firstlast_post_in_topic', 
                             array('topic_id' => $args['topic_id'],
                                   'first'    => true,
                                   'id_only'  => true)); 
    $isfirst = ($minpost == $args['post_id']) ? true : false;
    return $isfirst;
}

/**
 * update post
 * updates a posting in the db after editing it
 *
 *@params $args['post_id'] int the postings id
 *@params $args['subject'] string the subject
 *@params $args['message'] string the text
 *@params $args['delete'] boolean true if the posting is to be deleted
 *@params $args['attach_signature'] boolean true if the addsig place holder has to be appended
 *@returns string url to redirect to after action (topic of forum if the (last) posting has been deleted)
 */
function Dizkus_userapi_updatepost($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    $sql = "SELECT p.poster_id,
                   p.post_time,
                   p.topic_id,
                   p.forum_id,
                   t.topic_title,
                   t.topic_status,
                   t.topic_last_post_id,
                   t.topic_replies,
                   f.cat_id,
                   f.forum_last_post_id
            FROM  ".$pntable['dizkus_posts']." as p,
                  ".$pntable['dizkus_topics']." as t,
                  ".$pntable['dizkus_forums']." as f
            WHERE (p.post_id = '".(int)DataUtil::formatForStore($post_id)."')
              AND (t.topic_id = p.topic_id)
              AND (f.forum_id = p.forum_id)";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if ($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_DZK_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    dzkCloseDB($result);
    extract($myrow);

    $pn_uid = pnUserGetVar('uid');

    if (!($pn_uid == $poster_id) &&
        !allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        // user is not allowed to edit post
        return showforumerror(getforumerror('auth_mod', $forum_id, 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    if (($topic_status == 1) &&
        !allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        // topic is locked, user is not moderator
        return showforumerror(getforumerror('auth_mod',$forum_id, 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    if (trim($message) == '') {
        // no message
        return showforumerror( _DZK_EMPTYMSG, __FILE__, __LINE__);
    }

    if (empty($delete)) {

        // update the posting
        if (!allowedtoadmincategoryandforum($cat_id, $forum_id)) {
            // if not admin then add a edited by line
            // If it's been edited more than once, there might be old "edited by" strings with
            // escaped HTML code in them. We want to fix this up right here:
            $message = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $message);
            // who is editing?
            if (pnUserLoggedIn()) {
                $editname = pnUserGetVar('uname');
            } else {
                $editname = pnModGetVar('Users', 'anonymous');
            }
            $edit_date = ml_ftime(_DATETIMEBRIEF, GetUserTime(time()));
            $message .= '<br /><br /><!-- editby --><br /><br /><em>' . _DZK_EDITBY . ' ' . $editname . ', ' . $edit_date . '</em><!-- end editby --> ';
        }

        // add signature placeholder
        if (($poster_id <> 1) && ($attach_signature==true)){
            $message .= '[addsig]';
        }
        $message = DataUtil::formatForStore($message);

        $sql = "UPDATE ".$pntable['dizkus_posts']."
                SET post_text = '$message'
                WHERE (post_id = '".(int)DataUtil::formatForStore($post_id)."')";

        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);

        if (!empty ($subject)) {
            //  topic has a new subject
            if (trim($subject) != '') {
                $subject = DataUtil::formatForStore($subject);
                $sql = "UPDATE ".$pntable['dizkus_topics']."
                        SET topic_title = '$subject'
                        WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";

                $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
                dzkCloseDB($result);
            }
        }

        // Let any hooks know that we have updated an item.
        //pnModCallHooks('item', 'update', $post_id, array('module' => 'Dizkus'));
        pnModCallHooks('item', 'update', $topic_id, array('module' => 'Dizkus',
                                                          'post_id' => $post_id));

        // update done, return now
        return pnModURL('Dizkus', 'user', 'viewtopic',
                        array('topic' => $topic_id /*,
                              'start' => $start*/) );

    } else {

        // we are going to delete this posting

        // delete the post from the posts table
        $sql = "DELETE FROM ".$pntable['dizkus_posts']."
                WHERE post_id = '".(int)DataUtil::formatForStore($post_id)."'";

        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
/*
        // delete the post from the posts_text table
//      $sql = "DELETE FROM ".$pntable['dizkus_posts_text']."
                WHERE post_id = '".(int)DataUtil::formatForStore($post_id)."'";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
*/
        // Let any hooks know that we have deleted an item.
        //pnModCallHooks('item', 'delete', $post_id, array('module' => 'Dizkus'));

        //
        // there are several possibilities now:
        // #1 we deleted the last posting in the thread, but their are still others.
        //    this means we have to update to topic_last_post_id
        // #2 we deleted the last posting but there is no other posting, this means
        //    we have to delete the whole topic too
        // #3 we deleted any other topic in the thread - this means no change at all is
        //    necessary
        //
        // option #1 and #3 mean we have to adjust the topic_replies counter (= -1) too
        // option #1 and #2 result in changes in the forums table too
        //
        // check if the deleted post_id is not the last one (#3)
        if ($post_id <> $topic_last_post_id) {

            // the deleted posting was not the last one
            // adjust the users post count
            Dizkus_userapi_update_user_post_count(array('user_id' => $poster_id, 'mode' => 'dec'));

            //
            // adjust the post counter in the forum, topic counter and last_post_id not changed
            //
            $sql = "UPDATE ".$pntable['dizkus_forums']."
                    SET forum_posts = forum_posts - 1
                    WHERE forum_id = '".(int)DataUtil::formatForStore($forum_id)."'";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            dzkCloseDB($result);

            //
            //  adjust the topic_replies
            //
            $sql = "UPDATE ".$pntable['dizkus_topics']."
                    SET topic_replies = topic_replies - 1
                    WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            dzkCloseDB($result);

            //
            // no more actions necessary, just return to the topic and show the last page
            // after removing the posting the $topic_replies now contains the total number
            // posts in this topic :-)
            // $topic_replies - one deleted post + one initial post
            // get some enviroment
            $posts_per_page = pnModGetVar('Dizkus', 'posts_per_page');
            $times = 0;
            if (($topic_replies - $posts_per_page) >=  0) {
                for ($x = 0; $x < $topic_replies - $posts_per_page; $x += $posts_per_page) {
                    $times++;
                }
            }
            $start = $times * $posts_per_page;
            return pnModURL('Dizkus', 'user', 'viewtopic',
                            array('topic' => $topic_id,
                                  'start' => $start));
        } else {
            //
            // check if this was the last post in the topic, if yes, remove topic
            //
            $last_topic_post = Dizkus_userapi_get_firstlast_post_in_topic(array('topic_id' => $topic_id));
            $forum_last_post_id = Dizkus_userapi_get_last_post_in_forum(array('forum_id' => $forum_id, 'id_only' => true));

            if ($last_topic_post == false) {
                //
                // it was the last post in the thread, remove topic
                //
                $sql = "DELETE FROM ".$pntable['dizkus_topics']."
                        WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";
                $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
                dzkCloseDB($result);

                //
                // adjust the users post counter
                //
                Dizkus_userapi_update_user_post_count(array('user_id' => $poster_id, 'mode' => 'dec'));

                //
                // adjust the post and topic counter and forum_last_post_id in the forum
                //
                $sql = "UPDATE ".$pntable['dizkus_forums']."
                        SET forum_topics = forum_topics - 1,
                            forum_posts = forum_posts - 1,
                            forum_last_post_id = '".(int)DataUtil::formatForStore($forum_last_post_id)."'
                        WHERE forum_id = '".(int)DataUtil::formatForStore($forum_id)."'";
                $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
                dzkCloseDB($result);

                // Let any hooks know that we have deleted an item (topic).
                pnModCallHooks('item', 'delete', $topic_id, array('module' => 'Dizkus'));

                //
                // ready to return
                //
                return pnModURL('Dizkus', 'user', 'viewforum',
                                array('forum' => $forum_id));
            } else {
                //
                // there is at least one posting in this topic
                // $post contains the data of the last posting
                //
                $lastposttime = date('Y-m-d H:i', $last_topic_post['post_unixtime']);
                $sql = "UPDATE ".$pntable['dizkus_topics']."
                        SET topic_time = '".DataUtil::formatForStore($lastposttime)."',
                            topic_last_post_id = '".(int)DataUtil::formatForStore($last_topic_post['post_id'])."',
                            topic_replies = topic_replies - 1
                        WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";
                $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
                dzkCloseDB($result);

                //
                // adjust the users post counter
                //
                Dizkus_userapi_update_user_post_count(array('user_id' => $poster_id, 'mode' => 'dec'));

                //
                // adjust the post counter in the forum, topic counter not changed
                //
                $sql = "UPDATE ".$pntable['dizkus_forums']."
                        SET forum_posts = forum_posts - 1,
                            forum_last_post_id = '".(int)DataUtil::formatForStore($forum_last_post_id)."'
                        WHERE forum_id = '".(int)DataUtil::formatForStore($forum_id)."'";
                $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
                dzkCloseDB($result);

                //
                // after removing the posting the $topic_replies now contains the total number
                // posts in this topic :-)
                // $topic_replies - one deleted post + one initial post
                // get some enviroment
                $posts_per_page = pnModGetVar('Dizkus', 'posts_per_page');
                $times = 0;
                if (($topic_replies-$posts_per_page) >= 0) {
                    for ($x = 0; $x < $topic_replies - $posts_per_page; $x += $posts_per_page) {
                        $times++;
                    }
                }
                $start = $times * $posts_per_page;
                return pnModURL('Dizkus', 'user', 'viewtopic',
                                array('topic' => $topic_id,
                                      'start' => $start));
            }
        }
    }

    // we should not get here, but who knows...
    return pnModURL('Dizkus', 'user', 'main');
}

/**
 * get_viewip_data
 *
 *@params $args['post_id] int the postings id
 *@returns array with informstion ...
 */
function Dizkus_userapi_get_viewip_data($args)
{
    list($dbconn, $pntable) = dzkOpenDB();

    $viewip = array();

    $sql = "SELECT u.pn_uname, p.poster_ip
            FROM ".$pntable['users']." u, ".$pntable['dizkus_posts']." p
            WHERE p.post_id = '".(int)DataUtil::formatForStore($args['post_id'])."'
            AND u.pn_uid = p.poster_id";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    if($result->EOF) {
        // TODO we have a valid user here, but he doesn't have posts
        return showforumerror(_DZK_NOUSER_OR_POST, __FILE__, __LINE__);
    } else {
        $row = $result->GetRowAssoc(false);
    }
    dzkCloseDB($result);
    $viewip['poster_ip']   = $row['poster_ip'];
    $viewip['poster_host'] = gethostbyaddr($row['poster_ip']);

    $sql = "SELECT pn_uid, pn_uname, count(*) AS postcount
            FROM ".$pntable['dizkus_posts']." p, ".$pntable['users']." u
            WHERE poster_ip='".DataUtil::formatForStore($viewip['poster_ip'])."' && p.poster_id = u.pn_uid
            GROUP BY pn_uid";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    $viewip['users'] = array();
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $user = array();
        $user['pn_uid']    = $row['pn_uid'];
        $user['pn_uname']  = $row['pn_uname'];
        $user['postcount'] = $row['postcount'];
        array_push($viewip['users'], $user);
        $result->MoveNext();
    }
    dzkCloseDB($result);
    return $viewip;
}

/**
 * lockunlocktopic
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['mode']     string lock or unlock
 *@returns void
 */
function Dizkus_userapi_lockunlocktopic($args)
{
    list($dbconn, $pntable) = dzkOpenDB();

    $new_status = ($args['mode']=='lock') ? 1 : 0;

    $sql = "UPDATE ".$pntable['dizkus_topics']."
            SET topic_status = $new_status
            WHERE topic_id = '".(int)DataUtil::formatForStore($args['topic_id'])."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);
    return;
}

/**
 * stickyunstickytopic
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['mode']     string sticky or unsticky
 *@returns void
 */
function Dizkus_userapi_stickyunstickytopic($args)
{
    list($dbconn, $pntable) = dzkOpenDB();

    $new_sticky = ($args['mode']=='sticky') ? 1 : 0;

    $sql = "UPDATE ".$pntable['dizkus_topics']."
            SET sticky = '$new_sticky'
            WHERE topic_id = '".(int)DataUtil::formatForStore($args['topic_id'])."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);
    return;
}

/**
 * get_forumid_and categoryid_from_topicid
 * used for permission checks
 *
 *@params $args['topic_id'] int the topics id
 *@returns array(forum_id, category_id)
 */
function Dizkus_userapi_get_forumid_and_categoryid_from_topicid($args)
{
    list($dbconn, $pntable) = dzkOpenDB();

    // we know about the topic_id, let's find out the forum and catgeory name for permission checks
    $sql = "SELECT f.forum_id,
                   c.cat_id
            FROM  ".$pntable['dizkus_topics']." t
            LEFT JOIN ".$pntable['dizkus_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['dizkus_categories']." AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)DataUtil::formatForStore($args['topic_id'])."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_DZK_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    dzkCloseDB($result);

    $forum_id = DataUtil::formatForDisplay($myrow['forum_id']);
    $cat_id = DataUtil::formatForDisplay($myrow['cat_id']);

    return array( $forum_id, $cat_id);
}

/**
 * readuserforums
 * reads all forums the recent users is allowed to see
 *
 *@params $args['cat_id'] int a category id (optional, if set, only reads the forums in this category)
 *@params $args['forum_id'] int a forums id (optional, if set, only reads this category
 *@returns array of forums, maybe empty
 */
function Dizkus_userapi_readuserforums($args)
{
    extract($args);
    unset($args);

    if(!empty($cat_id) && !empty($forum_id)) {
        if(!allowedtoseecategoryandforum($cat_id, $forum_id)) {
            return showforumerror(getforumerror('auth_overview',$forum_id, 'forum', _DZK_NOAUTH_TOSEE), __FILE__, __LINE__);
        }
    }

    list($dbconn, $pntable) = dzkOpenDB();

    $where = '';
    if(isset($forum_id)) {
        $where = 'WHERE f.forum_id=' . DataUtil::formatForStore($forum_id) . ' ';
    } elseif (isset($cat_id)) {
        $where = 'WHERE c.cat_id=' . DataUtil::formatForStore($cat_id) . ' ';
    }
    $sql = "SELECT f.forum_name,
                   f.forum_id,
                   f.forum_desc,
                   f.forum_order,
                   f.forum_topics,
                   f.forum_posts,
                   f.forum_pop3_active,
                   f.forum_pop3_server,
                   f.forum_pop3_port,
                   f.forum_pop3_login,
                   f.forum_pop3_password,
                   f.forum_pop3_interval,
                   f.forum_pop3_lastconnect,
                   f.forum_pop3_matchstring,
                   f.forum_pop3_pnuser,
                   f.forum_pop3_pnpassword,
                   f.forum_moduleref,
                   f.forum_pntopic,
                   c.cat_title,
                   c.cat_id
            FROM ".$pntable['dizkus_forums']." AS f
            LEFT JOIN ".$pntable['dizkus_categories']." AS c
            ON c.cat_id=f.cat_id
            $where
            ORDER BY c.cat_order, f.forum_order";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $forums = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $forum = array();
            list( $forum['forum_name'],
                  $forum['forum_id'],
                  $forum['forum_desc'],
                  $forum['forum_order'],
                  $forum['forum_topics'],
                  $forum['forum_posts'],
                  $forum['pop3_active'],
                  $forum['pop3_server'],
                  $forum['pop3_port'],
                  $forum['pop3_login'],
                  $forum['pop3_password'],
                  $forum['pop3_interval'],
                  $forum['pop3_lastconnect'],
                  $forum['pop3_matchstring'],
                  $forum['pop3_pnuser'],
                  $forum['pop3_pnpassword'],
                  $forum['forum_moduleref'],
                  $forum['forum_pntopic'],
                  $forum['cat_title'],
                  $forum['cat_id'] ) = $result->fields;
            if(allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
                array_push( $forums, $forum );
            }
        }
    }
    dzkCloseDB($result);
    if(isset($forum_id)) {
        return $forums[0];
    }
    return $forums;
}

/**
 * movetopic
 * moves a topic to another forum
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['forum_id'] int the destination forums id
 *@params $args['shadow']   boolean true = create shadow topic
 *@returns void
 */
function Dizkus_userapi_movetopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    // get the old forum id and old post date
    $sql = "SELECT t.forum_id,
                   t.topic_time,
                   t.topic_title
            FROM  ".$pntable['dizkus_topics']." t
            WHERE t.topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    if ($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_DZK_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    dzkCloseDB($result);

    $oldforum_id  = DataUtil::formatForDisplay($myrow['forum_id']);
    $topic_time   = $myrow['topic_time'];
    $topic_title  = $myrow['topic_title'];

    if ($oldforum_id <> $forum_id) {
        // set new forum id
        $sql = "UPDATE $pntable[dizkus_topics]
                SET forum_id = '".(int)DataUtil::formatForStore($forum_id)."'
                WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);

        $sql = "UPDATE ".$pntable['dizkus_posts']."
                SET forum_id = '".(int)DataUtil::formatForStore($forum_id)."'
                WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);

        if ($shadow == true) {
            // user wants to have a shadow topic
            $message = sprintf(_DZK_SHADOWTOPIC_MESSAGE, pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)) );
            $subject = '***' . DataUtil::formatForDisplay(_DZK_MOVED_SUBJECT) . ': ' . $topic_title;

            Dizkus_userapi_storenewtopic(array('subject'  => $subject,
                                                'message'  => $message,
                                                'forum_id' => $oldforum_id,
                                                'time'     => $topic_time,
                                                'no_sig'   => true));
        }
        pnModAPIFunc('Dizkus', 'admin', 'sync', array('id' => $forum_id, 'type' => 'forum'));
        pnModAPIFunc('Dizkus', 'admin', 'sync', array('id' => $oldforum_id, 'type' => 'forum'));
    }

    return;
}

/**
 * deletetopic
 *
 *@params $args['topic_id'] int the topics id
 *@returns int the forums id for redirecting
 */
function Dizkus_userapi_deletetopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    // get the forum id
    $sql = "SELECT t.forum_id
            FROM  ".$pntable['dizkus_topics']." t
            WHERE t.topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    if ($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_DZK_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    dzkCloseDB($result);

    $forum_id = DataUtil::formatForDisplay($myrow['forum_id']);

    // Update the users's post count, this might be slow on big topics but it makes other parts of the
    // forum faster so we win out in the long run.
    $sql = "SELECT poster_id, post_id
            FROM ".$pntable['dizkus_posts']."
            WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        if ($row['poster_id'] != -1) {
            $sql2 = "UPDATE ".$pntable['dizkus_users']."
                     SET user_posts = user_posts - 1
                     WHERE user_id = '".DataUtil::formatForStore($row['poster_id'])."'";
            $result2 = dzkExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
            dzkCloseDB($result2);
        }
        // collect the post ID's we have to remove.
        $posts_to_remove[] = $row['post_id'];
        $result->MoveNext();
    }
    dzkCloseDB($result);

    $sql = "DELETE FROM ".$pntable['dizkus_posts']."
            WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    $sql = "DELETE FROM ".$pntable['dizkus_topics']."
            WHERE topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // fix for bug [#3753] SQL Error in Dizkus_userapi_deletetopic
    // credits to rmaiwald for te fix
/*
    if (count($posts_to_remove)>0) {
//      $sql = "DELETE FROM ".$pntable['dizkus_posts_text']
             . " WHERE post_id IN (" . implode(",",$posts_to_remove) . ")";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
    }
*/
    // bug [#2491] removal of topics doesn't remove the subscriptions
    $sql = "DELETE FROM ".$pntable['dizkus_topic_subscription']."
            WHERE topic_id = '" . (int)DataUtil::formatForStore($topic_id) . "'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // Let any hooks know that we have deleted an item (topic).
    pnModCallHooks('item', 'delete', $topic_id, array('module' => 'Dizkus'));

    pnModAPIFunc('Dizkus', 'admin', 'sync', array('id' => $forum_id, 'type' => 'forum'));
    return $forum_id;

}

/**
 * Sending notify e-mail to users subscribed to the topic of the forum
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['poster_id'] int the users pn_uid
 *@params $args['post_message'] string the text
 *@params $args['type'] int, 0=new message, 2=reply
 *@returns void
 */
function Dizkus_userapi_notify_by_email($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    setlocale (LC_TIME, pnConfigGetVar('locale'));
    $modinfo = pnModGetInfo(pnModGetIDFromName(pnModGetName()));

    // generate the mailheader info
    $email_from = pnModGetVar('Dizkus', 'email_from');
    if ($email_from == '') {
        // nothing in forumwide-settings, use PN adminmail
        $email_from = pnConfigGetVar('adminmail');
    }

    // normal notification
    $sql = "SELECT t.topic_title,
                   t.topic_poster,
                   t.topic_time,
                   f.cat_id,
                   c.cat_title,
                   f.forum_name,
                   f.forum_id
            FROM  ".$pntable['dizkus_topics']." t
            LEFT JOIN ".$pntable['dizkus_forums']." f ON t.forum_id = f.forum_id
            LEFT JOIN ".$pntable['dizkus_categories']." c ON f.cat_id = c.cat_id
            WHERE t.topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if ($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_DZK_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    dzkCloseDB($result);

    $topic_unixtime= dzk_str2time($myrow['topic_time']); //strtotime ($myrow['topic_time']);
    $topic_time_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($topic_unixtime));

    $poster_name = pnUserGetVar('uname',$poster_id);

    $forum_id = DataUtil::formatForDisplay($myrow['forum_id']);
    $forum_name = DataUtil::formatForDisplay($myrow['forum_name']);
    $category_name = DataUtil::formatForDisplay($myrow['cat_title']);
    $topic_subject = DataUtil::formatForDisplay($myrow['topic_title']);

    if ($type == 0) {
        // New message
        $subject= '';
    } elseif ($type == 2) {
        // Reply
        $subject= 'Re: ';
    }
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
    $sql = "SELECT DISTINCT fs.user_id,
                            c.cat_id
            FROM " . $pntable['dizkus_subscription'] . " as fs,
                 " . $pntable['dizkus_forums'] . " as f,
                 " . $pntable['dizkus_categories'] . " as c
            WHERE fs.forum_id=".DataUtil::formatForStore($forum_id)."
              " . $fs_wherenotuser . "
              AND f.forum_id = fs.forum_id
              AND c.cat_id = f.cat_id";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $recipients = array();
    // check if list is empty - then do nothing
    // we create an array of recipients here
    if ($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            list($pn_uid, $cat_id) = $result->fields;
            $pn_email = pnUserGetVar('email', $pn_uid);
            if (empty($pn_email)) { continue; }
            // check permissions
            if (SecurityUtil::checkPermission('Dizkus::', $cat_id . ':' . $forum_id . ':', ACCESS_READ, $pn_uid)) {
                $pn_name  = pnUserGetVar('name', $pn_uid);
                $email['name'] = (!empty($pn_name)) ? $pn_name : pnUserGetVar('uname', $pn_uid);
                $email['address'] = $pn_email;
                $email['uid'] = $pn_uid;
                $recipients[$email['name']] = $email;
            }
        }
    }
    dzkCloseDB($result);

    //  get list of topic_subscribers with non-empty emails
    $sql = "SELECT DISTINCT ts.user_id,
                            c.cat_id,
                            f.forum_id
            FROM " . $pntable['dizkus_topic_subscription'] . " as ts,
                 " . $pntable['dizkus_forums'] . " as f,
                 " . $pntable['dizkus_categories'] . " as c
            WHERE ts.topic_id=".DataUtil::formatForStore($topic_id)."
              " . $ts_wherenotuser . "
              AND f.forum_id = ts.forum_id
              AND c.cat_id = f.cat_id";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if ($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            list($pn_uid, $cat_id, $forum_id) = $result->fields;
            $pn_email = pnUserGetVar('email', $pn_uid);
            if (empty($pn_email)) { continue; }
            // check permissions
            if (SecurityUtil::checkPermission('Dizkus::', $cat_id . ':' . $forum_id . ':', ACCESS_READ, $pn_uid)) {
                $pn_name  = pnUserGetVar('name', $pn_uid);
                $email['name'] = (!empty($pn_name)) ? $pn_name : pnUserGetVar('uname', $pn_uid);
                $email['address'] = $pn_email;
                $email['uid'] = $pn_uid;
                $recipients[$email['name']] = $email;
            }
        }
    }
    dzkCloseDB($result);

    $sitename = pnConfigGetVar('sitename');

    $message = _DZK_NOTIFYBODY1 . ' '. $sitename . "\n"
            . $category_name . ' :: ' . $forum_name . ' :: '. $topic_subject . "\n\n"
            . $poster_name . ' ' .DataUtil::formatForDisplay(_DZK_NOTIFYBODY2) . ' ' . $topic_time_ml . "\n"
            . "---------------------------------------------------------------------\n\n"
            . strip_tags($post_message) . "\n"
            . "---------------------------------------------------------------------\n\n"
            . _DZK_NOTIFYBODY3 . "\n"
            . pnModURL('Dizkus', 'user', 'reply', array('topic' => $topic_id, 'forum' => $forum_id), null, null, true) . "\n\n"
            . _DZK_NOTIFYBODY4 . "\n"
            . pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id), null, null, true) . "\n\n"
            . _DZK_NOTIFYBODY6 . "\n"
            . pnModURL('Dizkus', 'user', 'prefs', array(), null, null, true) . "\n"
            . "\n"
            . _DZK_NOTIFYBODY5 . ' ' . pnGetBaseURL();

    if (count($recipients) > 0) {
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
                                                      'X-DizkusTopicID: ' . $topic_id));
    
                pnModAPIFunc('Mailer', 'user', 'sendmessage', $args);
            }
        }
    }
    return;
}

/**
 * get_topic_subscriptions
 *
 *@params none
 *@params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 *@returns array with topic ids, may be empty
 */
function Dizkus_userapi_get_topic_subscriptions($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    if (isset($user_id)) {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return showforumerror(_DZK_NOAUTH);
        }
    } else {
        $user_id = pnUserGetVar('uid');
    }

    $tstable = $pntable['dizkus_topic_subscription'];
    $tscolumn = $pntable['dizkus_topic_subscription_column'];
    $topicstable = $pntable['dizkus_topics'];
    $topicscolumn = $pntable['dizkus_topics_column'];
    $forumstable = $pntable['dizkus_forums'];
    $forumscolumn = $pntable['dizkus_forums_column'];
    $userstable = $pntable['users'];
    $userscolumn = $pntable['users_column'];

    // read the topic ids
    $sql = "SELECT ts.topic_id,
                   ts.forum_id,
                   t.topic_title,
                   t.topic_poster,
                   t.topic_time,
                   t.topic_replies,
                   t.topic_last_post_id,
                   u.pn_uname,
                   f.forum_name
            FROM $tstable AS ts,
                 $topicstable AS t,
                 $userstable AS u,
                 $forumstable AS f
            WHERE (ts.user_id='".(int)DataUtil::formatForStore($user_id)."'
              AND t.topic_id=ts.topic_id
              AND u.pn_uid=ts.user_id
              AND f.forum_id=ts.forum_id)
            ORDER BY ts.forum_id, ts.topic_id";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $subscriptions = array();
    $post_sort_order = pnModAPIFunc('Dizkus', 'user', 'get_user_post_order', array('user_id' => $user_id));
    $posts_per_page  = pnModGetVar('Dizkus', 'posts_per_page');

    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $subscription = array('topic_id'           => $row['topic_id'],
                              'forum_id'           => $row['forum_id'],
                              'topic_title'        => $row['topic_title'],
                              'topic_poster'       => $row['topic_poster'],
                              'topic_time'         => $row['topic_time'],
                              'topic_replies'      => $row['topic_replies'],
                              'topic_last_post_id' => $row['topic_last_post_id'],
                              'poster_name'        => $row['pn_uname'],
                              'forum_name'         => $row['forum_name']);
        if ($post_sort_order == 'ASC') {
            $start = ((ceil(($subscription['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page);
        } else {
            // latest topic is on top anyway...
            $start = 0;
        }
        // we now create the url to the last post in the thread. This might
        // on site 1, 2 or what ever in the thread, depending on topic_replies
        // count and the posts_per_page setting
        $subscription['last_post_url'] = DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewtopic',
                                                                             array('topic' => $subscription['topic_id'],
                                                                                   'start' => $start)));
        $subscription['last_post_url_anchor'] = $subscription['last_post_url'] . '#pid' . $subscription['topic_last_post_id'];

        array_push($subscriptions, $subscription);
        $result->MoveNext();
    }

    dzkCloseDB($result);

    return $subscriptions;
}

/**
 * subscribe_topic
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 *@returns void
 */
function Dizkus_userapi_subscribe_topic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    if (isset($user_id) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    } else {
        $user_id = pnUserGetVar('uid');
    }

    list($forum_id, $cat_id) = Dizkus_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id'=>$topic_id));
    if (!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        return showforumerror(getforumerror('auth_read',$forum_id, 'forum', _DZK_NOAUTH_TOREAD), __FILE__, __LINE__);
    }

    if (Dizkus_userapi_get_topic_subscription_status(array('userid' => $user_id, 'topic_id' => $topic_id)) == false) {
        // add user only if not already subscribed to the topic
        $sql = "INSERT INTO ".$pntable['dizkus_topic_subscription']." (user_id, forum_id, topic_id)
                VALUES ('".(int)DataUtil::formatForStore($user_id)."','".(int)DataUtil::formatForStore($forum_id)."','".(int)DataUtil::formatForStore($topic_id)."')";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
    }
    return;
}

/**
 * unsubscribe_topic
 *
 *@params $args['topic_id'] int the topics id, if not set we unsubscribe all topics
 *@params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 *@params $args['silent'] bool true=no error message when not subscribed, simply return void (obsolete)
 *@returns void
 */
function Dizkus_userapi_unsubscribe_topic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    $tsubtable  = $pntable['dizkus_topic_subscription'];
    $tsubcolumn = $pntable['dizkus_topic_subscription_column'];

    if (isset($user_id)) {
        if(!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    } else {
        $user_id = pnUserGetVar('uid');
    }

    $wheretopic = '';
    if (!empty($topic_id)) {
        $wheretopic = ' AND ' . $tsubcolumn['topic_id'] . '=' . (int)DataUtil::formatForStore($topic_id);
    }

    $sql = 'DELETE FROM ' .$tsubtable . '
            WHERE ' . $tsubcolumn['user_id'] . '=' . (int)DataUtil::formatForStore($user_id) .
            $wheretopic;

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    return;
}

/**
 * subscribe_forum
 *
 *@params $args['forum_id'] int the forums id
 *@params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 *@returns void
 */
function Dizkus_userapi_subscribe_forum($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();


    if (isset($user_id) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    } else {
        $user_id = pnUserGetVar('uid');
    }

    $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                          array('forum_id' => $forum_id));
    if (!allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(getforumerror('auth_read',$forum['forum_id'], 'forum', _DZK_NOAUTH_TOREAD), __FILE__, __LINE__);
    }

    if (Dizkus_userapi_get_forum_subscription_status(array('userid' => $user_id, 'forum_id' => $forum_id)) == false) {
        // add user only if not already subscribed to the forum
        $sql = "INSERT INTO ".$pntable['dizkus_subscription']." (user_id, forum_id)
                VALUES ('".(int)DataUtil::formatForStore($user_id)."','".(int)DataUtil::formatForStore($forum_id)."')";

        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
    }

    return;
}

/**
 * unsubscribe_forum
 *
 *@params $args['forum_id'] int the forums id, if empty then we unsubscribe all forums
 *@params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 *@returns void
 */
function Dizkus_userapi_unsubscribe_forum($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    $fsubtable  = $pntable['dizkus_subscription'];
    $fsubcolumn = $pntable['dizkus_subscription_column'];

    if (isset($user_id)) {
        if(!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    } else {
        $user_id = pnUserGetVar('uid');
    }

    $whereforum = '';
    if (!empty($forum_id)) {
        $whereforum = ' AND ' . $fsubcolumn['forum_id'] . '=' . (int)DataUtil::formatForStore($forum_id);
    }

    $sql = 'DELETE FROM ' . $fsubtable . '
            WHERE ' . $fsubcolumn['user_id'] . '=' . (int)DataUtil::formatForStore($user_id) .
            $whereforum;

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    return;
}

/**
 * add_favorite_forum
 *
 *@params $args['forum_id'] int the forums id
 *@params $args['user_id'] int - Optional - the user id
 *@returns void
 */
function Dizkus_userapi_add_favorite_forum($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    if (!isset($user_id)) {
        $user_id = (int)pnUserGetVar('uid');
    }

    $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                          array('forum_id' => $forum_id));

    if (!allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(getforumerror('auth_read', $forum['forum_id'], 'forum', _DZK_NOAUTH_TOREAD), __FILE__, __LINE__);
    }

    if (Dizkus_userapi_get_forum_favorites_status(array('userid' => $user_id, 'forum_id' => $forum_id)) == false) {
        // add user only if not already a favorite
        $sql = "INSERT INTO ".$pntable['dizkus_forum_favorites']." (user_id, forum_id)
                VALUES ('".(int)DataUtil::formatForStore($user_id)."','".(int)DataUtil::formatForStore($forum_id)."')";

        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
    }
    return;
}

/**
 * remove_favorite_forum
 *
 *@params $args['forum_id'] int the forums id
 *@params $args['user_id'] int - Optional - the user id
 *@returns void
 */
function Dizkus_userapi_remove_favorite_forum($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    if (!isset($user_id)) {
        $user_id = (int)pnUserGetVar('uid');
    }

    if (Dizkus_userapi_get_forum_favorites_status(array('userid' => $user_id, 'forum_id' => $forum_id)) == true) {
        // remove from favorites
        $sql = "DELETE FROM ".$pntable['dizkus_forum_favorites']."
                WHERE user_id='".(int)DataUtil::formatForStore($user_id)."'
                AND forum_id='".(int)DataUtil::formatForStore($forum_id)."'";

        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
    }

    return;
}

/**
 * prepareemailtopic
 * prepares data for sending a "look at this topic" mail.
 *
 *@params $args['topic_id'] int the topics id
 *returns array with topic information
 */
function Dizkus_userapi_prepareemailtopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    $sql = "SELECT t.topic_title,
                   t.topic_id,
                   t.forum_id,
                   f.forum_name,
                   f.cat_id,
                   c.cat_title
            FROM  ".$pntable['dizkus_topics']." t
            LEFT JOIN ".$pntable['dizkus_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['dizkus_categories']." AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)DataUtil::formatForStore($topic_id)."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if ($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_DZK_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    dzkCloseDB($result);

    $topic['topic_id'] = DataUtil::formatForDisplay($myrow['topic_id']);
    $topic['forum_name'] = DataUtil::formatForDisplay($myrow['forum_name']);
    $topic['cat_title'] = DataUtil::formatForDisplay($myrow['cat_title']);
    $topic['forum_id'] = DataUtil::formatForDisplay($myrow['forum_id']);
    $topic['cat_id'] = DataUtil::formatForDisplay($myrow['cat_id']);
    $topic['topic_subject'] = DataUtil::formatForDisplay($myrow['topic_title']);

    /**
     * base security check
     */
    if (!allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id'])) {
        return showforumerror(getforumerror('auth_read',$topic['forum_id'], 'forum', _DZK_NOAUTH_TOREAD), __FILE__, __LINE__);
    }
    return $topic;
}

/**
 * emailtopic
 *
 *@params $args['sendto_email'] string the recipients email address
 *@params $args['message'] string the text
 *@params $args['subject'] string the subject
 *@returns void
 */
function Dizkus_userapi_emailtopic($args)
{
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

    pnModAPIFunc('Mailer', 'user', 'sendmessage', $args2);
    return;
}

/**
 * get_latest_posts
 *
 *@params $args['selorder'] int 1-6, see below
 *@params $args['nohours'] int posting within these hours
 *@params $args['unanswered'] int 0 or 1(= postings with no answers)
 *@params $args['last_visit'] string the users last visit data
 *@params $args['last_visit_unix'] string the users last visit data as unix timestamp
 *@params $args['limit'] int limits the numbers hits read (per list), defaults and limited to 250
 *@returns array (postings, mail2forumpostings, rsspostings, text_to_display)
 */
function Dizkus_userapi_get_latest_posts($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    // init some arrays
    $posts = array();
    $m2fposts = array();
    $rssposts = array();

    if (!isset($limit) || empty($limit) || ($limit < 0) || ($limit > 100)) {
        $limit = 100;
    }

    $dizkusvars = pnModGetVar('Dizkus');
    $posts_per_page  = $dizkusvars['posts_per_page'];
    $post_sort_order = $dizkusvars['post_sort_order'];
    $hot_threshold   = $dizkusvars['hot_threshold'];

    if ($unanswered == 1) {
        $unanswered = "AND t.topic_replies = '0' ORDER BY t.topic_time DESC";
    } else {
        $unanswered = 'ORDER BY t.topic_time DESC';
    }

    // sql part per selected time frame

    switch ($selorder)
    {
        case '2' : // today
                   $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) = 0 ";
                   $text = _DZK_TODAY;
                   break;
        case '3' : // yesterday
                   $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) = 1 ";
                   $text = _DZK_YESTERDAY;
                   break;
        case '4' : // lastweek
                   $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) < 8 ";
                   $text= _DZK_LASTWEEK;
                   break;
        case '5' : // last x hours
                   $wheretime  = " AND t.topic_time > DATE_SUB(NOW(), INTERVAL " . DataUtil::formatForStore($nohours) . " HOUR) ";
                   $text = _DZK_LAST . ' ' . $nohours . ' ' . _DZK_HOURS;
                   break;
        case '6' : // last visit
                   $wheretime = " AND t.topic_time > '" . DataUtil::formatForStore($last_visit) . "' ";
                   $text = _DZK_LASTVISIT . ' ' . ml_ftime(_DATETIMEBRIEF, $last_visit_unix);
                   break;
        case '7' : // last x posts
                   $wheretime = "";
                   $limit = $amount-1;
                   $text = _DZK_RECENT_POSTS . ' ' . $amount;
                   break;
        case '1' :
        default:   // last 24 hours
                   $wheretime = " AND t.topic_time > DATE_SUB(NOW(), INTERVAL 1 DAY) ";
                   $text  =_DZK_LAST24;
                   break;
    }

    // get all forums the user is allowed to read
    $userforums = pnModAPIFunc('Dizkus', 'user', 'readuserforums');
    if (!is_array($userforums) || count($userforums) == 0) {
        // error or user is not allowed to read any forum at all
        // return empty result set without even doing a db access
        return array($posts, $m2fposts, $rssposts, $text);
    }

    // now create a very simle array of forum_ids only. we do not need
    // all the other stuff in the $userforums array entries
    $allowedforums = array();
    for ($i = 0; $i < count($userforums); $i++) {
        array_push($allowedforums, $userforums[$i]['forum_id']);
    }
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

    // build the tricky sql
    $sql = "SELECT    t.topic_id,
                      t.topic_title,
                      t.topic_replies,
                      t.topic_last_post_id,
                      t.sticky,
                      t.topic_status,
                      f.forum_id,
                      f.forum_name,
                      f.forum_pop3_active,
                      c.cat_id,
                      c.cat_title,
                      p.post_time,
                      p.poster_id
          FROM        ".$pntable['dizkus_topics']." AS t,
                      ".$pntable['dizkus_forums']." AS f,
                      ".$pntable['dizkus_categories']." AS c,
                      ".$pntable['dizkus_posts']." AS p
          WHERE f.forum_id = t.forum_id
            AND c.cat_id = f.cat_id
            AND p.post_id = t.topic_last_post_id
            AND " .
            $whereforum .
            $wheretime .
            $whereignorelist .
            $unanswered;

//pnfdebug('sql', $sql, true);
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $limit_reached = false;
    while ((list($topic_id,
                 $topic_title,
                 $topic_replies,
                 $topic_last_post_id,
                 $sticky,
                 $topic_status,
                 $forum_id,
                 $forum_name,
                 $pop3_active,
                 $cat_id,
                 $cat_title,
                 $post_time,
                 $poster_id) = $result->fields) && !$limit_reached ) {

        $post = array();
        $post['topic_id'] = DataUtil::formatForDisplay($topic_id);
        $post['topic_title'] = DataUtil::formatForDisplay($topic_title);
        $post['forum_id'] = DataUtil::formatForDisplay($forum_id);
        $post['forum_name'] = DataUtil::formatForDisplay($forum_name);
        $post['cat_id'] = DataUtil::formatForDisplay($cat_id);
        $post['cat_title'] = DataUtil::formatForDisplay($cat_title);
        $post['topic_replies'] = DataUtil::formatForDisplay($topic_replies);
        $post['topic_last_post_id'] = DataUtil::formatForDisplay($topic_last_post_id);
        $post['sticky'] = DataUtil::formatForDisplay($sticky);
        $post['topic_status'] = DataUtil::formatForDisplay($topic_status);
        $post['post_time'] = DataUtil::formatForDisplay($post_time);
        $post['poster_id'] = DataUtil::formatForDisplay($poster_id);

        // does this topic have enough postings to be hot?
        $post['hot_topic'] = ($post['topic_replies'] >= $hot_threshold) ? true : false;

        // get correct page for latest entry
        if ($post_sort_order == 'ASC') {
            $hc_dlink_times = 0;
            if (($topic_replies + 1 - $posts_per_page) >= 0) {
                $hc_dlink_times = 0;
                for ($x = 0; $x < $topic_replies + 1 - $posts_per_page; $x += $posts_per_page) {
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
        $post['post_time'] = ml_ftime(_DATETIMEBRIEF, GetUserTime($post['posted_unixtime']));

        $post['last_post_url'] = DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewtopic',
                                                     array('topic' => $post['topic_id'],
                                                           'start' => (ceil(($post['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page)));

        $post['last_post_url_anchor'] = $post['last_post_url'] . '#pid' . $post['topic_last_post_id'];

        switch ((int)$pop3_active)
        {
            case 1: // mail2forum
                array_push($m2fposts, $post);
                $limit_reached = count($m2fposts) > $limit;
                break;
            case 2:
                array_push($rssposts, $post);
                $limit_reached = count($rssposts) > $limit;
                break;
            case 0: // normal posting
            default:
                array_push($posts, $post);
                $limit_reached = count($posts) > $limit;
        }
        $result->MoveNext();
    }
    dzkCloseDB($result);

    return array($posts, $m2fposts, $rssposts, $text);
}

/**
 * usersync
 * stub function for syncing new pn users to Dizkus
 *
 *@params none
 *@returns void
 */
function Dizkus_userapi_usersync()
{
    pnModAPIFunc('Dizkus', 'admin', 'sync',
                 array('type' => 'all users'));

    return;
}

/**
 * splittopic
 *
 *@params $args['post'] array with posting data as returned from readpost()
 *@returns int id of the new topic
 */
function Dizkus_userapi_splittopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    // before we do anything we will read the topic_last_post_id because we will need
    // this one later (it will become the topic_last_post_id of the new thread)
    $sql = "SELECT topic_last_post_id,
                   topic_replies
            FROM ".$pntable['dizkus_topics']."
            WHERE topic_id = '".$post['topic_id']."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    list($old_last_post_id, $old_replies) = $result->fields;
    dzkCloseDB($result);

    $time = date('Y-m-d H:i');

    //  insert values into topics-table
    $topic_id = $dbconn->GenID($pntable['dizkus_topics']);
    $sql = "INSERT INTO ".$pntable['dizkus_topics']."
            (topic_id,
             topic_title,
             topic_poster,
             forum_id,
             topic_time)
            VALUES
            ('".DataUtil::formatForStore($topic_id)."',
             '".DataUtil::formatForStore($post['topic_subject'])."',
             '".DataUtil::formatForStore($post['poster_data']['pn_uid'])."',
             '".DataUtil::formatForStore($post['forum_id'])."',
             '".DataUtil::formatForStore($time)."')";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    $newtopic_id = $dbconn->PO_Insert_ID($pntable['dizkus_topics'], 'topic_id');
    dzkCloseDB($result);

    // now we need to change the postings:
    // first step: count the number of posting we have to move
    $sql = "SELECT COUNT(*) AS total
            FROM ".$pntable['dizkus_posts']."
            WHERE topic_id = '".(int)DataUtil::formatForStore($post['topic_id'])."'
              AND post_id >= '".(int)DataUtil::formatForStore($post['post_id'])."'";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    list($posts_to_move) = $result->fields;
    dzkCloseDB($result);

    // update the topic_id in the postings
    // starting with $post['post_id'] and then all post_id's where topic_id = $post['topic_id'] and
    // post_id > $post['post_id']
    $sql = "UPDATE ".$pntable['dizkus_posts']."
            SET topic_id = '" . DataUtil::formatForStore($newtopic_id) . "'
            WHERE post_id >= '".(int)DataUtil::formatForStore($post['post_id'])."'
            AND topic_id = '".$post['topic_id']."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // get the new topic_last_post_id of the old topic
    $sql = "SELECT post_id,
                   post_time
            FROM ".$pntable['dizkus_posts']."
            WHERE topic_id = '".(int)DataUtil::formatForStore($post['topic_id'])."'
            ORDER BY post_time DESC";
    $result = dzkSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
    list($new_last_post_id, $new_topic_time) = $result->fields;
    dzkCloseDB($result);

    // update the new topic
    $newtopic_replies = (int)$posts_to_move - 1;
    $sql = "UPDATE ".$pntable['dizkus_topics']."
            SET topic_replies = '$newtopic_replies',
                topic_last_post_id = '" . DataUtil::formatForStore($old_last_post_id) . "'
            WHERE topic_id = '" . DataUtil::formatForStore($newtopic_id) . "'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // update the old topic
    $old_replies = (int)$old_replies - (int)$posts_to_move;
    $sql = "UPDATE ".$pntable['dizkus_topics']."
            SET topic_replies = " . DataUtil::formatForStore($old_replies) . ",
                topic_last_post_id = '" . DataUtil::formatForStore($new_last_post_id) . "',
                topic_time = '" . DataUtil::formatForStore($new_topic_time) . "'
            WHERE topic_id = '". (int)DataUtil::formatForStore($post['topic_id'])."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    return $newtopic_id;
}

/**
 * update_user_post_count
 *
 *@params $args['user_id'] int the users id
 *@params $args['mode']    string, either "inc" (+1) or "dec" (-1)
 *@returns bool true or false
 */
function Dizkus_userapi_update_user_post_count($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    if (!isset($user_id) || !isset($mode)) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }
    if (strtolower($mode)=='inc') {
        $math = '+';
    } elseif(strtolower($mode)=='dec') {
        $math = '-';
    } else {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    $sql = "UPDATE ".$pntable['dizkus_users']."
            SET user_posts = user_posts $math 1
            WHERE user_id = '".(int)DataUtil::formatForStore($user_id)."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);
    return true;
}

/**
 * get_previous_or_next_topic_id
 * returns the next or previous topic_id in the same forum of a given topic_id
 *
 *@params $args['topic_id'] int the reference topic_id
 *@params $args['view']     string either "next" or "previous"
 *@returns int topic_id maybe the same as the reference id if no more topics exist in the selectd direction
 */
function Dizkus_userapi_get_previous_or_next_topic_id($args)
{
    extract($args);
    unset($args);

    if (!isset($topic_id) || !isset($view) ) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    list($dbconn, $pntable) = dzkOpenDB();

    switch ($view) {
        case 'previous':
            $math = '<';
            $sort = 'DESC';
            break;
        case 'next':
            $math = '>';
            $sort = 'ASC';
            break;
        default:
            return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

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
            $whereignorelist = " AND t1.topic_poster NOT IN (".implode(',',$ignored_uids).")";
        }
    }

    $sql = "SELECT t1.topic_id
            FROM ".$pntable['dizkus_topics']." AS t1,
                 ".$pntable['dizkus_topics']." AS t2
            WHERE t2.topic_id = ".(int)DataUtil::formatForStore($topic_id)."
              AND t1.topic_time $math t2.topic_time
              AND t1.forum_id = t2.forum_id
              AND t1.sticky = 0
              ".$whereignorelist."
            ORDER BY t1.topic_time $sort";
    $result = dzkSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);

    if (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $topic_id = $row['topic_id'];
    }
    dzkCloseDB($result);

    return $topic_id;
}

/**
 * getfavorites
 * return the list of favorite forums for this user
 *
 *@params $args['user_id'] -Optional- the user id of the person who we want the favorites for
 *@params $args['last_visit'] timestamp date of last visit
 *@returns array of categories with an array of forums in the catgories
 *
 */
function Dizkus_userapi_getfavorites($args)
{
    static $tree;

    extract($args);
    unset($args);

    // if we have already gone through this once then don't do it again
    // if we have a favorites block displayed and are looking at the
    // forums this will get called twice.
    if (isset($tree)) {
        return $tree;
    }

    // lets get all the forums just like we would a normal display
    // we'll figure out which ones aren't needed further down.
    $tree = pnModAPIFunc('Dizkus', 'user', 'readcategorytree', array('last_visit' => $last_visit ));

    // if they are anonymous they can't have favorites
    if (!pnUserLoggedIn()) {
        return $tree;
    }

    if (!isset($user_id)) {
        $user_id = (int)pnUserGetVar('uid');
    }

    list($dbconn, $pntable) = dzkOpenDB();

    $sql = "SELECT f.forum_id
            FROM ".$pntable['dizkus_forum_favorites']." AS f
            WHERE f.user_id='" . (int)DataUtil::formatForStore($user_id) . "'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    // make sure we start with an empty array
    $favoritesArray = array();
    while(!$result->EOF) {
        list($forum_id) = $result->fields;
        // add this favorite to the favorites array
        array_push($favoritesArray, (int)$forum_id);
        $result->MoveNext();
    }
    dzkCloseDB($result);

    // categoryCount is needed since the categories aren't stored as numerical
    // indexes.  They are stored as associative arrays.
    $categoryCount=0;
    // loop through all the forums and delete all forums that aren't part of
    // the favorites.
    $deleteMe = array();
    foreach ($tree as $categoryIndex => $category) {
        // $count is needed because the index changes as we splice the array
        // but the foreach is working on a copy of the array so the $forumIndex
        // value will point to non-existent elements in the modified array.
        $count = 0;
        foreach ($category['forums'] as $forumIndex=>$forum) {
            // if this isn't one of our favorites then we need to remove it
            if (!in_array((int)$forum['forum_id'],$favoritesArray,true)){
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
 * get_favorite_status
 *
 * read the flag fromthe users table that indicates the users last choice: show all forum (0) or favorites only (1)
 *@params $args['user_id'] int the users id
 *@returns 0 or 1
 *
 */
function Dizkus_userapi_get_favorite_status($args)
{
    extract($args);
    unset($args);

    if (!isset($args['user_id'])) {
        $user_id = (int)pnUserGetVar('uid');
    }

    list($dbconn, $pntable) = dzkOpenDB();
    $userstable = $pntable['dizkus_users'];
    $userscol    = $pntable['dizkus_users_column'];
    $sql = "SELECT $userscol[user_favorites]
            FROM $userstable
            WHERE $userscol[user_id] = '".DataUtil::formatForStore($user_id)."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    list($favorite) = $result->fields;
    dzkCloseDB($result);

    return (bool)$favorite;
}

/**
 * change_favorite_status
 *
 * changes the flag in the users table that indicates the users last choice: show all forum (0) or favorites only (1)
 *@params $args['user_id'] int the users id
 *@returns 0 or 1
 *
 */
function Dizkus_userapi_change_favorite_status($args)
{
    extract($args);
    unset($args);

    if (!isset($user_id)) {
        $user_id = (int)pnUserGetVar('uid');
    }

    $recentstatus = Dizkus_userapi_get_favorite_status(array('user_id' => $user_id));
    $newstatus = ($recentstatus==true) ? 0 : 1;

    list($dbconn, $pntable) = dzkOpenDB();
    $userstable = $pntable['dizkus_users'];
    $userscol   = $pntable['dizkus_users_column'];
    $sql = "UPDATE $userstable
            SET $userscol[user_favorites] = $newstatus
            WHERE $userscol[user_id] = '".DataUtil::formatForStore($user_id)."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    return (bool)$newstatus;
}

/**
 * get_user_post_order
 * Determines the users desired post order for topics.
 * Either Newest First or Oldest First
 * Returns 'ASC' or 'DESC' on success, false on failure.
 *
 *@params user_id - The user id of the person who's order we
 *                  are trying to determine
 *@returns string on success, false on failure
 */
function Dizkus_userapi_get_user_post_order($args)
{
    extract($args);
    unset($args);

    $loggedIn = pnUserLoggedIn();

    // if we are passed the user_id then lets use it
    if (isset($user_id)) {
        // we got passed the id but it is the anonymous user
        // and the user isn't logged in, so we return the default order.
        // We use this check because we may want to call this function
        // from another module or function as an admin, moderator, etc
        // so the logged in user may not be the person we want the info about.
        if ($user_id < 2 || !$loggedIn) {
            return pnModGetVar('Dizkus', 'post_sort_order');
        }
    } else {
        // we didn't get a user_id passed into the function so if
        // the user is logged in then lets use their id.  If not
        // then return th default order.
        if ($loggedIn) {
            $user_id = pnUserGetVar('uid');
        } else {
            return pnModGetVar('Dizkus', 'post_sort_order');
        }
    }

    list($dbconn, $pntable) = dzkOpenDB();
    $pnfusertable = $pntable['dizkus_users'];
    $pnfusercolumn = $pntable['dizkus_users_column'];

    $sql = 'SELECT ' . $pnfusercolumn['user_post_order'] . '
            FROM  ' . $pnfusertable . '
            WHERE ' . $pnfusercolumn['user_id'] . ' = "' . (int)DataUtil::formatForStore($user_id).'"';

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if (!$result->EOF) {
        list($post_order) = $result->fields;
        $post_order = ($post_order) ? 'DESC' : 'ASC';
    } else {
        $post_order = pnModGetVar('Dizkus', 'post_sort_order');
    }
    dzkCloseDB($result);

    return $post_order;
}

/**
 * change_user_post_order
 *
 * changes the flag in the users table that indicates the users preferred post order: Oldest First (0) or Newest First (1)
 *@params $args['user_id'] int the users id
 *@returns bool - true on success, false on failure
 *
 */
function Dizkus_userapi_change_user_post_order($args)
{
    $user_id = $args['user_id'];

    // if we didn't get a user_id and the user isn't logged in then
    // return false because there is no database entry to update
    if (!isset($user_id) && pnUserLoggedIn()) {
        $user_id = (int)pnUserGetVar('uid');
    } else {
        return false;
    }

    $post_order = pnModAPIFunc('Dizkus','user','get_user_post_order');

    $new_post_order = ($post_order=='DESC') ? 0 : 1;

    list($dbconn, $pntable) = dzkOpenDB();
    $userstable = $pntable['dizkus_users'];
    $userscol   = $pntable['dizkus_users_column'];
    $sql = "UPDATE $userstable
            SET $userscol[user_post_order] = $new_post_order
            WHERE $userscol[user_id] = '".DataUtil::formatForStore($user_id)."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    return true;
}

/**
 * get_forum_category
 * Determines the category that a forum belongs to.
 *
 *@params forum_id - The forum id to find the category of
 *@returns int on success, false on failure
 */
function Dizkus_userapi_get_forum_category($args)
{
    if (!isset($args['forum_id']) || !is_numeric($args['forum_id'])) {
        return false;
    }

    $cat_id = (int)DBUtil::selectFieldByID('dizkus_forums', 'cat_id', $args['forum_id'], 'forum_id');
    return $cat_id;
}

/**
 * get_page_from_topic_replies
 * Uses the number of topic_replies and the posts_per_page settings to determine the page
 * number of the last post in the thread. This is needed for easier navigation.
 *
 *@params $args['topic_replies'] int number of topic replies
 *@return int page number of last posting in the thread
 */
function Dizkus_userapi_get_page_from_topic_replies($args)
{
    if (!isset($args['topic_replies']) || !is_numeric($args['topic_replies']) || $args['topic_replies'] < 0 ) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    // get some enviroment
    $posts_per_page = pnModGetVar('Dizkus', 'posts_per_page');
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
 *@params $args['forum'] array with forum information
 *@params $args['force'] boolean if true force connection no matter of active setting or interval
 *@params $args['debug'] boolean indicates debug mode on/off
 *@returns none
 */
function Dizkus_userapi_mailcron($args)
{
    extract($args);
    unset($args);

    if (pnModGetVar('Dizkus', 'm2f_enabled') <> 'yes') {
        return;
    }

    $force = (isset($force)) ? (boolean)$force : false;

    Loader::includeOnce('modules/Dizkus/pnincludes/pop3.php');
    if ( (($forum['pop3_active'] == 1) && ($forum['pop3_last_connect'] <= time()-($forum['pop3_interval']*60)) ) || ($force == true) ) {
        mailcronecho('found active: ' . $forum['forum_id'] . ' = ' . $forum['forum_name'] . "\n", $debug);
        // get new mails for this forum
        $pop3 =& new pop3_class;
        $pop3->hostname = $forum['pop3_server'];
        $pop3->port     = $forum['pop3_port'];
        $error = '';

        // open connection to pop3 server
        if (($error = $pop3->Open()) == '') {
            mailcronecho("connected to the pop3 server '".$pop3->hostname."'.\n", $debug);
            // login to pop3 server
            if (($error = $pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0)) == '') {
                mailcronecho( "user '" . $forum['pop3_login'] . "' logged into pop3 server '".$pop3->hostname."'.\n", $debug);
                // check for message
                if (($error = $pop3->Statistics($messages,$size)) == '') {
                    mailcronecho("there are $messages messages in the mail box with a total of $size bytes.\n", $debug);
                    // get message list...
                    $result = $pop3->ListMessages('', 1);
                    if (is_array($result) && count($result) > 0) {
                        // logout the currentuser
                        mailcronecho("logging out '" . pnUserGetVar('uname') . "' from pn\n", $debug);
                        pnUserLogOut();
                        // login the correct user
                        if (pnUserLogIn($forum['pop3_pnuser'], base64_decode($forum['pop3_pnpassword']), false)) {
                            mailcronecho('user ' . pnUserGetVar('uname') . ' successfully logged in', $debug);
                            if (!allowedtowritetocategoryandforum($forum['cat_id'], $forum['forum_id'])) {
                                mailcronecho("stop: insufficient permissions for " . pnUserGetVar('uname') . " in forum " . $forum['forum_name'] . "(id=" . $forum['forum_id'] . ")", $debug);
                                pnUserLogOut();
                                mailcronecho('user ' . pnUserGetVar('uname') . ' logged out', $debug);
                                return false;
                            }
                            mailcronecho("adding new posts as user '" . pnUserGetVar('uname') . "' now\n", $debug);
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
                                        $subject = DataUtil::formatForDisplay(_DZK_NOSUBJECT);
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
                                                    mailcronecho("added new post '$subject' (post=$post_id) to topic $topic_id\n", $debug);
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
                                                mailcronecho("added new topic '$subject' (topic=$topic_id) to forum '".$forum['forum_name'] ."'\n", $debug);
                                            }
                                        } else {
                                            mailcronecho("mail subject '$subject' does not match requirement - ignored!", $debug);
                                        }
                                    } else {
                                        mailcronecho("mail subject '$subject' is a possible loop - ignored!", $debug);
                                    }
                                    // mark message for deletion
                                    $pop3->DeleteMessage($cnt);
                                }
                            }
                            // logout the mail2forum user
                            if (pnUserLogOut()) {
                                mailcronecho('user ' . $forum['pop3_pnuser'] . ' logged out', $debug);
                            }
                        } else {
                            mailcronecho("error: cannot login user '". $forum['pop3_pnuser'] ."' to pn\n");
                        }
                        // close pop3 connection and finally delete messages
                        if ($error == '' && ($error=$pop3->Close()) == '') {
                            mailcronecho("disconnected from the POP3 server '".$pop3->hostname."'.\n");
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
        list($dbconn, $pntable) = dzkOpenDB();
        $sql = "UPDATE ".$pntable['dizkus_forums']."
                SET forum_pop3_lastconnect='". DataUtil::formatForStore(time()) . "'
                WHERE forum_id=" . DataUtil::formatForStore($forum['forum_id']) . "";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
    }

    return;
}

/**
 * testpop3connection
 *
 *@params $args['forum_id'] int the id of the forum to test the pop3 connection
 *@returns array of messages from pop3 connection test
 *
 */
function Dizkus_userapi_testpop3connection($args)
{
    if (!isset($args['forum_id']) || !is_numeric($args['forum_id'])) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                          array('forum_id' => $args['forum_id']));
    Loader::includeOnce('modules/Dizkus/pnincludes/pop3.php');

    $pop3 =& new pop3_class;
    $pop3->hostname = $forum['pop3_server'];
    $pop3->port     = $forum['pop3_port'];

    $error = '';
    $pop3messages = array();
    if (($error=$pop3->Open()) == '') {
        $pop3messages[] = "connected to the POP3 server '".$pop3->hostname."'";
        if (($error=$pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0))=='') {
            $pop3messages[] = "user '" . $forum['pop3_login'] . "' logged in";
            if (($error=$pop3->Statistics($messages,$size))=='') {
                $pop3messages[] = "there are $messages messages in the mail box with a total of $size bytes.";
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
                        $pop3messages[] = "disconnected from the POP3 server '".$pop3->hostname."'.\n";
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
 *@params $args['msgid'] string the msgid
 *@returns int topic_id or false if not found
 *
 */
function Dizkus_userapi_get_topic_by_postmsgid($args)
{
    if (!isset($args['msgid']) || empty($args['msgid'])) {
        return showforumerror(_MODSRGSERROR, __FILE__, __LINE__);
    }

    $topic_id = DBUtil::selectFieldByID('dizkus_posts', 'topic_id', $args['msgid'], 'post_msgid');
    return $topic_id;
}

/**
 * get_topicid_by_postid
 * gets a topic_id from the post_id
 *
 *@params $args['post_id'] string the post_id
 *@returns int topic_id or false if not found
 *
 */
function Dizkus_userapi_get_topicid_by_postid($args)
{
    if (!isset($args['post_id']) || empty($args['post_id'])) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    $topic_id = DBUtil::selectFieldByID('dizkus_posts', 'topic_id', $args['post_id'], 'post_id');
    return $topic_id;
}

/**
 * movepost
 *
 *@params $args['post'] array with posting data as returned from readpost()
 *@params $args['to_topic']
 *@returns int id of the new topic
 */
function Dizkus_userapi_movepost($args)
{
    extract($args); // $post, $to_topic
    unset($args);
    // 1 . update topic_id, post_time in posts table
    // for post[post_id]
    // 2 . update topic_replies in nuke_dizkus_topics ( COUNT )
    // for old_topic
    // 3 . update topic_last_post_id in nuke_dizkus_topics
    // for old_topic
    // 4 . update topic_replies in nuke_dizkus_topics ( COUNT )
    // 5 . update topic_last_post_id in nuke_dizkus_topics if necessary

    list($dbconn, $pntable) = dzkOpenDB();

    // 1 . update topic_id in posts table

    $sql = "UPDATE ".$pntable['dizkus_posts']."
            SET topic_id='".(int)DataUtil::formatForStore($to_topic)."'
            WHERE (post_id = '".(int)DataUtil::formatForStore($post['post_id'])."')";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // for to_topic
    // 2 . update topic_replies in nuke_dizkus_topics ( COUNT )
    // 3 . update topic_last_post_id in nuke_dizkus_topics
    // get the new topic_last_post_id of to_topic
    $sql = "SELECT post_id, post_time
            FROM ".$pntable['dizkus_posts']."
            WHERE topic_id = '".(int)DataUtil::formatForStore($to_topic)."'
            ORDER BY post_time DESC";
    $result = dzkSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
    list($to_last_post_id, $to_post_time) = $result->fields;
    dzkCloseDB($result);

    $sql = "UPDATE ".$pntable['dizkus_topics']."
            SET topic_replies = topic_replies + 1,
        topic_last_post_id='".(int)DataUtil::formatForStore($to_last_post_id)."',
        topic_time='".DataUtil::formatForStore($to_post_time)."'
            WHERE topic_id='".(int)DataUtil::formatForStore($to_topic)."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // for old topic ($post[topic_id]
    // 4 . update topic_replies in nuke_dizkus_topics ( COUNT )
    // 5 . update topic_last_post_id in nuke_dizkus_topics if necessary

    // Se obtiene el valor de topic_last_pos_id en el topic antiguo
    // get the new topic_last_post_id of the old topic
    $sql = "SELECT post_id, post_time
            FROM ".$pntable['dizkus_posts']."
            WHERE topic_id = '".(int)DataUtil::formatForStore($post['topic_id'])."'
            ORDER BY post_time DESC";
    $result = dzkSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
    list($old_last_post_id, $old_post_time) = $result->fields;
    dzkCloseDB($result);

    // update
    $sql = "UPDATE ".$pntable['dizkus_topics']."
            SET topic_replies = topic_replies - 1,
        topic_last_post_id = '".(int)DataUtil::formatForStore($old_last_post_id)."',
        topic_time='".DataUtil::formatForStore($old_post_time)."'
            WHERE topic_id = '".(int)DataUtil::formatForStore($post['topic_id'])."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    return Dizkus_userapi_get_last_topic_page(array('topic_id' => $post['topic_id']));
}

/**
 * get_last_topic_page
 * returns the number of the last page of the topic if more than posts_per_page entries
 * eg. for use as the start parameter in urls
 *
 *@params $args['topic_id'] int the topic id
 *@returns int the page number
 */
function Dizkus_userapi_get_last_topic_page($args)
{
    // get some enviroment
    $posts_per_page = pnModGetVar('Dizkus', 'posts_per_page');
    $post_sort_order = pnModGetVar('Dizkus', 'post_sort_order');

    if (!isset($args['topic_id']) || !is_numeric($args['topic_id'])) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
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
 *@params $args['from_topic_id'] int this topic get integrated into to_topic
 *@params $args['to_topic_id'] int   the target topic that will contain the post from from_topic
 */
function Dizkus_userapi_jointopics($args)
{
    extract($args); // $new_topic, $old_topic (parameters)
    unset($args);

    // check if from_topic exists. this function will return an error if not
    $from_topic = pnModAPIFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $from_topic_id, 'complete' => false, 'count' => false));
    if (!allowedtomoderatecategoryandforum($from_topic['cat_id'], $from_topic['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod', $from_topic['forum_id'], 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    // check if to_topic exists. this function will return an error if not
    $to_topic = pnModAPIFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $to_topic_id, 'complete' => false, 'count' => false));
    if (!allowedtomoderatecategoryandforum($to_topic['cat_id'], $to_topic['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod', $to_topic['forum_id'], 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    list($dbconn, $pntable) = dzkOpenDB();

    // join topics: update posts with from_topic['topic_id'] to contain to_topic['topic_id']
    // and from_topic['forum_id'] to to_topic['forum_id']
    $sql = "UPDATE ".$pntable['dizkus_posts']."
            SET topic_id = '".(int)DataUtil::formatForStore($to_topic['topic_id'])."',
        forum_id = '".(int)DataUtil::formatForStore($to_topic['forum_id'])."'
            WHERE topic_id='".(int)DataUtil::formatForStore($from_topic['topic_id'])."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // to_topic['topic_replies'] must be incremented by from_topic['topic_replies'] + 1 (initial
    // posting
    // update to_topic['topic_time'] and to_topic['topic_last_post_id']
    // get new topic_time and topic_last_post_id
    $sql = "SELECT post_id, post_time
            FROM ".$pntable['dizkus_posts']."
            WHERE topic_id = '".(int)DataUtil::formatForStore($to_topic['topic_id'])."'
            ORDER BY post_time DESC";
    $result = dzkSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
    list($new_last_post_id, $new_post_time) = $result->fields;
    dzkCloseDB($result);

    $topic_replies = $to_topic['topic_replies'] + $from_topic['topic_replies'] + 1;

    $sql = "UPDATE ".$pntable['dizkus_topics']."
            SET topic_replies = '".(int)DataUtil::formatForStore($topic_replies)."',
        topic_last_post_id='".(int)DataUtil::formatForStore($new_last_post_id)."',
        topic_time='".DataUtil::formatForStore($new_post_time)."'
            WHERE topic_id='".(int)DataUtil::formatForStore($to_topic['topic_id'])."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // delete from_topic from dizkus_topics
    $sql = "DELETE FROM ".$pntable['dizkus_topics']." WHERE topic_id='".(int)DataUtil::formatForStore($from_topic['topic_id'])."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // update forums table
    // get topics count: decrement from_topic['forum_id']'s topic count by 1
    $sql = "UPDATE ".$pntable['dizkus_forums']."
            SET forum_topics = forum_topics - 1
            WHERE forum_id='".(int)DataUtil::formatForStore($from_topic['forum_id'])."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    // get posts count: if both topics are in the same forum, we just have to increment
    // the post count by 1 for the initial postig that is now part of the to_topic,
    // if they are in different forums, we have to decrement the post count
    // in from_topic's forum and increment it in to_topic's forum by from_topic['topic_replies'] + 1
    // for the initial posting
    // get last_post: if both topics are in the same forum, everything stays
    // as-is, if not, we update both, even if it is not necessary

    if ($from_topic['forum_id'] == $to_topic['forum_id']) {
        // same forum
        $sql = "UPDATE ".$pntable['dizkus_forums']."
                SET forum_posts = forum_posts + 1
                WHERE forum_id='".(int)DataUtil::formatForStore($to_topic['forum_id'])."'";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
    } else {
        // different forum
        // get last post in forums
        $sql = "SELECT post_id
                FROM ".$pntable['dizkus_posts']."
                WHERE forum_id = '".(int)DataUtil::formatForStore($from_topic['forum_id'])."'
                ORDER BY post_time DESC";
        $result = dzkSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
        list($from_forum_last_post_id) = $result->fields;
        dzkCloseDB($result);

        $sql = "SELECT post_id
                FROM ".$pntable['dizkus_posts']."
                WHERE forum_id = '".(int)DataUtil::formatForStore($to_topic['forum_id'])."'
                ORDER BY post_time DESC";
        $result = dzkSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
        list($to_forum_last_post_id) = $result->fields;
        dzkCloseDB($result);

        $post_count_difference = (int)DataUtil::formatForStore($from_topic['topic_replies']+1);
        // decrement from_topic's forum post_count
        $sql = "UPDATE ".$pntable['dizkus_forums']."
                SET forum_posts = forum_posts - $post_count_difference,
                    forum_last_post_id = '" . (int)DataUtil::formatForStore($from_forum_last_post) . "'
                WHERE forum_id='".(int)DataUtil::formatForStore($from_topic['forum_id'])."'";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);

        $sql = "UPDATE ".$pntable['dizkus_forums']."
                SET forum_posts = forum_posts + $post_count_difference,
                    forum_last_post_id = '" . (int)DataUtil::formatForStore($to_forum_last_post) . "'
                WHERE forum_id='".(int)DataUtil::formatForStore($to_topic['forum_id'])."'";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        dzkCloseDB($result);
    }



/*
// 1. Select all posts to move from dizkus_posts table
  $sql = "SELECT post_id,topic_id,forum_id,poster_id,post_time,poster_ip
          FROM ".$pntable['dizkus_posts']."
        WHERE topic_id='".(int)DataUtil::formatForStore($post['old_topic'])."'";
  $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
// 2. Updated date for all moved posts
  $time = date("Y-m-d H:i");
// 3. Make a loop with all readed posts: Readed post = moved post
  while(!$result->EOF) {
    $readedPost = $result->GetRowAssoc(false);
    // Every post is inserted at the end of the table dizkus_post, so we are sure every message
    // is moved in the correct order and with the right date
    $sql = "INSERT INTO ".$pntable['dizkus_posts']." (topic_id,forum_id,poster_id,post_time,poster_ip)
        VALUES ('".(int)DataUtil::formatForStore($post['new_topic'])."',
        '".(int)DataUtil::formatForStore($readedPost['forum_id'])."',
        '".(int)DataUtil::formatForStore($readedPost['poster_id'])."',
        '".DataUtil::formatForStore($time)."',
        '".DataUtil::formatForStore($readedPost['poster_ip'])."')";
    $result2 = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result2);
    // We get the new post_id from the moved post
    $last_post_id = $dbconn->PO_Insert_ID($pntable['dizkus_posts'], 'post_id');
    // Read post text using old post_id (post_id before move)
//    $sql = "SELECT post_text FROM ".$pntable['dizkus_posts_text']." WHERE post_id='".(int)DataUtil::formatForStore($readedPost['post_id'])."'";
    $result2 = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    list($post_text) = $result2->fields;
    dzkCloseDB($result2);
    // Text post is inserted at the end of the post_text table using new post_id values
//    $sql = "INSERT INTO ".$pntable['dizkus_posts_text']." (post_id,post_text) VALUES ('".(int)DataUtil::formatForStore($last_post_id)."','".DataUtil::formatForStore($post_text)."')";
    $result2 = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result2);
    // At this moment we have the post duplicated: in the old Topic and in the new Topic
    // Delete old post_text in old Topic
//    $sql = "DELETE FROM ".$pntable['dizkus_posts_text']." WHERE post_id='".(int)DataUtil::formatForStore($readedPost['post_id'])."'";
    $result2 = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result2);
    // Delete old post in old Topic
    $sql = "DELETE FROM ".$pntable['dizkus_posts']." WHERE post_id='".(int)DataUtil::formatForStore($readedPost['post_id'])."'";
    $result2 = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result2);
    // Siguiente post
    $result->MoveNext();
    }
    dzkCloseDB($result);
// We can delete all moved posts just with 1 SQL for each table...
  //$sql = "DELETE FROM ".$pntable['dizkus_posts']." WHERE topic_id='".(int)DataUtil::formatForStore($post['old_topic'])."'";
  //$result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
  //dzkCloseDB($result);
// All posts should be moved, we don't need old Topic data
// 4. Detele the old Topic from dizkus_topics
  $sql = "DELETE FROM ".$pntable['dizkus_topics']." WHERE topic_id='".(int)DataUtil::formatForStore($post['old_topic'])."'";
  $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
  dzkCloseDB($result);
// Sync
// 6. Update last_post_id & topic_replies values of the Topic with the moved posts in dizkus_topics
// 6.1. topic_replies : Count number of posts
  $sql = "SELECT COUNT(*) FROM ".$pntable['dizkus_posts']." WHERE topic_id='".(int)DataUtil::formatForStore($post['new_topic'])."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    list($topic_replies) = $result->fields;
    dzkCloseDB($result);
// 6.2. last_post_id : we get from last loop :)

// 6.3. Update
    $sql = "UPDATE ".$pntable['dizkus_topics']."
            SET topic_replies = '".(int)DataUtil::formatForStore($topic_replies)."',
        topic_last_post_id='".(int)DataUtil::formatForStore($last_post_id)."',
        topic_time='".DataUtil::formatForStore($time)."'
            WHERE topic_id='".(int)DataUtil::formatForStore($post['new_topic'])."'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

// 7. All done, return $topic_id of the Topic with moved posts
*/
    return $to_topic['topic_id'];
}

/**
 * get_last_post
 * gets the post_id of the very last posting stored in our database, independent
 * from topic or forum
 *
 *@params none
 *@return int post_id or false on error
 */
function Dizkus_userapi_get_last_post()
{
    $post_id = (int)DBUtil::selectFieldMax('dizkus_posts', 'post_id');
    return $post_id;
}

/**
 * notify moderators
 *
 *@params $args['post'] array the post array
 *@returns void
 */
function Dizkus_userapi_notify_moderator($args)
{
    extract($args);
    unset($args);

    setlocale (LC_TIME, pnConfigGetVar('locale'));
    $modinfo = pnModGetInfo(pnModGetIDFromName(pnModGetName()));

    $mods = pnModAPIFunc('Dizkus', 'admin', 'readmoderators',
                         array('forum_id' => $post['forum_id']));

    // generate the mailheader
    $email_from = pnModGetVar('Dizkus', 'email_from');
    if ($email_from == '') {
        // nothing in forumwide-settings, use PN adminmail
        $email_from = pnConfigGetVar('adminmail');
    }

    $subject .= DataUtil::formatForDisplay(_DZK_MODERATION_NOTICE) . ': ' . strip_tags($post['topic_rawsubject']);
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
                    foreach($group['members'] as $gm_uid) {
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
                          array('topic_replies' => $post['topic_replies'],
                                'start'         => $start));

    $message = _DZK_NOTIFYMODBODY1 . ' ' . pnConfigGetVar('sitename') . "\n"
            . $post['cat_title'] . '::' . $post['forum_name'] . '::' . $post['topic_rawsubject'] . "\n\n"
            . _DZK_REPORTINGUSERNAME . ": $reporting_username \n"
            . _DZK_NOTIFYMODBODY2 . ": \n"
            . $comment . " \n\n"
            . "---------------------------------------------------------------------\n"
            . strip_tags($post['post_text']) . " \n"
            . "---------------------------------------------------------------------\n\n"
            . _DZK_NOTIFYMODBODY3 . ":\n"
            . pnGetBaseURL() . pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $post['topic_id'], 'start' => $start)) . '#pid' . $post['post_id'] . "\n"
            . "\n";

    if (count($recipients) > 0) {
        foreach($recipients as $recipient) {
            $args = array( 'fromname'    => $sitename,
                           'fromaddress' => $email_from,
                           'toname'      => $recipient['uname'],
                           'toaddress'   => $recipient['email'],
                           'subject'     => $subject,
                           'body'        => $message,
                           'headers'     => array('X-UserID: ' . $reporting_userid,
                                                  'X-Mailer: ' . $modinfo['name'] . ' ' . $modinfo['version']));
            pnModAPIFunc('Mailer', 'user', 'sendmessage', $args);
        }
    }

    return;
}

/**
 * get_topicid_by_reference
 * gets a topic reference as parameter and delivers the internal topic id
 * used for Dizkus as comment module
 *
 *@params $args['reference'] string the refernce
 */
function Dizkus_userapi_get_topicid_by_reference($args)
{
    if (!isset($args['reference']) || empty($args['reference'])) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    $topic_id = DBUtil::selectFieldByID('dizkus_topics', 'topic_id', $args['reference'], 'topic_reference');
    return $topic_id;
}

/**
 * insertrss
 *
 *@params $args['forum']    array with forum data
 *@params $args['items']    array with feed data as returned from Feeds module
 *@return boolean true or false
 */
function Dizkus_userapi_insertrss($args)
{
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

    foreach ($args['items'] as $item) {
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
            $message  = $boldstart . _DZK_RSS_SUMMARY . ' :' . $boldend . "\n\n" . $item->get_description() . "\n\n" . $urlstart . $item->get_link() . $urlend . "\n\n";

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
 *@params none
 *@params $args['user_id'] int the users id (needs ACCESS_ADMIN)
 *@returns array with forum ids, may be empty
 */
function Dizkus_userapi_get_forum_subscriptions($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    if (isset($user_id)) {
        if(!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    } else {
        $user_id = pnUserGetVar('uid');
    }

    $fstable  = $pntable['dizkus_subscription'];
    $fscolumn = $pntable['dizkus_subscription_column'];
    $forumstable  = $pntable['dizkus_forums'];
    $forumscolumn = $pntable['dizkus_forums_column'];
    $categoriestable  = $pntable['dizkus_categories'];
    $categoriescolumn = $pntable['dizkus_categories_column'];

    // read the topic ids
    $sql = 'SELECT f.' . $forumscolumn['forum_id'] . ',
                   f.' . $forumscolumn['forum_name'] . ',
                   c.' . $categoriescolumn['cat_id'] . ',
                   c.' . $categoriescolumn['cat_title'] . '
            FROM ' . $fstable . ' AS fs,
                 ' . $forumstable . ' AS f,
                 ' . $categoriestable . ' AS c 
            WHERE fs.' . $fscolumn['user_id'] . '=' . (int)DataUtil::formatForStore($user_id) . '
              AND f.' . $forumscolumn['forum_id'] . '=fs.' . $fscolumn['forum_id'] . '
              AND c.' . $categoriescolumn['cat_id'] . '=f.' . $forumscolumn['cat_id']. '
            ORDER BY c.' . $categoriescolumn['cat_order'] . ', f.' . $forumscolumn['forum_order'];
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $subscriptions = array();
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $subscription = array('forum_id'      => $row['forum_id'],
                              'forum_name'    => $row['forum_name'],
                              'cat_id'        => $row['cat_id'],
                              'cat_title'     => $row['cat_title']);
        array_push($subscriptions, $subscription);
        $result->MoveNext();
    }

    dzkCloseDB($result);

    return $subscriptions;
}

/**
 * get_settings_ignorelist
 *
 *@params none
 *@params $args['uid']  int     the users id
 *@returns level for ignorelist handling as string
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

    $attr = pnUserGetVar('__ATTRIBUTES__',$uid);
    $ignorelist_myhandling = $attr['dzk_ignorelist_myhandling'];
    $default = pnModGetVar('Dizkus','ignorelist_handling');
    if (isset($ignorelist_myhandling) && ($ignorelist_myhandling != '')) {
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
