<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                               *
 ************************************************************************
 * Modified version of:                                                 *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License                                                              *
 ************************************************************************
 * This program is free software; you can redistribute it and/or modify *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation; either version 2 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * This program is distributed in the hope that it will be useful,      *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with this program; if not, write to the Free Software          *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 *
 * USA                                                                  *
 ************************************************************************
 *
 * user api functions
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2004 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html> 
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

include_once("modules/pnForum/common.php");

/**
 * get_userdata_from_id
 * This function dynamically reads all fields of the <prefix>_users and <prefix>_pnforum_users
 * tables. When ever data fields are added there, they will be read too without any change here.
 *
 *@params $args{'userid'] int the users id (pn_uid)
 *@returns array of userdata information
 */
function pnForum_userapi_get_userdata_from_id($args)
{
    extract($args);
    unset($args);
    
    list($dbconn, $pntable) = pnfOpenDB();
    
    $phpbbusercol = $pntable['pnforum_users_column'];
    $phpbbusercolkeys = array_keys($phpbbusercol);
    $usercol    = $pntable['users_column'];
    $usercolkeys    = array_keys($usercol);

    $user = array();    
    $sql = "SELECT ";
    $prefix = pnConfigGetVar('prefix');
    $keycount = count($phpbbusercolkeys);
    for($cnt=0; $cnt<$keycount; $cnt++) {
        $key = str_replace($prefix . "_pnforum_users.", "", $phpbbusercol[$phpbbusercolkeys[$cnt]]);
        $sql .= "b." . $key . ", ";
        $userkeys[] = $key;
    }
    $keycount = count($usercolkeys);
    for($cnt=0; $cnt<$keycount; $cnt++) {
        $key = str_replace($prefix . "_users.", "", $usercol[$usercolkeys[$cnt]]);
        $sql .= "n." . $key;
        $userkeys[] = $key;
        if($cnt <> $keycount-1) {
            $sql .=", ";
        } else {
            $sql .= " ";
        }
    }
    $sql .= "FROM ".$pntable['users']." AS n 
             LEFT JOIN ".$pntable['pnforum_users']." AS b ON b.user_id=n.pn_uid 
             WHERE n.pn_uid='".(int)pnVarPrepForStore($userid)."'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    
    if(!$result->EOF) {
        $userdata = array();
        for($i=0; $i<count($userkeys); $i++) {
            $userdata[$userkeys[$i]] = $result->fields[$i];
        }
        pnfCloseDB($result);
        
        // set some basic data
        $userdata['moderate'] = false;
        $userdata['reply'] = false;
        $userdata['seeip'] = false;

        //
        // get the users rank
        //
        if ($userdata['user_rank'] != 0) {
            $sql = "SELECT rank_title, rank_image
                    FROM ".$pntable['pnforum_ranks']."
                    WHERE rank_id = '".(int)pnVarPrepForStore($userdata['user_rank'])."'";
        } elseif ($userdata['user_posts'] != 0) {
            $sql = "SELECT rank_title, rank_image
                    FROM ".$pntable['pnforum_ranks']."
                    WHERE rank_min <= '".(int)pnVarPrepForStore($userdata['user_posts'])."' 
                    AND rank_max >= '".(int)pnVarPrepForStore($userdata['user_posts'])."'";
        }
        $rank_result = pnfExecuteSQL($dbconn, $sql);

        $rank = "";
        $rank_image = "";
        while (!$rank_result->EOF) {
            list($rank, $rank_image) = $rank_result->fields;
            if($rank) {
                $userdata['rank'] = $rank;
                if($rank_image) {
                    $userdata['rank_image'] =  pnModGetVar('pnForum', 'url_ranks_images') . "/" . $rank_image;
                    $userdata['rank_image_attr'] = getimagesize($userdata['rank_image']);
                }
            }
            $rank_result->MoveNext();
        }
        pnfCloseDB($rank_result);
        
        //
        // user name and avatar
        //
        if($userdata['pn_uid'] != 1) {
            // user is logged in, display some info
            $activetime = time() - (pnConfigGetVar('secinactivemins') * 60);
            $userhack = "SELECT pn_uid
                         FROM ".$pntable['session_info']."
                         WHERE pn_uid = '".$userdata['pn_uid']."'
                         AND pn_lastused > '".pnVarPrepForStore($activetime)."'";

            $userresult = pnfExecuteSQL($dbconn, $userhack);

            $online_state = $userresult->GetRowAssoc(false);
            $userdata['online'] = false;
            if($online_state['pn_uid'] == $userdata['pn_uid']) {
                $userdata['online'] = true; //$online_state[$userdata['pn_uid']];
            }
            pnfCloseDB($userresult);

            // avatar
            if($userdata['pn_user_avatar']){
                $userdata['pn_user_avatar'] = "images/avatar/" . $userdata['pn_user_avatar'];
                $userdata['pn_user_avatar_attr'] = getimagesize($userdata['pn_user_avatar']);
            }

        } else {
            // user is anonymous
            $userdata['pn_uname'] = pnConfigGetVar('anonymous');
        }
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
function pnForum_userapi_boardstats($args)
{
    extract($args);
    unset($args);
    
    list($dbconn, $pntable) = pnfOpenDB();

    switch($type) {
        case 'all':
            $sql = "SELECT SUM(forum_posts) as total 
                    FROM ".$pntable['pnforum_forums'].""; 
            break;
        case 'category':
            $sql = "SELECT count(*) AS total 
                    FROM ".$pntable['pnforum_categories']."";
            break;
        case 'topic':
            $sql = "SELECT count(*) AS total 
                    FROM ".$pntable['pnforum_posts']." 
                    WHERE topic_id = '".pnVarPrepForStore($id)."'";
            break;
        case 'forumposts':
            $sql = "SELECT count(*) AS total 
                    FROM ".$pntable['pnforum_posts']." 
                    WHERE forum_id = '".pnVarPrepForStore($id)."'";
            break;
        case 'forumtopics':
            $sql = "SELECT count(*) AS total 
                    FROM ".$pntable['pnforum_topics']." 
                    WHERE forum_id = '".pnVarPrepForStore($id)."'";
            break;
        default:
        }
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    list ($total) = $result->fields;
    pnfCloseDB($result);
    return $total;
}

/**
 * get_last_post_in_topic
 * gets the last post in a topic, false if no posts
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['id_only'] boolean if true, only return the id, not the complete post information
 *@returns array with post information of false
 */
function pnForum_userapi_get_last_post_in_topic($args)
{
    extract($args);
    unset($args);
    
    list($dbconn, $pntable) = pnfOpenDB();
  
    if(isset($topic_id) && is_numeric($topic_id)) {
        $sql = "SELECT p.post_id 
                FROM ".$pntable['pnforum_posts']." AS p 
                WHERE p.topic_id = '".(int)pnVarPrepForStore($topic_id)."' 
                ORDER BY p.post_time DESC";
        
        $result = pnfSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
        if($result->EOF) {
            pnfCloseDB($result);
            return false;
        }
        $row = $result->GetRowAssoc(false);
        $post_id = $row['post_id'];
        pnfCloseDB($result);
        if($id_only == true) {
            return $post_id;
        }        
        return pnForum_userapi_readpost(array('post_id' => $post_id));
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
function pnForum_userapi_get_last_post_in_forum($args)
{
    extract($args);
    unset($args);
    
    list($dbconn, $pntable) = pnfOpenDB();
    
    if(isset($forum_id) && is_numeric($forum_id)) {
        $sql = "SELECT p.post_id 
                FROM ".$pntable['pnforum_posts']." AS p 
                WHERE p.forum_id = '".(int)pnVarPrepForStore($forum_id)."' 
                ORDER BY p.post_time DESC";
        
        $result = pnfSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
        if($result->EOF) {
            pnfCloseDB($result);
            return false;
        }
        $row = $result->GetRowAssoc(false);
        $post_id = $row['post_id'];
        pnfCloseDB($result);
        if($id_only == true) {
            return $post_id;
        }        
        return pnForum_userapi_readpost(array('post_id' => $post_id));
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
function pnForum_userapi_readcategorytree($args)
{
    static $tree;

    // if we have already called this once during the script
    if (isset($tree)) {
        return $tree;
    }

    extract($args);
    unset($args);
    
    list($dbconn, $pntable) = pnfOpenDB();
    
    $sql = "SELECT c.cat_id,
                    c.cat_title,
                    c.cat_order,
                    f.forum_id,
                    f.forum_name,
                    f.forum_desc,
                    f.forum_topics,
                    f.forum_posts,
                    u.pn_uname,
                    u.pn_uid,
                    p.topic_id,
                    p.post_time
            FROM ".$pntable['pnforum_categories']." AS c
            LEFT JOIN ".$pntable['pnforum_forums']." AS f ON f.cat_id=c.cat_id
            LEFT JOIN ".$pntable['pnforum_posts']." AS p ON p.post_id=f.forum_last_post_id
            LEFT JOIN ".$pntable['users']." AS u ON u.pn_uid=p.poster_id
            ORDER BY c.cat_order, f.forum_order, f.forum_name";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $folder_image = pnModGetVar('pnForum', 'folder_image');
    $newposts_image = pnModGetVar('pnForum', 'newposts_image');
    
    $tree = array();
    while(!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $cat   = array();
        $forum = array();
        $cat['forums'] = array();
        $cat['cat_id']         = $row['cat_id'];
        $cat['cat_title']      = $row['cat_title'];
        $cat['cat_order']      = $row['cat_order'];
        $forum['forum_id']     = $row['forum_id'];
        $forum['forum_name']   = $row['forum_name'];
        $forum['forum_desc']   = $row['forum_desc'];
        $forum['forum_topics'] = $row['forum_topics'];
        $forum['forum_posts']  = $row['forum_posts'];
        $forum['pn_uname']     = $row['pn_uname'];
        $forum['pn_uid']       = $row['pn_uid'];
        $forum['topic_id']     = $row['topic_id'];
        $forum['post_time']    = $row['post_time'];
        if(allowedtoseecategoryandforum($cat['cat_id'], $forum['forum_id'])) {
            if(!array_key_exists( $cat['cat_title'], $tree)) {
                $tree[$cat['cat_title']] = $cat;
            }
            if(!empty($forum['forum_id'])) {
                if ($forum['forum_topics'] != 0) {
                    // are there new topics since last_visit?
                    if ($forum['post_time'] > $last_visit) {
                        // we have new posts
                        $fldr_img = $newposts_image;
                        $fldr_alt = _PNFORUM_NEWPOSTS;
                    } else {
                        // no new posts
                        $fldr_img = $folder_image;
                        $fldr_alt = _PNFORUM_NONEWPOSTS;
                    }
                
                    $posted_unixtime= strtotime ($forum['post_time']);
                    $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($posted_unixtime));
                    if ($posted_unixtime) {
                        if ($forum['pn_uid']==1) {
                            $username = pnConfigGetVar('anonymous');
                        } else {
                            $username = $forum['pn_uname'];
                        }
                
                        $last_post = sprintf(_PNFORUM_LASTPOSTSTRING, $posted_ml, $username);
                        $last_post = $last_post." <a href=\"". pnModURL('pnForum','user','viewtopic', array('topic' =>$forum['topic_id'])). "\">"
                                               ."<img src=\"modules/pnForum/pnimages/icon_latest_topic.gif\" alt=\"".$posted_ml." ".$username."\" height=\"9\" width=\"18\"></a>";
                    } else {
                        // no posts in forum
                        $last_post = _PNFORUM_NOPOSTS;
                    }
                } else {
                    // there are no posts in this forum
                    $fldr_img = $folder_image;
                    $fldr_alt = _PNFORUM_NONEWPOSTS;
                    $last_post = _PNFORUM_NOPOSTS;
                }
                $forum['fldr_img']  = $fldr_img;
                $forum['fldr_img_attr'] =  getimagesize($fldr_img);
                $forum['fldr_alt']  = $fldr_alt;
                $forum['last_post'] = $last_post;
                $forum['forum_mods'] = pnForum_userapi_get_moderators(array('forum_id' => $forum['forum_id']));
                
                array_push($tree[$cat['cat_title']]['forums'], $forum);
            }
        }
        $result->MoveNext();
    }
    // sort the array by cat_order
    uasort($tree, 'cmp_catorder');
    pnfCloseDB($result);
    return $tree;
}

/**
 * Returns an array of all the moderators of a forum
 *
 *@params $args['forum_id'] int the forums id
 *@returns array containing the pn_uid as index and the users name as value
 */
function pnForum_userapi_get_moderators($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT u.pn_uname, u.pn_uid 
            FROM ".$pntable['users']." u, ".$pntable['pnforum_forum_mods']." f 
            WHERE f.forum_id = '".(int)pnVarPrepForStore($forum_id)."' 
            AND u.pn_uid = f.user_id";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $mods = array();
    while(!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $mods[$row['pn_uid']] = $row['pn_uname'];
        $result->MoveNext();
    }
    pnfCloseDB($result);
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
function pnForum_userapi_setcookies()
{
    /**
     * set last visit cookies and get last visit time
     * set LastVisit cookie, which always gets the current time and lasts one year
     */
    setcookie('phpBBLastVisit', time(), time()+31536000);
    
    if (!isset ($_COOKIE['phpBBLastVisitTemp'])){
        $temptime = $_COOKIE['phpBBLastVisit'];
    } else {
        $temptime = $_COOKIE['phpBBLastVisitTemp'];
    }
    if(empty($temptime)) {
        $temptime = 0;
    }
    
    // set LastVisitTemp cookie, which only gets the time from the LastVisit and lasts for 30 min
    setcookie('phpBBLastVisitTemp', $temptime, time()+1800);
    
    // set vars for all scripts
    $last_visit = ml_ftime("%Y-%m-%d %H:%M",$temptime);
    return array($last_visit, $temptime);
}

/**
 * readforum
 * reads the forum information and the last posts_per_page topics incl. poster data
 *
 *@params $args['forum_id'] int the forums id
 *@params $args['start'] int number of topic to start with (if on page 1+)
 *@params $args['last_visit'] string users last visit date
 *@returns very complex array, see <!--[ debug ]--> for more information 
 */
function pnForum_userapi_readforum($args)
{
    extract($args);
    unset($args);

    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 

    $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                          array('forum_id' => $forum_id));
    if($forum==false) {
        return showforumerror(_PNFORUM_FORUM_NOEXIST, __FILE__, __LINE__);
    }

    if(!allowedtoseecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(_PNFORUM_NOAUTH_TOSEE, __FILE__, __LINE__);
    } 

    list($dbconn, $pntable) = pnfOpenDB();

    $posts_per_page     = pnModGetVar('pnForum', 'posts_per_page');
    $topics_per_page    = pnModGetVar('pnForum', 'topics_per_page');
    $hot_threshold      = pnModGetVar('pnForum', 'hot_threshold');
    $folder_image       = pnModGetVar('pnForum', 'folder_image');
    $hot_folder_image   = pnModGetVar('pnForum', 'hot_folder_image');
    $newposts_image     = pnModGetVar('pnForum', 'newposts_image');
    $hot_newposts_image = pnModGetVar('pnForum', 'hot_newposts_image');
    $posticon           = pnModGetVar('pnForum', 'posticon');
    $firstnew_image     = pnModGetVar('pnForum', 'firstnew_image');

    // read moderators
    $forum['forum_mods'] = pnForum_userapi_get_moderators(array('forum_id' => $forum['forum_id']));
    $forum['last_visit'] = $last_visit;

    // let us calculate GotoPage line here
    $l_phpbb_showGotopage = 0;
    if (!empty ($start)) {
        $topics_start = $start;
    } else {
        $topics_start = 0;
    }
    
    $count = 1;
    $next = $topics_start + $topics_per_page;
    $previous = $topics_start - $topics_per_page;
    $l_phpbb_nextpage = "";

    if($forum['forum_topics'] > $topics_per_page) {
        // more topcs than we want to see
        $l_phpbb_nextpage = "<span class=\"pn-sub\">";
        for($x = 0; $x < $forum['forum_topics']; $x++) {
            if(($previous >= 0) and ($count == 1)) {
                $l_phpbb_nextpage .=  "<a href=\"". pnModURL('pnForum', 'user', 'viewforum', array( 'forum'=>$forum['forum_id'], 'start' => $previous))."\">".pnVarPrepForDisplay(_PNFORUM_PREVPAGE).'</a>';
                //$l_phpbb_nextpage .= " | ";
            }
            if(!($x % $topics_per_page)) {
                if($x > 0) {
                    //$l_phpbb_nextpage .= " | ";
                }
                if($x == $topics_start) {
                    $l_phpbb_nextpage .=  "| $count\n";
    
                } else {
                    if ( (($count%10)==0) // link if page is multiple of 10 
                    || ($count==1) // link first page 
                    || (($x > ($start-6*$topics_per_page)) //link -5 and +5 pages 
                    &&($x < ($start+6*$topics_per_page))) ) {
                        $l_phpbb_nextpage .=  " | <a href=\"".pnModURL('pnForum', 'user', 'viewforum', array('forum'=>$forum['forum_id'],'start'=>$x))."\">$count</a>\n";
                    }
                }
                $count++;
            }
        }
        if($next < $forum['forum_topics']) {
            $l_phpbb_nextpage .=  " | <a href=\"".pnModURL('pnForum', 'user', 'viewforum', array('forum'=>$forum['forum_id'],'start'=>$next))."\">".pnVarPrepForDisplay(_PNFORUM_NEXTPAGE)."</a>";
        }
    
        $l_phpbb_nextpage .= "</span>";
        $l_phpbb_showGotopage = 1;
    }
    $forum['forum_pager'] = $l_phpbb_nextpage;
    
    $sql = "SELECT t.topic_id, 
                   t.topic_title, 
                   t.topic_views, 
                   t.topic_replies, 
                   t.sticky, 
                   t.topic_status,
                   t.topic_last_post_id, 
                   u.pn_uname, 
                   u2.pn_uname as last_poster, 
                   p.post_time
            FROM ".$pntable['pnforum_topics']." AS t
            LEFT JOIN ".$pntable['users']." AS u ON t.topic_poster = u.pn_uid
            LEFT JOIN ".$pntable['pnforum_posts']." AS p ON t.topic_last_post_id = p.post_id
            LEFT JOIN ".$pntable['users']." AS u2 ON p.poster_id = u2.pn_uid
            WHERE t.forum_id = '".(int)pnVarPrepForStore($forum_id)."'
            ORDER BY t.sticky DESC, p.post_time DESC";
//            ORDER BY t.sticky DESC, topic_time DESC";
    
    $result = pnfSelectLimit($dbconn, $sql, $topics_per_page, $start, __FILE__, __LINE__);
    $forum['forum_id'] = $forum_id;
    $forum['topics'] = array();
    while(!$result->EOF) {
        $topic = array();
        $topic = $result->GetRowAssoc(false);
        //$topic = $row;
        if ($topic['last_poster'] == "Anonymous") {$topic['last_poster'] = pnConfigGetVar('anonymous'); }
        if ($topic['pn_uname'] == "Anonymous") {$topic['pn_uname'] = pnConfigGetVar('anonymous'); }
        
        $posted_unixtime= strtotime ($topic['post_time']);
        $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($posted_unixtime));
        $topic['last_post'] = sprintf(_PNFORUM_LASTPOSTSTRING, pnVarPrepForDisplay($posted_ml), pnVarPrepForDisplay($topic['last_poster']));

        if($topic['topic_replies'] >= $hot_threshold) {
            // topic is hot
            if($topic['post_time'] < $last_visit) {
                // topic has no new posts
                $image = $hot_folder_image;
                $altimage = "hot_folder_image";
            } else {
                // topic has new posts
                $image = $hot_newposts_image;
                $altimage = "hot_newposts_image";
            }
        } else {
            // topic is normal
            if($topic['post_time'] < $last_visit) {
                // topic has no new posts
                $image = $folder_image;
                $altimage = "folder_image";
            } else {
                // topic has new posts
                $image = $newposts_image;
                $altimage = "newposts_image";
            }
        }
/*      
        if($topic['topic_status'] == 1) {
            $image = $locked_image;
            $altimage = "locked_image";
        }
*/        
        $topic['image'] = $image;
        $topic['altimage'] = $altimage;
        $topic['image_attr'] = getimagesize($image);
        // go to first new post
        $newest_post = "";
        if ($topic['post_time'] > $last_visit){
            $sql = "SELECT post_id
                    FROM ".$pntable['pnforum_posts']."
                    WHERE topic_id='".pnVarPrepForStore($topic['topic_id'])."'
                    ORDER BY post_time";
        
//            $newp = $dbconn->Execute($sql);
            $newp = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        
            if (!$newp->EOF) {
                $temp = $newp->GetRowAssoc(false);
                $topic['newest_postid'] = $temp['post_id'];
            }
            pnfCloseDB($newp);
        } else {
            $forum['newest_post'] = '';
        }
        
            // FIX ASAP
//            $newest_post = '';
        // pagination
        $pagination = "";
        if($topic['topic_replies']+1 > $posts_per_page) {
            $pagination .= "&nbsp;&nbsp;&nbsp;<span class=\"pn-sub\">(".pnVarPrepForDisplay(_PNFORUM_GOTOPAGE)."&nbsp;";
            $pagenr = 1;
            $skippages = 0;
            for($x = 0; $x < $topic['topic_replies'] + 1; $x += $posts_per_page) {
                $lastpage = (($x + $posts_per_page) >= $topic['topic_replies'] + 1);
        
                if($lastpage) {
                    $start = $x;
                } else {
                    if ($x != 0) {
                        $start = $x;
                    }
                }
        
                if($pagenr > 3 && $skippages != 1 && !$lastpage) {
                    $pagination .= ", ... ";
                    $skippages = 1;
                }
        
                if ($skippages != 1 || $lastpage) {
                    if ($x!=0) $pagination .= ", ";
                    $pagination .= "<a href=\"".pnModURL('pnForum', 'user', 'viewtopic', array('topic' => $topic['topic_id'], 'start' => $start))."\" title=\"" . $topic['topic_title'] . " " . pnVarPrepForDisplay(_PNFORUM_PAGE) . " $pagenr\">$pagenr</a>";
                }
        
                $pagenr++;
            }
            $pagination .= ")</span>";
        }
        $topic['pagination'] = $pagination;
        
        array_push( $forum['topics'], $topic );
        $result->MoveNext(); 
    }
    pnfCloseDB($result);

    $topics_start = $start;


    return $forum;
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
 *@returns very complex array, see <!--[ debug ]--> for more information
 */
function pnForum_userapi_readtopic($args)
{
    extract($args);
    unset($args);

    $posts_per_page = pnModGetVar('pnForum', 'posts_per_page');
    $topics_per_page = pnModGetVar('pnForum', 'topics_per_page');
    $posticon = pnModGetVar('pnForum', 'posticon');
    $post_sort_order = pnModAPIFunc('pnForum','user','get_user_post_order'); 

    $complete = (isset($complete)) ? $complete : false;

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT t.topic_title, 
                   t.topic_status, 
                   t.forum_id, 
                   t.sticky, 
                   t.topic_time,
                   t.topic_last_post_id,
                   f.forum_name, 
                   f.cat_id, 
                   c.cat_title
            FROM  ".$pntable['pnforum_topics']." t
            LEFT JOIN ".$pntable['pnforum_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['pnforum_categories']." AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $topic = array();
    if(!$result->EOF) {
        $topic = $result->GetRowAssoc(false);
        $topic['topic_id'] = $topic_id;
        $topic['start'] = $start;
        $topic['topic_unixtime'] = strtotime ($topic['topic_time']);
        $topic['post_sort_order'] = $post_sort_order;

        if(!allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id'])) {
            return showforumerror(_PNFORUM_NOAUTH_TOREAD, __FILE__, __LINE__);
        } 
/*
        $topic['access_comment'] = false;
        if(allowedtowritetocategoryandforum($topic['cat_id'], $topic['forum_id'])) {
            $topic['access_comment'] = true;
        } 
*/        
        $topic['forum_mods'] = pnForum_userapi_get_moderators(array('forum_id' => $topic['forum_id']));

        $topic['access_see']      = allowedtoseecategoryandforum($topic['cat_id'], $topic['forum_id']);
        $topic['access_read']     = $topic['access_see'] && allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id']);
        $topic['access_comment']  = $topic['access_read'] && allowedtowritetocategoryandforum($topic['cat_id'], $topic['forum_id']);
        $topic['access_moderate'] = $topic['access_comment'] && allowedtomoderatecategoryandforum($topic['cat_id'], $topic['forum_id']);
        $topic['access_admin']    = $topic['access_moderate'] && allowedtoadmincategoryandforum($topic['cat_id'], $topic['forum_id']);
        
        /**
         * update topic counter
         */
        $sql = "UPDATE ".$pntable['pnforum_topics']."
                SET topic_views = topic_views + 1
                WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

        /**
         * more then one page in this topic?
         */
        $topic['total_posts'] = pnForum_userapi_boardstats(array('id'=>$topic_id, 'type'=>"topic"));
        
        if($topic['total_posts'] > $posts_per_page) {
            $times = 0;
            for($x = 0; $x < $topic['total_posts']; $x += $posts_per_page) {
                $times++;
            }
            $topic['pages'] = $times;
        }
        /**
         * generate pager
         */
        $pager = "";
        if($topic['total_posts'] > $posts_per_page) {
            if (!isset($start)) { 
                $start=0;
            }
            $times = 1;
            $pager = "<span class=\"pn-sub\">".pnVarPrepForDisplay(_PNFORUM_GOTOPAGE)." ( ";
            $last_page = $start - $posts_per_page;
            if($start > 0) {
                $pager .= "<a href=\"" . pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id,'start'=>$last_page)) . "\">".pnVarPrepForDisplay(_PNFORUM_PREVPAGE).'</a> ';
            }
            for($x = 0; $x < $topic['total_posts']; $x += $posts_per_page) {
                if($times != 1) {
                    $pager .= " | ";
                }
                if($start && ($start == $x)) {
                    $pager .= $times;
                } else if($start == 0 && $x == 0) {
                    $pager .= "1";
                } else {
                    $pager .= "<a href=\"" . pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id,'start'=>$x)) . "\">$times</a>";
                }
                $times++;
            }
        
            if(($start + $posts_per_page) < $total) {
                $next_page = $start + $posts_per_page;
                $pager .= " <a href=\"" . pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id,'start'=>$next_page)) . "\">".pnVarPrepForDisplay(_PNFORUM_NEXTPAGE).'</a>';
            }
            $pager .= " ) </span><br />\n";
        }
        $topic['topic_pager'] = $pager;
        $topic['posts'] = array();

        // read posts
        $sql2 = "SELECT p.post_id, 
                        p.poster_id, 
                        p.post_time, 
                        pt.post_text
                FROM ".$pntable['pnforum_posts']." p,
                     ".$pntable['pnforum_posts_text']." pt
                WHERE p.topic_id = '".(int)pnVarPrepForStore($topic['topic_id'])."'
                AND p.post_id = pt.post_id
                ORDER BY p.post_id $post_sort_order";

        if($complete==true) {
            $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
        } elseif(isset($start)) {
            // $start is given
            $result2 = pnfSelectLimit($dbconn, $sql2, $posts_per_page, $start, __FILE__, __LINE__);
        } else {
            $result2 = pnfSelectLimit($dbconn, $sql2, $posts_per_page, false, __FILE__, __LINE__);
        }
        while(!$result2->EOF) {
            $row = $result2->GetRowAssoc(false);
            $post = array();
            $post['post_id']   = $row['post_id'];
            $post['poster_id'] = $row['poster_id'];
            $post['post_time'] = $row['post_time'];
            $post['post_text'] = $row['post_text'];
            
            // check if the user is still in the postnuke db
            $user_name = pnUserGetVar('uname', $post['poster_id']);
            if ($user_name == "") {
                // user deleted from the db?
                $post['poster_id'] = '1';
            }
            
            $post['poster_data'] = pnForum_userapi_get_userdata_from_id(array('userid' =>$post['poster_id']));


            $post['posted_unixtime'] = strtotime ($post['post_time']);
            $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($post['posted_unixtime']));
            // we use br2nl here for backwards compatibility
            //$message = phpbb_br2nl($message);
            $post['post_text'] = phpbb_br2nl($post['post_text']);
            $post['post_text'] = pnForum_replacesignature($post['post_text'], $post['poster_data']['pn_user_sig']);

            // call hooks for $message
            list($post['post_text']) = pnModCallHooks('item', 'transform', '', array($post['post_text']));
            $post['post_text'] = pnVarPrepHTMLDisplay(pnVarCensor(nl2br($post['post_text'])));

            $pn_uid = pnUserGetVar('uid');
            if ($post['poster_data']['pn_uid']==$pn_uid) {
                // user is allowed to moderate || own post
                $post['poster_data']['edit'] = true;
            }
            if(allowedtowritetocategoryandforum($topic['cat_id'], $topic['forum_id'])) {
                // user is allowed to reply
                $post['poster_data']['reply'] = true;
            }

            if(allowedtomoderatecategoryandforum($topic['cat_id'], $topic['forum_id']) &&
                pnModGetVar('pnForum', 'log_ip') == "yes") {
                // user is allowed to see ip
                $post['poster_data']['seeip'] = true;
            }
            if(allowedtomoderatecategoryandforum($topic['cat_id'], $topic['forum_id'])) {
                // user is allowed to moderate
                $post['poster_data']['moderate'] = true;
                $post['poster_data']['edit'] = true;
            }
            array_push($topic['posts'], $post);
            $result2->MoveNext();
        }
        pnfCloseDB($result2);
    } else {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    }
    pnfCloseDB($result);
    
    return $topic;
}

/** 
 * preparereply
 * prepapare a reply to a posting by reading the last ten postign in revers order for review
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['post_id'] int the post id to reply to
 *@params $args['quote'] bool if user wants to qupte or not
 *@params $args['last_visit'] string the users last visit data
 *@returns very complex array, see <!--[ debug ]--> for more information
 */
function pnForum_userapi_preparereply($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $reply = array();

    if($post_id<>0) {
        // We have a post id, so include that in the checks
        // create a reply with quote
        $sql = "SELECT f.forum_id,
                       f.cat_id,
                       t.topic_id,
                       t.topic_title,
                       t.topic_status,
                       pt.post_text,
                       p.post_time,
                       u.pn_uname
                FROM ".$pntable['pnforum_forums']." AS f, 
                     ".$pntable['pnforum_topics']." AS t, 
                     ".$pntable['pnforum_posts']." AS p,
                     ".$pntable['pnforum_posts_text']." AS pt,
                     ".$pntable['users']." AS u
                WHERE (p.post_id = '".(int)pnVarPrepForStore($post_id)."')
                AND (t.forum_id = f.forum_id)
                AND (p.topic_id = t.topic_id)
                AND (pt.post_id = p.post_id)
                AND (p.poster_id = u.pn_uid)";
    } else {
        // No post id, just check topic.
        // reply without quote
        $sql = "SELECT f.forum_id,
                       f.cat_id,
                       t.topic_id,
                       t.topic_title,
                       t.topic_status
                FROM ".$pntable['pnforum_forums']." AS f, 
                     ".$pntable['pnforum_topics']." AS t
                WHERE (t.topic_id = '".(int)pnVarPrepForStore($topic_id)."')
                AND (t.forum_id = f.forum_id)";
    }
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if ($result->EOF) {
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);
    
    $reply['forum_id'] = pnVarPrepForDisplay($myrow['forum_id']);
    $reply['cat_id'] = pnVarPrepForDisplay($myrow['cat_id']);
    $reply['topic_subject'] = pnVarPrepForDisplay($myrow['topic_title']);
    $reply['topic_status'] = pnVarPrepForDisplay($myrow['topic_status']);
    $reply['topic_id'] = pnVarPrepForDisplay($myrow['topic_id']);
    // the next line is only producing a valid result, if we get a post_id which
    // means we are producing a reply with quote
    if(array_key_exists("post_text", $myrow)) {
        $text = pnForum_bbdecode($myrow['post_text']);
        $text = preg_replace('/(<br[ \/]*?>)/i', "", $text);
        // just for backwards compatibility
        $text = pnForum_undo_make_clickable($text);
        $text = str_replace("[addsig]", "", $text);
        $reply['message'] = '[quote='.$myrow['pn_uname'].']'.$text.'[/quote]';
    } else {
        $reply['message'] = "";
    }

    // anonymous user has uid=0, but needs pn_uid=1
    if(!pnUserLoggedin()) {
        $pn_uid = 1;
    } else {
        $pn_uid = pnUserGetVar('uid');
    }
    $reply['poster_data'] = pnForum_userapi_get_userdata_from_id(array('userid'=>$pn_uid));
    
    if($reply['topic_status']==1) {
        return showforumerror(_PNFORUM_NOPOSTLOCK, __FILE__, __LINE__);
    }
    
    if(!allowedtowritetocategoryandforum($reply['cat_id'], $reply['forum_id'])) {
        return showforumerror( _PNFORUM_NOAUTH_TOWRITE, __FILE__, __LINE__);
    }

    // Topic review (show last 10)
    $sql = "SELECT p.poster_id, 
                   p.post_time, 
                   pt.post_text, 
                   t.topic_title
                    FROM $pntable[pnforum_posts_text] pt, $pntable[pnforum_posts] p
                        LEFT JOIN $pntable[pnforum_topics] t ON t.topic_id=p.topic_id
                        WHERE p.topic_id = '".$reply['topic_id']."' AND p.post_id = pt.post_id
                        ORDER BY p.post_id DESC";

    $result = pnfSelectLimit($dbconn, $sql, 10, false, __FILE__, __LINE__);
    $reply['topic_review'] = array();
    while(!$result->EOF) {
        $review = array();
        $row = $result->GetRowAssoc(false);
        $review = $row;            
        $review['user_name'] = pnUserGetVar('uname', $review['poster_id']);
        if ($review['user_name'] == "") {
            // user deleted from the db?
            $review['poster_id'] = 1;
        }
    
        $review['poster_data'] = pnForum_userapi_get_userdata_from_id(array('userid'=>$review['poster_id']));
    
        // TODO extract unixtime directly from MySql
        $review['post_unixtime'] = strtotime ($review['post_time']);
        $review['post_ml'] = ml_ftime(_DATETIMEBRIEF, GetUserTime($review['post_unixtime']));
    
        $message = $review['post_text'];
        // we use br2nl here for backward compatibility
        $message = phpbb_br2nl($message);
        // Before we insert the sig, we have to strip its HTML if HTML is disabled by the admin.
    
        // We do this _before_ pn_bbencode(), otherwise we'd kill the bbcode's html.
        $message = pnForum_replacesignature($message, $review['poster_data']['pn_user_sig']);

        // call hooks for $message
        list($message) = pnModCallHooks('item', 'transform', '', array($message));
        $review['post_text'] = $message;
        
        array_push($reply['topic_review'], $review);
        $result->MoveNext();
    }
    pnfCloseDB($result);
    return $reply;
}

/**
 * storereply
 * store the users reply in the database
 *
 *@params $args['message'] string the text
 *@params $args['topic_id'] int the topics id
 *@params $args['forum_id'] int the forums id
 *@params $args['attach_signature'] int 1=yes, 0=no
 *@params $args['subscribe_topic'] int 1=yes, 0=no
 *@returns int the number of the posting to show in the topic affter adding 
 */
function pnForum_userapi_storereply($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    if(trim($message) == '') {
        return showforumerror(_PNFORUM_EMPTYMSG, __FILE__, __LINE__);
    }

    /*
    it's a submitted page and message is not empty
    */

    // grab message for notification
    // without html-specialchars, bbcode, smilies <br> and [addsig]
    $posted_message=stripslashes($message);

    // signature is always on, except anonymous user
    // anonymous user has uid=0, but needs pn_uid=1
    if(pnUserLoggedin()) {
        if($attach_signature==1) {
            $message .= "[addsig]";
        }
        $pn_uid = pnUserGetVar('uid');
    } else {
        $pn_uid = 1;
    }

    if (pnModGetVar('pnForum', 'log_ip') == "no") {
        // for privavy issues ip logging can be deactivated
        $poster_ip = "127.0.0.1";
    } else {
        // some enviroment for logging ;)
        if (getenv(HTTP_X_FORWARDED_FOR)){ 
            $poster_ip=getenv(HTTP_X_FORWARDED_FOR); 
        } else { 
            $poster_ip=getenv(REMOTE_ADDR);
        }
    }

    // read forum_id from topic_id
    $sql = "SELECT f.forum_id
            FROM ".$pntable[pnforum_forums]." AS f, 
                 ".$pntable[pnforum_topics]." AS t
            WHERE (t.topic_id = '".(int)pnVarPrepForStore($topic_id)."')
            AND (t.forum_id = f.forum_id)";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if ($result->EOF) {
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);
    $forum_id = pnVarPrepForStore($myrow['forum_id']);

    // Prep for DB
    $time = date("Y-m-d H:i");
    $topic_id = pnVarPrepForStore($topic_id);
    $message = pnVarPrepForStore($message);
    $pn_uid = pnVarPrepForStore($pn_uid);
    $time = pnVarPrepForStore($time);
    $poster_ip = pnVarPrepForStore($poster_ip);

    // insert values into posts-table
    $postid = $dbconn->GenID($pntable['pnforum_posts']);
    $sql = "INSERT INTO $pntable[pnforum_posts]
                        (post_id, topic_id, forum_id, poster_id, post_time, poster_ip)
                        VALUES
                        ('".pnVarPrepForStore($postid)."', '$topic_id', '$forum_id', '$pn_uid','$time', '$poster_ip')";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    $this_post = $dbconn->PO_Insert_ID($pntable['pnforum_posts'], 'post_id');
    if($this_post) {
        $sql = "INSERT INTO $pntable[pnforum_posts_text]
                (post_id, post_text)
                VALUES
                ('".pnVarPrepForStore($this_post)."', '$message')";

        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);
    }

    // update topics-table
    $sql = "UPDATE $pntable[pnforum_topics]
            SET topic_replies = topic_replies+1, topic_last_post_id = '".pnVarPrepForStore($this_post)."', topic_time = '$time'
            WHERE topic_id = '$topic_id'";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    if(pnUserLoggedIn()) {
        // user logged in we have to update users-table
        $sql = "UPDATE $pntable[pnforum_users]
                SET user_posts=user_posts+1
                WHERE (user_id = $pn_uid)";

        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);

        // update subscription
        if($subscribe_topic==1) {
            // user wants to subscribe the topic
            pnForum_userapi_subscribe_topic(array('topic_id'=>$topic_id));
        } else {
            // user wants not to subscribe the topic
            pnForum_userapi_unsubscribe_topic(array('topic_id'=>$topic_id, 
                                                    'silent'  => true));     
        }
    }

    // update forums-table
    $sql = "UPDATE $pntable[pnforum_forums]
            SET forum_posts = forum_posts+1, forum_last_post_id = '" . pnVarPrepForStore($this_post) . "'
            WHERE forum_id = '$forum_id'";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    pnForum_userapi_notify_by_email(array('topic_id'=>$topic_id, 'poster_id'=>$pn_uid, 'post_message'=>$posted_message, 'type'=>'2'));
    
    // get topic_replies for correct redirect
    $sql = "SELECT topic_replies FROM $pntable[pnforum_topics] WHERE topic_id = '$topic_id'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    list ($topic_replies) = $result->fields;
    pnfCloseDB($result);

    // get some enviroment
    $posts_per_page = pnModGetVar('pnForum', 'posts_per_page');
    $post_sort_order = pnModGetVar('pnForum', 'post_sort_order');

    if ($post_sort_order == "ASC") {
        $hc_dlink_times = 0;
        if (($topic_replies+1-$posts_per_page)>= 0) { 
            $hc_dlink_times = 0; 
            for ($x = 0; $x < $topic_replies+1-$posts_per_page; $x+= $posts_per_page) 
            $hc_dlink_times++; 
        } 
        $start = $hc_dlink_times*$posts_per_page;
    } else {
        // latest topic is on top anyway...
        $start = 0;
    }
    return $start;
} 

/**
 * get_topic_subscription_status
 *
 *@params $args['userid'] int the users pn_uid
 *@params $args['topic_id'] int the topic id
 *@returns bool true if the user is subscribed or false if not
 */
function pnForum_userapi_get_topic_subscription_status($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT user_id from ".$pntable['pnforum_topic_subscription']." 
            WHERE user_id = '".(int)pnVarPrepForStore($userid)."' AND topic_id = '".(int)pnVarPrepForStore($topic_id)."'";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    $rc = $result->RecordCount();
    pnfCloseDB($result);

    if($rc>0) {
        return true;
    } else {
        return false;
    }
}

/**
 * get_forum_subscription_status
 *
 *@params $args['userid'] int the users pn_uid
 *@params $args['forum_id'] int the forums id
 *@returns bool true if the user is subscribed or false if not
 */
function pnForum_userapi_get_forum_subscription_status($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT user_id from ".$pntable['pnforum_subscription']." 
            WHERE user_id = '".(int)pnVarPrepForStore($userid)."' AND forum_id = '".(int)pnVarPrepForStore($forum_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    $rc = $result->RecordCount();
    pnfCloseDB($result);

    if($rc>0) {
        return true;
    } else {
        return false;
    }
}

/**
 * get_forum_favorites_status
 *
 *@params $args['userid'] int the users pn_uid
 *@params $args['forum_id'] int the forums id
 *@returns bool true if the user is subscribed or false if not
 */
function pnForum_userapi_get_forum_favorites_status($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT user_id from ".$pntable['pnforum_forum_favorites']." 
            WHERE user_id = '".(int)pnVarPrepForStore($userid)."' AND forum_id = '".(int)pnVarPrepForStore($forum_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    $rc = $result->RecordCount();
    pnfCloseDB($result);

    if($rc>0) {
        return true;
    } else {
        return false;
    }
}

/**
 * preparenewtopic
 *
 *@params $args['message'] string the text (only set when preview is selected)
 *@params $args['subject'] string the subject (only set when preview is selected)
 *@params $args['forum_id'] int the forums id
 *@returns array with information....
 */
function pnForum_userapi_preparenewtopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $newtopic = array();
    $newtopic['forum_id'] = $forum_id;
    
    // select forum name and cat title based on forum_id
    $sql = "SELECT f.forum_name,
                   c.cat_id, 
                   c.cat_title
            FROM ".$pntable['pnforum_forums']." AS f,
                ".$pntable['pnforum_categories']." AS c
            WHERE (forum_id = '".(int)pnVarPrepForStore($forum_id)."'
            AND f.cat_id=c.cat_id)";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    $myrow = $result->GetRowAssoc(false);
    pnfCloseDB($result);

    $newtopic['cat_id']     = $myrow['cat_id'];
    $newtopic['forum_name'] = pnVarPrepForDisplay($myrow['forum_name']);
    $newtopic['cat_title']  = pnVarPrepForDisplay($myrow['cat_title']);

    // need at least "comment" to add newtopic
    if(!allowedtowritetocategoryandforum($newtopic['cat_id'], $newtopic['forum_id'])) {
        // user is not allowed to post
        return showforumerror(_PNFORUM_NOAUTH_TOWRITE, __FILE__, __LINE__);
    }
    $newtopic['poster_data'] = pnForum_userapi_get_userdata_from_id(array('userid' => pnUserGetVar('uid')));

    $newtopic['subject'] = $subject;
    $newtopic['message'] = $message;
    $newtopic['message_display'] = phpbb_br2nl($message);

    list($newtopic['message_display']) = pnModCallHooks('item', 'transform', '', array($newtopic['message_display']));
    $newtopic['message_display'] = nl2br($newtopic['message_display']);

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
 *@params $args['no_sig'] bool (optional) if true we do not add a signature
 *@returns int the new topics id
 */
function pnForum_userapi_storenewtopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    // it's a submitted page
    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
    }

    if(trim($message)=='' || trim($subject) == '') {
        // either message or subject is empty
        return showforumerror(_PNFORUM_EMPTYMSG, __FILE__, __LINE__);
    }

    /*
    it's a submitted page and message and subject are not empty
    */
    
    //  do a censor on subject and message
    // $subject = censor($subject);
    // $message = censor($message);
    
    //  grab message for notification 
    //  without html-specialchars, bbcode, smilies <br /> and [addsig]
    $posted_message=stripslashes($message);
    
    //  signature is always on, except anonymous user
    //  anonymous user has uid=0, but needs pn_uid=1
    if(pnUserLoggedin()) {
        if(is_bool($no_sig) && ($no_sig==true)){
            // do nothing
        } else {
            // add signature
            $message .= "[addsig]";
        }
        $pn_uid = pnUserGetVar('uid');
    } else  {
        $pn_uid = 1;
    }

    // some enviroment for logging ;)
    if (getenv(HTTP_X_FORWARDED_FOR)){ 
        $poster_ip=getenv(HTTP_X_FORWARDED_FOR); 
    } else { 
        $poster_ip=getenv(REMOTE_ADDR);
    }
    // for privavy issues ip logging can be deactivated
    if (pnModGetVar('pnForum', 'log_ip') == "no") {
        $poster_ip = "127.0.0.1";
    }
    
    $time = (isset($time)) ? $time : date("Y-m-d H:i");

    // Prep for DB
    $subject   = pnVarPrepForStore($subject);
    $message   = pnVarPrepForStore($message);
    $pn_uid    = pnVarPrepForStore($pn_uid);
    $forum_id  = pnVarPrepForStore($forum_id);
    $time      = pnVarPrepForStore($time);
    $poster_ip = pnVarPrepForStore($poster_ip);

    //  insert values into topics-table
    $topic_id = $dbconn->GenID($pntable['pnforum_topics']);
    $sql = "INSERT INTO ".$pntable['pnforum_topics']." 
            (topic_id, topic_title, topic_poster, forum_id, topic_time, topic_notify) 
            VALUES 
            ('".pnVarPrepForStore($topic_id)."','$subject', '$pn_uid', '$forum_id', '$time', '' )";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    //  insert values into posts-table   
    $topic_id = $dbconn->PO_Insert_ID($pntable['pnforum_topics'], 'topic_id');
    
    $post_id = $dbconn->GenID($pntable['pnforum_posts']);
    $sql = "INSERT INTO ".$pntable['pnforum_posts']." 
            (post_id, topic_id, forum_id, poster_id, post_time, poster_ip) 
            VALUES 
            ('".pnVarPrepForStore($post_id)."', '".pnVarPrepForStore($topic_id)."', '$forum_id', '$pn_uid', '$time', '$poster_ip')";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    $post_id = $dbconn->PO_Insert_ID($pntable['pnforum_posts'], 'post_id');
    if($post_id)
    {
        //  insert values into posts_text-table
        $sql = "INSERT INTO ".$pntable['pnforum_posts_text']." 
                (post_id, post_text) 
                VALUES ('".pnVarPrepForStore($post_id)."', '$message')";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);

        //  updates topics-table
        $sql = "UPDATE ".$pntable['pnforum_topics']." 
                SET topic_last_post_id = '".pnVarPrepForStore($post_id)."' 
                WHERE topic_id = '".pnVarPrepForStore($topic_id)."'";

        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
    }

    if(pnUserLoggedin()) {
        // user logged in we have to update users-table
        $sql = "UPDATE $pntable[pnforum_users] 
                SET user_posts=user_posts+1 
                WHERE (user_id = $pn_uid)";

        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
    }
    //  update forums-table
    $sql = "UPDATE $pntable[pnforum_forums] 
            SET forum_posts = forum_posts+1, forum_topics = forum_topics+1, forum_last_post_id = '" . pnVarPrepForStore($post_id) . "' 
            WHERE forum_id = '$forum_id'";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);

    //  notify for newtopic
    pnForum_userapi_notify_by_email(array('topic_id'=>$topic_id, 'poster_id'=>$pn_uid, 'post_message'=>$posted_message, 'type'=>'0'));
    //  switch to topic display
    return $topic_id;
}

/**
 * readpost
 * reads a single posting
 *
 *@params $args['post_id'] int the postings id
 *@returns array with posting information...
 */
function pnForum_userapi_readpost($args)
{    
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();
  
    // we know about the post_id, let's find out the forum and catgeory name for permission checks
    $sql = "SELECT p.post_id, 
                    p.post_time, 
                    pt.post_text, 
                    p.poster_id, 
                    t.topic_id,
                    t.topic_title, 
                    t.topic_notify,
                    t.topic_replies,
                    f.forum_id,
                    f.forum_name, 
                    c.cat_title,
                    c.cat_id
            FROM ".$pntable['pnforum_posts']." p
            LEFT JOIN ".$pntable['pnforum_topics']." t ON t.topic_id = p.topic_id
            LEFT JOIN ".$pntable['pnforum_posts_text']." pt ON pt.post_id = p.post_id
            LEFT JOIN ".$pntable['pnforum_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['pnforum_categories']." c ON c.cat_id = f.cat_id
            WHERE (p.post_id = '".(int)pnVarPrepForStore($post_id)."')"; 

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);

    $post = array();
    $post['post_id']      = pnVarPrepForDisplay($myrow['post_id']);
    $post['post_time']    = pnVarPrepForDisplay($myrow['post_time']);
    $message              = $myrow['post_text'];
    $post['topic_id']     = pnVarPrepForDisplay($myrow['topic_id']);
    $post['topic_subject']= pnVarPrepForDisplay($myrow['topic_title']);
    $post['topic_notify'] = pnVarPrepForDisplay($myrow['topic_notify']);
    $post['topic_replies']= pnVarPrepForDisplay($myrow['topic_replies']);
    $post['forum_id']     = pnVarPrepForDisplay($myrow['forum_id']);
    $post['forum_name']   = pnVarPrepForDisplay($myrow['forum_name']);
    $post['cat_title']    = pnVarPrepForDisplay($myrow['cat_title']);
    $post['cat_id']       = pnVarPrepForDisplay($myrow['cat_id']);
    $post['poster_data'] = pnForum_userapi_get_userdata_from_id(array('userid' => $myrow['poster_id']));
    // create unix timestamp
    $post['post_unixtime'] = strtotime ($post['post_time']);

    if(!allowedtoreadcategoryandforum($post['cat_id'], $post['forum_id'])) {
        return showforumerror(_PNFORUM_NOAUTH_TOREAD, __FILE__, __LINE__);
    }
    
    $pn_uid = pnUserGetVar('uid');   
    $post['moderate'] = false;
    if(allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) { 
        $post['moderate'] = true; 
    }

    $post['post_textdisplay'] = phpbb_br2nl($message);
    $post['post_textdisplay'] = pnForum_replacesignature($post['post_textdisplay'], $post['poster_data']['pn_user_sig']);

    // call hooks for $message_display ($message remains untouched for the textarea)
    list($post['post_textdisplay']) = pnModCallHooks('item', 'transform', '', array($post['post_textdisplay']));
    $post['post_textdisplay'] = pnVarPrepHTMLDisplay(pnVarCensor(nl2br($post['post_textdisplay'])));

    //$message = pnVarPrepForDisplay($message);
    //  remove [addsig]
    $message = eregi_replace("\[addsig]$", "", $message);
    //  remove <!-- editby -->
    $message = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $message);
    //  convert <br /> to \n (since nl2br only inserts additional <br /> we just need to remove them
    //$message = eregi_replace('<br />', "", $message);
    $message = phpbb_br2nl($message);
    //  convert bbcode (just for backwards compatibility)
    $message = pnForum_bbdecode($message);
    //  convert autolinks (just for backwards compatibility)
    $message = pnForum_undo_make_clickable($message);
    $post['post_text'] = $message;

    // allow to edit the subject if first post
    $post['first_post'] = pnForum_userapi_is_first_post(array('topic_id' => $post['topic_id'], 'post_id' => $post_id));

    return $post;
}   

/**
 * Check if this is the first post in a topic. 
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['post_id'] int the postings id
 *@returns boolean
 */
function pnForum_userapi_is_first_post($args)
{
    //topic_id, $post_id
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT post_id FROM ".$pntable['pnforum_posts']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."' 
            ORDER BY post_id 
            LIMIT 1";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    if($result->RecordCount()>0) {
        list($read_post_id) = $result->fields;
        pnfCloseDB($result);
        if($post_id == $read_post_id) {
            return true;
        }
    }
    pnfCloseDB($result);
    return false;
}

/**
 * update post
 * updates a posting in the db after editing it
 *
 *@params $args['post_id'] int the postings id
 *@params $args['subject'] string the subject
 *@params $args['message'] string the text
 *@params $args['delete'] boolean true if the posting is to be deleted
 *@returns string url to redirect to after action (topic of forum if the (last) posting has been deleted)
 */
function pnForum_userapi_updatepost($args)
{
    extract($args);
    unset($args);

    /**
     * Confirm authorisation code
     */
    if (!pnSecConfirmAuthKey()) {
        return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
    }

    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 

    list($dbconn, $pntable) = pnfOpenDB();

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
            FROM  ".$pntable['pnforum_posts']." as p,
                  ".$pntable['pnforum_topics']." as t,
                  ".$pntable['pnforum_forums']." as f
            WHERE (p.post_id = '".(int)pnVarPrepForStore($post_id)."')
              AND (t.topic_id = p.topic_id)
              AND (f.forum_id = p.forum_id)";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);
    extract($myrow);

    $pn_uid = pnUserGetVar('uid');

    if (!($pn_uid == $poster_id) && 
        !allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        // user is not allowed to edit post
        return showforumerror( _PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }
    
    if(($topic_status == 1) && 
        !allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        // topic is locked, user is not moderator
        return showforumerror( _PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }

    if(trim($message) == '') {
        // no message
        return showforumerror( _PNFORUM_EMPTYMSG, __FILE__, __LINE__);
    }

    if (empty($delete)) {
        //
        // update the posting
        //
        if(!allowedtoadmincategoryandforum($cat_id, $forum_id)) {
            // if not admin then add a edited by line
            // If it's been edited more than once, there might be old "edited by" strings with
            // escaped HTML code in them. We want to fix this up right here:
            $message = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $message);
            // who is editing?
            if(pnUserLoggedIn()) {
                $editname = pnUserGetVar('uname');
            } else {
                $editname = pnConfigGetVar('anonymous');
            }
            $edit_date = ml_ftime(_DATETIMEBRIEF, GetUserTime(time()));
            $message .= "<br /><br /><!-- editby --><br /><br /><em>"._PNFORUM_EDITBY." $editname, $edit_date</em><!-- end editby --> ";
        }
    
        // add signature placeholder
        if ($poster_id <> 1){
            $message .= "[addsig]";
        }
        $message = pnVarPrepForStore($message);

        $sql = "UPDATE ".$pntable['pnforum_posts_text']." 
                SET post_text = '$message' 
                WHERE (post_id = '".(int)pnVarPrepForStore($post_id)."')";
        
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);

        if (!empty ($subject)) { 
            //  topic has a new subject
            if (trim($subject) != '') {
                $subject = pnVarPrepForStore(pnVarCensor($subject));
                $sql = "UPDATE ".$pntable['pnforum_topics']." 
                        SET topic_title = '$subject' 
                        WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
                
                $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
                pnfCloseDB($result);
            }
        }
        // update done, return now
        return pnModURL('pnForum', 'user', 'viewtopic',
                        array('topic' => $topic_id /*,
                              'start' => $start*/) ); 

    } else {
        //
        // we are going to delete this posting
        //

        // delete the post from the posts table
        $sql = "DELETE FROM ".$pntable['pnforum_posts']." 
                WHERE post_id = '".(int)pnVarPrepForStore($post_id)."'";
        
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
        
        // delete the post from the posts_text table
        $sql = "DELETE FROM ".$pntable['pnforum_posts_text']." 
                WHERE post_id = '".(int)pnVarPrepForStore($post_id)."'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
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
        if($post_id <> $topic_last_post_id) {

            // the deleted posting was not the last one
            // adjust the users post count
            pnForum_userapi_update_user_post_count(array('user_id' => $poster_id, 'mode' => 'dec'));
            
            //
            // adjust the post counter in the forum, topic counter and last_post_id not changed
            //
            $sql = "UPDATE ".$pntable['pnforum_forums']." 
                    SET forum_posts=forum_posts - 1
                    WHERE forum_id = '".(int)pnVarPrepForStore($forum_id)."'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
            pnfCloseDB($result);
            
            //
            //  adjust the topic_replies
            //
            $sql = "UPDATE ".$pntable['pnforum_topics']." 
                    SET topic_replies=topic_replies-1 
                    WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
            pnfCloseDB($result);
            
            //
            // no more actions necessary, just return to the topic and show the last page
            // after removing the posting the $topic_replies now contains the total number
            // posts in this topic :-)
            // $topic_replies - one deleted post + one initial post 
            // get some enviroment
            $posts_per_page = pnModGetVar('pnForum', 'posts_per_page');
            $times = 0;
            if (($topic_replies-$posts_per_page)>= 0) { 
                for ($x = 0; $x < $topic_replies-$posts_per_page; $x+= $posts_per_page) {
                    $times++; 
                }
            } 
            $start = $times * $posts_per_page;
            return pnModURL('pnForum', 'user', 'viewtopic',
                            array('topic' => $topic_id,
                                  'start' => $start)); 
        } else { 
            //  
            // check if this was the last post in the topic, if yes, remove topic
            //
            $last_topic_post = pnForum_userapi_get_last_post_in_topic(array('topic_id' => $topic_id));
            $forum_last_post_id = pnForum_userapi_get_last_post_in_forum(array('forum_id' => $forum_id, 'id_only' => true));
            if($last_topic_post == false) {
                //
                // it was the last post in the thread, remove topic
                //
                $sql = "DELETE FROM ".$pntable['pnforum_topics']." 
                        WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
                $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
                pnfCloseDB($result);
                
                //
                // adjust the users post counter
                //
                pnForum_userapi_update_user_post_count(array('user_id' => $poster_id, 'mode' => 'dec'));
                
                //
                // adjust the post and topic counter and forum_last_post_id in the forum
                //
                $sql = "UPDATE ".$pntable['pnforum_forums']." 
                        SET forum_topics=forum_topics - 1, 
                            forum_posts=forum_posts - 1,
                            forum_last_post_id = '".(int)pnVarPrepForDisplay($forum_last_post_id)."' 
                        WHERE forum_id = '".(int)pnVarPrepForStore($forum_id)."'";
                $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
                pnfCloseDB($result);
                
                //
                // ready to return
                //
                return pnModURL('pnForum', 'user', 'viewforum',
                                array('forum' => $forum_id)); 
            } else {
                //
                // there is at least one posting in this topic
                // $post contains the data of the last posting
                //
                $lastposttime = date("Y-m-d H:i", $last_topic_post['post_unixtime']);
                $sql = "UPDATE ".$pntable['pnforum_topics']." 
                        SET topic_time = '".pnVarPrepForStore($lastposttime)."',
                            topic_last_post_id = '".(int)pnVarPrepForStore($last_topic_post['post_id'])."',
                            topic_replies=topic_replies-1 
                        WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
                $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
                pnfCloseDB($result);

                //
                // adjust the users post counter
                //
                pnForum_userapi_update_user_post_count(array('user_id' => $poster_id, 'mode' => 'dec'));

                //
                // adjust the post counter in the forum, topi counter not changed
                //
                $sql = "UPDATE ".$pntable['pnforum_forums']." 
                        SET forum_posts=forum_posts - 1,
                            forum_last_post_id = '".(int)pnVarPrepForDisplay($forum_last_post_id)."' 
                        WHERE forum_id = '".(int)pnVarPrepForStore($forum_id)."'";
                $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
                pnfCloseDB($result);

                //
                // after removing the posting the $topic_replies now contains the total number
                // posts in this topic :-)
                // $topic_replies - one deleted post + one initial post 
                // get some enviroment
                $posts_per_page = pnModGetVar('pnForum', 'posts_per_page');
                $times = 0;
                if (($topic_replies-$posts_per_page)>= 0) { 
                    for ($x = 0; $x < $topic_replies-$posts_per_page; $x+= $posts_per_page) {
                        $times++; 
                    }
                } 
                $start = $times * $posts_per_page;
                return pnModURL('pnForum', 'user', 'viewtopic',
                                array('topic' => $topic_id,
                                      'start' => $start)); 
            }
        }
    }
    // we should not get here, but who knows...
    return pnModURL('pnForum', 'user', 'main');
}
 
/**
 * Returns the most recent post in a forum, or a topic
 *
 * What does this function really do???
 *
 *@params $args['id'] int the id, defined by 'type' parameter
 *@params $args['type'] string, either topic of timefix 
 *returns ???
 */
function pnForum_userapi_get_last_boardpost($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT p.post_time, u.pn_uname 
            FROM ".$pntable['pnforum_posts']." p, ".$pntable['users']." u 
            WHERE p.topic_id = '".(int)pnVarPrepForStore($id)."' 
            AND p.poster_id = u.pn_uid 
            ORDER BY post_time DESC";

    $result = pnfSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
    $row = $result->GetRowAssoc(false);
    pnfCloseDB($result);
    $uname = $row['pn_uname'];
    $post_time = $row['post_time'];
    
    // format the return string
    switch($type) {
        case 'topic':
            $userlink = "<a href=\"user.php?op=userinfo&amp;uname=".$uname."\">".$uname."</a>";
            // correct the time
            $posted_unixtime= strtotime ($post_time);
            $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($posted_unixtime));
            $val = "<td><span class=\"pn-normal\">$userlink</span></td><td><span class=\"pn-normal\">$posted_ml</span></td>";
            break;
        case 'time_fix':
            $val = $post_time;
            break;
        default: 
            $val = false;
    }
    return($val);   
}
 
/** 
 * get_viewip_data
 *
 *@params $args['post_id] int the postings id
 *@returns array with informstion ...
 */
function pnForum_userapi_get_viewip_data($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $viewip = array();
    
    $sql = "SELECT u.pn_uname, p.poster_ip 
            FROM ".$pntable['users']." u, ".$pntable['pnforum_posts']." p 
            WHERE p.post_id = '".(int)pnVarPrepForStore($post_id)."'  
            AND u.pn_uid = p.poster_id";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    if($result->EOF) {
        // TODO we have valid user here, but he didn't has posts
        return showforumerror(_PNFORUM_NOUSER_OR_POST, __FILE__, __LINE__);
    } else {
        $row = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);
    $viewip['poster_ip']   = $row['poster_ip'];
    $viewip['poster_host'] = gethostbyaddr($row['poster_ip']);

    $sql = "SELECT pn_uid, pn_uname, count(*) AS postcount 
            FROM ".$pntable['pnforum_posts']." p, ".$pntable['users']." u 
            WHERE poster_ip='".$m['poster_ip']."' && p.poster_id = u.pn_uid 
            GROUP BY pn_uid";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
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
    pnfCloseDB($result);
    return $viewip;
}

/**
 * lockunlocktopic
 * 
 *@params $args['topic_id'] int the topics id
 *@returns void
 */    
function pnForum_userapi_lockunlocktopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    list($forum_id, $cat_id) = pnForum_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id'=>$topic_id));
    if(!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        return showforumerror(_PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }

    $new_status = ($mode=="lock") ? 1 : 0; 

    $sql = "UPDATE ".$pntable['pnforum_topics']." 
            SET topic_status = $new_status 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);
    return;
}

/**
 * stickyunstickytopic
 * 
 *@params $args['topic_id'] int the topics id
 *@returns void
 */    
function pnForum_userapi_stickyunstickytopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    list($forum_id, $cat_id) = pnForum_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id'=>$topic_id));
    if(!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        return showforumerror(_PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }

    $new_sticky = ($mode=="sticky") ? 1 : 0; 

    $sql = "UPDATE ".$pntable['pnforum_topics']." 
            SET sticky = '$new_sticky' 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);
    return;
}

/**
 * get_forumid_and categoryid_from_topicid
 * used for permission checks
 *
 *@params $args['topic_id'] int the topics id
 *@returns array(forum_id, category_id)
 */
function pnForum_userapi_get_forumid_and_categoryid_from_topicid($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    // we know about the topic_id, let's find out the forum and catgeory name for permission checks
    $sql = "SELECT f.forum_id,
                   c.cat_id
            FROM  ".$pntable['pnforum_topics']." t
            LEFT JOIN ".$pntable['pnforum_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['pnforum_categories']." AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);
    
    $forum_id = pnVarPrepForDisplay($myrow['forum_id']);
    $cat_id = pnVarPrepForDisplay($myrow['cat_id']);
    
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
function pnForum_userapi_readuserforums($args)
{
    extract($args);
    unset($args);
    
    if(!empty($cat_id) && !empty($forum_id)) {
        if(!allowedtoseecategoryandforum($cat_id, $forum_id)) {
            return showforumerror(_PNFORUM_NOAUTH_TOSEE, __FILE__, __LINE__); 
        }
    }

    list($dbconn, $pntable) = pnfOpenDB();
  
    $where = "";
    if(isset($forum_id)) {
        $where = "WHERE f.forum_id=$forum_id ";
    } elseif (isset($cat_id)) {
        $where = "WHERE c.cat_id=$cat_id ";
    }
    $sql = "SELECT f.forum_name,
                   f.forum_id,
                   f.forum_desc,
                   f.forum_access,
                   f.forum_type,
                   f.forum_order,
                   f.forum_topics,
                   f.forum_posts,
                   c.cat_title, 
                   c.cat_id
            FROM ".$pntable['pnforum_forums']." AS f
            LEFT JOIN ".$pntable['pnforum_categories']." AS c
            ON c.cat_id=f.cat_id
            $where                  
            ORDER BY c.cat_order, f.forum_order";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    $forums = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $forum = array();
            list( $forum['forum_name'],
                  $forum['forum_id'],
                  $forum['forum_desc'],
                  $forum['forum_access'],
                  $forum['forum_type'],
                  $forum['forum_order'],
                  $forum['forum_topics'],
                  $forum['forum_posts'],
                  $forum['cat_title'],
                  $forum['cat_id'] ) = $result->fields;
            if(allowedtoseecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
                array_push( $forums, $forum );
            }
        }
    }
    pnfCloseDB($result);
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
 *@returns void
 */
function pnForum_userapi_movetopic($args)
{
    extract($args);
    unset($args);
    
    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 

    list($dbconn, $pntable) = pnfOpenDB();
      
    // get the old forum id and old post date
    $sql = "SELECT t.forum_id,
                   t.topic_time,
                   t.topic_title
            FROM  ".$pntable['pnforum_topics']." t
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);
    
    $oldforum_id  = pnVarPrepForDisplay($myrow['forum_id']);
    $topic_time   = $myrow['topic_time'];
    $topic_title  = $myrow['topic_title'];

    if($oldforum_id <> $forum_id) {
        // set new forum id
        $sql = "UPDATE ".$pntable['pnforum_topics']." 
                SET forum_id = '".(int)pnVarPrepForStore($forum_id)."' 
                WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);

        $sql = "UPDATE ".$pntable['pnforum_posts']." 
                SET forum_id = '".(int)pnVarPrepForStore($forum_id)."' 
                WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);

        if($shadow==true) {
            // user wants to have a shadow topic
            $message = sprintf(_PNFORUM_SHADOWTOPIC_MESSAGE, pnModURL('pnForum','user','viewtopic', array('topic'=> $topic_id)) ); 
            $subject = "***" . pnVarPrepForDisplay(_PNFORUM_MOVED_SUBJECT) . ": " . $topic_title;

            pnForum_userapi_storenewtopic(array('subject'  => $subject,
                                                'message'  => $message,
                                                'forum_id' => $oldforum_id,
                                                'time'     => $topic_time,
                                                'no_sig'   => true));
        }
        pnModAPIFunc('pnForum', 'admin', 'sync', array('id' => $forum_id, 'type' => 'forum'));
        pnModAPIFunc('pnForum', 'admin', 'sync', array('id' => $oldforum_id, 'type' => 'forum'));
    }
    return;
}

/**
 * deletetopic
 *
 *@params $args['topic_id'] int the topics id
 *@returns int the forums id for redirecting
 */
function pnForum_userapi_deletetopic($args)
{
    extract($args);
    unset($args);
    
    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 

    list($dbconn, $pntable) = pnfOpenDB();

    // get the forum id
    $sql = "SELECT t.forum_id
            FROM  ".$pntable['pnforum_topics']." t
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);
    
    $forum_id = pnVarPrepForDisplay($myrow['forum_id']);

    // Update the users's post count, this might be slow on big topics but it makes other parts of the
    // forum faster so we win out in the long run.
    $sql = "SELECT poster_id, post_id 
            FROM ".$pntable['pnforum_posts']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        if($row['poster_id'] != -1) {
            $sql2 = "UPDATE ".$pntable['pnforum_users']." 
                     SET user_posts = user_posts - 1 
                     WHERE user_id = '".$row['poster_id']."'";
            $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);        
            pnfCloseDB($result2);
        }
        $result->MoveNext();
    }
    pnfCloseDB($result);

    // Get the post ID's we have to remove.
    $sql = "SELECT post_id FROM ".$pntable['pnforum_posts']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
        
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    // we need to put a check here if we have more posts...     
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $posts_to_remove[] = $row['post_id'];
        $result->MoveNext();
    }
    pnfCloseDB($result);

    $sql = "DELETE FROM ".$pntable['pnforum_posts']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);

    $sql = "DELETE FROM ".$pntable['pnforum_topics']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
      
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);

    $sql = "DELETE FROM ".$pntable['pnforum_posts_text']." 
            WHERE ";
    for($x = 0; $x < count($posts_to_remove); $x++) {
        if(isset($set)) {
            $sql .= " OR ";
        }
        $sql .= "post_id = '".$posts_to_remove[$x]."'";
        $set = TRUE;
    }
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);

    pnModAPIFunc('pnForum', 'admin', 'sync', array('id' => $forum_id, 'type' => 'forum'));
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
function pnForum_userapi_notify_by_email($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();
  
    setlocale (LC_TIME, pnConfigGetVar('locale'));
    $modInfo = pnModGetInfo(pnModGetIDFromName(pnModGetName()));
    $ModName = pnVarPrepForStore($modInfo['directory']);
    $modVersion = pnVarPrepForStore($modInfo['version']);

    // generate the mailheader
    $email_from = pnModGetVar('pnForum', 'email_from');
    if ($email_from == "") {
        // nothing in forumwide-settings, use PN adminmail
        $email_from = pnConfigGetVar('adminmail');
    }

    $msg_From_Header = "From: ".pnConfigGetVar('sitename')."<".$email_from.">\n";
    $msg_XMailer_Header = "X-Mailer: ".$ModName." ".$modVersion."\n";
    $msg_ContentType_Header = "Content-Type: text/plain;";

    $phpbb_default_charset = pnModGetVar('pnForum', 'default_lang');
    if ($phpbb_default_charset != '') {
        $msg_ContentType_Header .= " charset=".$phpbb_default_charset;
    }
    $msg_ContentType_Header .= "\n";

    // normal notification
    $sql = "SELECT t.topic_title,  
                   t.topic_poster,  
                   t.topic_time, 
                   f.cat_id, 
                   c.cat_title, 
                   f.forum_name, 
                   f.forum_id                   
            FROM  ".$pntable['pnforum_topics']." t 
            LEFT JOIN ".$pntable['pnforum_forums']." f ON t.forum_id = f.forum_id 
            LEFT JOIN ".$pntable['pnforum_categories']." c ON f.cat_id = c.cat_id 
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_FORUM_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);

    $topic_unixtime= strtotime ($myrow['topic_time']);
    $topic_time_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($topic_unixtime));

    $poster_name = pnUserGetVar('uname',$poster_id);
  
    $forum_id = pnVarPrepForDisplay($myrow['forum_id']);
    $forum_name = pnVarPrepForDisplay($myrow['forum_name']);
    $category_name = pnVarPrepForDisplay($myrow['cat_title']);
    $topic_subject = pnVarPrepForDisplay(pnVarCensor($myrow['topic_title']));

    if ($type == 0) {
        // New message
        $msg_Subject= "";
    } elseif ($type == 2) {
        // Reply
        $msg_Subject= "Re: ";
    }
    $msg_Subject .= "$category_name :: $forum_name :: $topic_subject";

    //  get list of forum subscribers
    $sql = "SELECT user_id
            FROM ".$pntable['pnforum_subscription']."
            WHERE forum_id=".pnVarPrepForStore($forum_id)."";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    $recipients = array();
    // check if list is empty - then do nothing
    // we create an array of recipients here
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            list($pn_uid) = $result->fields;
            // get e-mail address by uid
            //check if the recipient is already in our list to avoid dupes
            if(!array_key_exists($pn_uid, $recipients)) {
                $recipients[$pn_uid] = pnUserGetVar('email', $pn_uid);
            }
        }
    }
    pnfCloseDB($result);

    //  get list of topic_subscribers
    $sql = "SELECT user_id 
            FROM ".$pntable['pnforum_topic_subscription']." 
            WHERE topic_id=".(int)pnVarPrepForStore($topic_id)."";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            list($pn_uid) = $result->fields;
            // get e-mail address by uid
            //check if the recipient is already in our list to avoid dupes
            if(!array_key_exists($pn_uid, $recipients)) {
                $recipients[$pn_uid] = pnUserGetVar('email', $pn_uid);
            }
        }
    }
    pnfCloseDB($result);

    if(count($recipients)>0) {
        foreach($recipients as $uid=>$email) {
            // set reply-to to his own adress ;)
            $msg_Headers = $msg_From_Header.$msg_XMailer_Header.$msg_ContentType_Header;
            $msg_Headers .= "Reply-To: $email"; //.$subscriber_userdata['pn_email'];
        
            $message = _PNFORUM_NOTIFYBODY1." ".pnConfigGetVar('sitename')."\n"
                    . "$category_name :: $forum_name ::.. $topic_subject\n\n"
                    . "$poster_name ".pnVarPrepForDisplay(_PNFORUM_NOTIFYBODY2)." $topic_time_ml\n"
                    . "---------------------------------------------------------------------\n"
                    . "".pnVarCensor(strip_tags($post_message))."\n"
                    . "---------------------------------------------------------------------\n\n"
                    . _PNFORUM_NOTIFYBODY3."\n"
                    . pnModURL('pnForum', 'user', 'reply', array('topic'=>$topic_id,'forum'=>$forum_id))."\n\n"
                    . _PNFORUM_NOTIFYBODY4."\n"
                    . pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id))."\n"
                    . "\n"
                    . _PNFORUM_NOTIFYBODY5." ".pnGetBaseURL(); 
            pnMail($email, $msg_Subject, $message, $msg_Headers);
        }
    }
    return;
}

/**
 * subscribe_topic
 *
 *@params $args['topic_id'] int the topics id
 *@returns void
 */
function pnForum_userapi_subscribe_topic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $userid = pnUserGetVar('uid');

    list($forum_id, $cat_id) = pnForum_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id'=>$topic_id));
    if(!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        return showforumerror(_PNFORUM_NOAUTH_TOREAD, __FILE__, __LINE__);
    }
    
    if (pnForum_userapi_get_topic_subscription_status(array('userid'=>$userid, 'topic_id'=>$topic_id)) == false) {
        // add user only if not already subscribed to the topic
        $sql = "INSERT INTO ".$pntable['pnforum_topic_subscription']." (user_id, forum_id, topic_id) 
                VALUES ('".(int)pnVarPrepForStore($userid)."','".(int)pnVarPrepForStore($forum_id)."','".(int)pnVarPrepForStore($topic_id)."')";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
    }
    return;
}

/**
 * unsubscribe_topic
 *
 *@params $args['topic_id'] int the topics id
 *@params $args['silent'] bool true=no error message when not subscribed, simply return void
 *@returns void
 */
function pnForum_userapi_unsubscribe_topic($args)
{
    extract($args);
    unset($args);

    $silent = (isset($silent)) ? true : false;

    list($dbconn, $pntable) = pnfOpenDB();

    $userid = pnUserGetVar('uid');

    if (pnForum_userapi_get_topic_subscription_status(array('userid'=>$userid, 'topic_id'=>$topic_id)) == true) {
        // user is subscribed, delete subscription
        $sql = "DELETE FROM ".$pntable['pnforum_topic_subscription']." 
                WHERE user_id='".(int)pnVarPrepForStore($userid)."' 
                AND topic_id='".(int)pnVarPrepForStore($topic_id)."'";              
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
    } else {
        // user is not subscribed
        if($silent==false) {
            return showforumerror(_PNFORUM_NOTSUBSCRIBED, __FILE__, __LINE__);
        } else {
            return;
        }
    }
}

/**
 * subscribe_forum
 *
 *@params $args['forum_id'] int the forums id
 *@returns void
 */
function pnForum_userapi_subscribe_forum($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $userid = pnUserGetVar('uid');

    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 
    $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                          array('forum_id' => $forum_id));
    if(!allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(_PNFORUM_NOAUTH_TOREAD, __FILE__, __LINE__);
    }
    
    if (pnForum_userapi_get_forum_subscription_status(array('userid'=>$userid, 'forum_id'=>$forum_id)) == false) {
        // add user only if not already subscribed to the forum
        $sql = "INSERT INTO ".$pntable['pnforum_subscription']." (user_id, forum_id) 
                VALUES ('".(int)pnVarPrepForStore($userid)."','".(int)pnVarPrepForStore($forum_id)."')";
                
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
    }
    return;
}

/**
 * unsubscribe_forum
 *
 *@params $args['forum_id'] int the forums id
 *@returns void
 */
function pnForum_userapi_unsubscribe_forum($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();
  
    $userid = pnUserGetVar('uid');
    
    if (pnForum_userapi_get_forum_subscription_status(array('userid'=>$userid, 'forum_id'=>$forum_id)) == true) {
        // user is subscribed, delete subscription
        $sql = "DELETE FROM ".$pntable['pnforum_subscription']." 
                WHERE user_id='".(int)pnVarPrepForStore($userid)."' 
                AND forum_id='".(int)pnVarPrepForStore($forum_id)."'";
                
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
    } else {
        return showforumerror(_PNFORUM_NOTSUBSCRIBED, __FILE__, __LINE__);
    }
    return;
}
/**
 * add_favorite_forum
 *
 *@params $args['forum_id'] int the forums id
 *@returns void
 */
function pnForum_userapi_add_favorite_forum($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $userid = pnUserGetVar('uid');

    $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                          array('forum_id' => $forum_id));

    if(!allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(_PNFORUM_NOAUTH_TOREAD, __FILE__, __LINE__);
    }
    
    if (pnForum_userapi_get_forum_favorites_status(array('userid'=>$userid, 'forum_id'=>$forum_id)) == false) {
        // add user only if not already a favorite
        $sql = "INSERT INTO ".$pntable['pnforum_forum_favorites']." (user_id, forum_id) 
                VALUES ('".(int)pnVarPrepForStore($userid)."','".(int)pnVarPrepForStore($forum_id)."')";
                
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
    }
    return;
}

/**
 * remove_favorite_forum
 *
 *@params $args['forum_id'] int the forums id
 *@returns void
 */
function pnForum_userapi_remove_favorite_forum($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();
  
    $userid = pnUserGetVar('uid');
    
    if (pnForum_userapi_get_forum_favorites_status(array('userid'=>$userid, 'forum_id'=>$forum_id)) == true) {
        // remove from favorites
        $sql = "DELETE FROM ".$pntable['pnforum_forum_favorites']." 
                WHERE user_id='".(int)pnVarPrepForStore($userid)."' 
                AND forum_id='".(int)pnVarPrepForStore($forum_id)."'";
                
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
        pnfCloseDB($result);
    } else {
        return showforumerror(_PNFORUM_NOTSUBSCRIBED, __FILE__, __LINE__);
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
function pnForum_userapi_prepareemailtopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();
    
    $sql = "SELECT t.topic_title, 
                   t.topic_id,
                   t.forum_id, 
                   f.forum_name, 
                   f.cat_id, 
                   c.cat_title
            FROM  ".$pntable['pnforum_topics']." t
            LEFT JOIN ".$pntable['pnforum_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['pnforum_categories']." AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    pnfCloseDB($result);
    
    $topic['topic_id'] = pnVarPrepForDisplay($myrow['topic_id']);
    $topic['forum_name'] = pnVarPrepForDisplay($myrow['forum_name']);
    $topic['cat_title'] = pnVarPrepForDisplay($myrow['cat_title']);
    $topic['forum_id'] = pnVarPrepForDisplay($myrow['forum_id']);
    $topic['cat_id'] = pnVarPrepForDisplay($myrow['cat_id']);
    $topic['topic_subject'] = pnVarPrepForDisplay(pnVarCensor($myrow['topic_title']));
    
    /**
     * base security check
     */
    if(!allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id'])) {
        return showforumerror(_PNFORUM_NOAUTH_TOREAD, __FILE__, __LINE__);
    }
    return $topic;
}

/**
 * emailtopic
 *
 *@params $args['sendto_email'] stig the recipients email address
 *@params $args['message'] string the text
 *@params $args['subject'] string the subject
 *@returns void
 */
function pnForum_userapi_emailtopic($args)
{
    extract($args);
    unset($args);

    if (!pnSecConfirmAuthKey()) {
        return showforumerror(_PNFORUM_BADAUTHKEY, __FILE__, __LINE__);
    }

    $sender_name = pnUserGetVar('uname');
    $sender_email = pnUserGetVar('email');          
    if (!pnUserLoggedIn()) {
        $sender_name = pnConfigGetVar('anonymous');
        $sender_email = pnModGetVar('pnForum', 'email_from');
    }
    pnMail($sendto_email, $topic_subject, $message, "From: \"$sender_name\" <$sender_email>\nX-Mailer: PHP/" . phpversion());
    return;
}

/**
 * get_latest_posts
 *
 *@params $args['selorder'] int 1-6, see below
 *@params $args['nohours'] int posting within these hours
 *@params $args['unanswered'] int 0 or 1(= postings with no answers)
 *@params $args['last_visit'] string the users last visit data
 *@returns array (postings, text_to_display)
 */
function pnForum_userapi_get_latest_posts($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();
    
    $posts_per_page = pnModGetVar('pnForum', 'posts_per_page');
    $post_sort_order = pnModGetVar('pnForum', 'post_sort_order');
    
    // some tricky sql
    $part1 = "SELECT    t.topic_id,
                        t.topic_title,
                        f.forum_id,
                        f.forum_name, 
                        c.cat_id,
                        c.cat_title,
                        t.topic_replies,
                        t.topic_last_post_id
            FROM        ".$pntable['pnforum_topics']." t
            LEFT JOIN   ".$pntable['pnforum_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN   ".$pntable['pnforum_categories']." AS c ON c.cat_id = f.cat_id
            WHERE";
    
    if ($unanswered==1) {
        $part2 = "AND t.topic_replies='0' ORDER BY t.topic_time DESC";
    } else {
        $part2 = "ORDER BY t.topic_time DESC";
    }
    
    $lastweeksql    = $part1." TO_DAYS(NOW()) - TO_DAYS(t.topic_time) < 8 ".$part2;
    $yesterdaysql   = $part1." TO_DAYS(NOW()) - TO_DAYS(t.topic_time) = 1 ".$part2;
    $todaysql       = $part1." TO_DAYS(NOW()) - TO_DAYS(t.topic_time) = 0 ".$part2;
    $last24hsql     = $part1." t.topic_time > DATE_SUB(NOW(), INTERVAL 1 DAY) ".$part2;
    $lastxhsql      = $part1." t.topic_time > DATE_SUB(NOW(), INTERVAL $nohours HOUR) ".$part2;
    $lastvisitsql   = $part1." t.topic_time > '$last_visit' ".$part2;

    switch ($selorder) {
        case "1" : $sql = $last24hsql; $text=""._PNFORUM_LAST24.""; break;
        case "2" : $sql = $todaysql; $text=""._PNFORUM_TODAY.""; break;
        case "3" : $sql = $yesterdaysql; $text=""._PNFORUM_YESTERDAY.""; break;
        case "4" : $sql = $lastweeksql; $text=""._PNFORUM_LASTWEEK.""; break;
        case "5" : $sql = $lastxhsql; $text=""._PNFORUM_LAST." $nohours "._PNFORUM_HOURS.""; break;
        case "6" : $sql = $lastvisitsql; $text=""._PNFORUM_LASTVISIT." ".ml_ftime(_DATETIMEBRIEF, $temptime).""; break;
        default : $sql = $last24sql; break;
    }
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    $posts = array();
    while ((list($topic_id, $topic_title, $forum_id, $forum_name, $cat_id, $cat_title, 
                 $topic_replies, $topic_last_post_id) = $result->FetchRow()) ) {
        $post=array();
        $post['topic_id'] = pnVarPrepForDisplay($topic_id);
        $post['topic_title'] = pnVarPrepForDisplay(pnVarCensor($topic_title));
        $post['forum_id'] = pnVarPrepForDisplay($forum_id);
        $post['forum_name'] = pnVarPrepForDisplay($forum_name);
        $post['cat_id'] = pnVarPrepForDisplay($cat_id);
        $post['cat_title'] = pnVarPrepForDisplay($cat_title);
        $post['topic_replies'] = pnVarPrepForDisplay($topic_replies);
        $post['topic_last_post_id'] = pnVarPrepForDisplay($topic_last_post_id);
        
        // check permission before display
        if(allowedtoreadcategoryandforum($post['cat_id'], $post['forum_id'])) {
            // get correct page for latest entry
            if ($post_sort_order == "ASC") {
                $hc_dlink_times = 0;
                if (($topic_replies+1-$posts_per_page)>= 0) { 
                    $hc_dlink_times = 0; 
                    for ($x = 0; $x < $topic_replies+1-$posts_per_page; $x+= $posts_per_page) 
                    $hc_dlink_times++; 
                } 
                $start = $hc_dlink_times*$posts_per_page;
            } else {
                // latest topic is on top anyway...
                $start = 0;
            }
            $post['start'] = $start;
    
            // get postername and posttime
            $sql2 = "SELECT p.post_time, 
                           u.pn_uname 
                    FROM ".$pntable['pnforum_posts']." AS p, 
                         ".$pntable['users']." AS u 
                    WHERE p.topic_id = '".(int)pnVarPrepForStore($topic_id)."' 
                    AND p.poster_id = u.pn_uid 
                    ORDER BY post_time DESC";
        
            $result2 = pnfSelectLimit($dbconn, $sql2, 1, false, __FILE__, __LINE__);
            $row = $result2->GetRowAssoc(false);
            $post['poster_name'] = $row['pn_uname'];
            $post['posted_unixtime'] = strtotime ($row['post_time']);
            $post['post_time'] = ml_ftime(_DATETIMEBRIEF, GetUserTime($post['posted_unixtime']));
            pnfCloseDB($result2);
            
            array_push($posts, $post);
        }
    }
    pnfCloseDB($result);
    return array($posts, $text);
}
  
/**
 * usersync
 * stub function for syncing new pn users to pnforum
 *
 *@params none
 *@returns void
 */
function pnForum_userapi_usersync()
{
    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 
  	pnModAPIFunc('pnForum', 'admin', 'sync', 
                 array( 'id'   => NULL,
	                    'type' => "users"));
    return;
}

/**
 * splittopic
 *
 *@params $args['post'] array with posting data as returned from readpost()
 *@returns int id of the new topic
 */
function pnForum_userapi_splittopic($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    // it's a submitted page
    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
    }

    // before we do anything we will read the topic_last_post_id because we will need
    // this one later (it will become the topic_last_post_id of the new thread)
    $sql = "SELECT topic_last_post_id,
                   topic_replies
            FROM ".$pntable['pnforum_topics']." 
            WHERE topic_id = '".$post['topic_id']."'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        

    list($old_last_post_id, $old_replies) = $result->fields;
    pnfCloseDB($result);

    $time = date("Y-m-d H:i");

    //  insert values into topics-table
    $topic_id = $dbconn->GenID($pntable['pnforum_topics']);
    $sql = "INSERT INTO ".$pntable['pnforum_topics']." 
            (topic_id, topic_title, topic_poster, forum_id, topic_time, topic_notify) 
            VALUES 
            ('".pnVarPrepForStore($topic_id)."', '".pnVarPrepForStore($post['topic_subject'])."', '".$post['poster_data']['pn_uid']."', '".$post['forum_id']."', '$time', '' )";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    $newtopic_id = $dbconn->PO_Insert_ID($pntable['pnforum_topics'], 'topic_id');
    pnfCloseDB($result);

    // now we need to change the postings:
    // first step: count the number of posting we have to move
    $sql = "SELECT COUNT(*) AS total
            FROM ".$pntable['pnforum_posts']." 
            WHERE topic_id = '".$post['topic_id']."'
              AND post_id >= '".(int)pnVarPrepForStore($post['post_id'])."'";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    list($posts_to_move) = $result->fields;
    pnfCloseDB($result);

    // starting with $post['post_id'] and then all post_id's where topic_id = $post['topic_id'] and
    // post_id > $post['post_id']
    $sql = "UPDATE ".$pntable['pnforum_posts']." 
            SET topic_id = '$newtopic_id' 
            WHERE post_id >= '".(int)pnVarPrepForStore($post['post_id'])."'
            AND topic_id = '".$post['topic_id']."'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);

    // get the new topic_last_pos_id of the old topic
    $sql = "SELECT post_id
            FROM ".$pntable['pnforum_posts']."
            WHERE topic_id = '".(int)pnVarPrepForStore($post['topic_id'])."' 
            ORDER BY post_time DESC";
    $result = pnfSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
    list($new_last_post_id) = $result->fields;
    pnfCloseDB($result);

    // update the new topic
    $newtopic_replies = (int)$posts_to_move - 1;
    $sql = "UPDATE ".$pntable['pnforum_topics']." 
            SET topic_replies = '$newtopic_replies',
                topic_last_post_id = '$old_last_post_id'
            WHERE topic_id = '$newtopic_id'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);

    // update the old topic
    $old_replies = (int)$old_replies - (int)$posts_to_move; 
    $sql = "UPDATE ".$pntable['pnforum_topics']." 
            SET topic_replies = $old_replies,
                topic_last_post_id = '$new_last_post_id'
            WHERE topic_id = '".$post['topic_id']."'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);
    
    return $newtopic_id;
}

/** 
 * update_user_post_count
 *
 *@params $args['user_id'] int the users id
 *@params $args['mode']    string, either "inc" (+1) or "dec" (-1)
 *@returns bool true or false
 */
function pnForum_userapi_update_user_post_count($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    if(!isset($user_id) || !isset($mode)) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }
    if(strtolower($mode)=="inc") {
        $math = "+";
    } elseif(strtolower($mode)=="dec") {
        $math = "-";
    } else {
        return false;
    }
    
    $sql = "UPDATE ".$pntable['pnforum_users']." 
            SET user_posts = user_posts $math 1 
            WHERE user_id = '".(int)pnVarPrepForStore($user_id)."'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    pnfCloseDB($result);
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
function pnForum_userapi_get_previous_or_next_topic_id($args)
{
    extract($args);
    unset($args);
    
    if(!isset($topic_id) || !isset($view) ) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    list($dbconn, $pntable) = pnfOpenDB();

    switch($view) {
        case "previous": $math = "<"; $sort = "DESC"; break;
        case "next":     $math = ">"; $sort = "ASC";break;
        default: return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }
                           
    $sql = "SELECT t1.topic_id
            FROM ".$pntable['pnforum_topics']." AS t1,
                 ".$pntable['pnforum_topics']." AS t2
            WHERE t2.topic_id = ".(int)pnVarPrepForStore($topic_id)."
              AND t1.topic_time $math t2.topic_time
              AND t1.forum_id = t2.forum_id
              AND t1.sticky = 0
            ORDER BY t1.topic_time $sort";
    $result = pnfSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
    if(!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $topic_id = $row['topic_id'];
    }
    pnfCloseDB($result);
    return $topic_id;        
}

/**
 * forumsearch
 * the function that will search the forum
 *
 *@params $args['searchfor']  string the search term
 *@params $args['bool']       string 'AND' or 'OR'
 *@params $args['forums']     array array of forum ids to search in
 *@params $args['author']     string searhc for postings of this author only
 *@params $args['order']      array array of order to display results
 *@params $args['startnum']   int number of entry to start showing when on page > 1
 *@returns array with search results
 */
function pnForum_userapi_forumsearch($args)
{
    extract($args);
    unset($args);

    if( empty($searchfor) && empty($author) ) {
        return showforumerror(_PNFORUM_SEARCHINCLUDE_MISSINGPARAMETERS, __FILE__, __LINE__);
    }
    
    list($dbconn, $pntable) = pnfOpenDB();

    $query = "SELECT DISTINCT
              f.forum_id,
              f.forum_name,
              f.cat_id,
              c.cat_title,
              pt.post_text,
              pt.post_id,
              t.topic_id,
              t.topic_title,
              t.topic_replies,
              t.topic_views,
              p.poster_id,
              p.post_time 
              FROM ".$pntable['pnforum_posts']." AS p, 
                   ".$pntable['pnforum_forums']." AS f,
                   ".$pntable['pnforum_posts_text']." AS pt, 
                   ".$pntable['pnforum_topics']." AS t,
                   ".$pntable['pnforum_categories']." AS c
              WHERE ";
            
    $searchfor = pnVarPrepForStore(trim($searchfor));
    if(!empty($searchfor)) {
        $flag = false;
        $words = explode(' ', $searchfor);
        $query .= "( ";
        foreach($words as $word) {
            if($flag) {
                switch($bool) {
                    case 'AND' :
                        $query .= ' AND ';
                        break;
                    case 'OR' :
                    default :
                        $query .= ' OR ';
                        break;
                }
            }
            // get post_text and match up forums/topics/posts
            $query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
            $flag = true;
        }
        $query .= " ) AND ";
    } else {
        // searchfor is empty, we search by author only
    }
    $query .= "p.post_id=pt.post_id \n";
    $query .= "AND p.topic_id=t.topic_id \n";
    $query .= "AND p.forum_id=f.forum_id\n";
    $query .= "AND c.cat_id=f.cat_id\n";
    
    //check forums (multiple selection is possible!)
    if($forums[0]) {
        $query .= " AND (";
        $flag = false;
        foreach($forums as $forumid) {
            if($flag) {
                $query .= " OR ";
            }
            $query .= "f.forum_id=$forumid";
            $flag = true;
        }
        $query .= ") ";
    }
    
    // authors with adodb
    if($author) {
        $search_username = addslashes($author);
        $sql = "SELECT pn_uid 
                FROM $pntable[users] 
                WHERE pn_uname = '".pnVarPrepForStore($search_username)."'";
        $result = pnfSelectLimit($dbconn, $sql, 1, false, __FILE__, __LINE__);
        $row = $result->GetRowAssoc(false);
        pnfCloseDB($result);
        $searchauthor = $row['pn_uid'];
        if ($searchauthor > 0){
            $query .= " AND p.poster_id=$searchauthor \n";
        } else {
            $query .= " AND p.poster_id=0 \n";
        }
    }

    // Not sure this is needed and is not cross DB compat
    //$query .= " GROUP BY pt.post_id ";
    
    $searchorder = $order['0'];

    if ($searchorder == 1){
        $query .= " ORDER BY pt.post_id DESC";
    }
    if ($searchorder == 2){
        $query .= " ORDER BY t.topic_title";
    }
    if ($searchorder == 3){
        $query .= " ORDER BY f.forum_name";
    }
    $result = pnfExecuteSQL($dbconn, $query, __FILE__, __LINE__);

    $total_hits = 0;
    $skip_hits = 0; 
    $searchresults = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            $sresult = array();
            list($sresult['forum_id'],
                 $sresult['forum_name'],
                 $sresult['cat_id'],
                 $sresult['cat_title'],
                 $sresult['post_text'],
                 $sresult['post_id'],
                 $sresult['topic_id'],
                 $sresult['topic_title'],
                 $sresult['topic_replies'],
                 $sresult['topic_views'],
                 $sresult['poster_id'],
                 $sresult['post_time']) = $result->fields;
     	    if(allowedtoseecategoryandforum($sresult['cat_id'], $sresult['forum_id'])) {
                // auth check for forum and category before displaying search result
                // timezone
                $sresult['posted_unixtime'] = strtotime ($sresult['post_time']);
                $sresult['posted_time'] = ml_ftime(_DATETIMEBRIEF, GetUserTime($sresult['posted_unixtime']));
                $sresult['topic_title'] = stripslashes($sresult['topic_title']);
                
                //without signature
                $sresult['post_text'] = eregi_replace("\[addsig]$", "", $sresult['post_text']);
                
                //strip_tags is needed here 'cause maybe we cut within a html-tag...
                $sresult['post_text'] = strip_tags($sresult['post_text']);
    
                // username
                $sresult['poster_name'] = pnUserGetVar('uname', $sresult['poster_id']);
                
                // check if we have to skip the first $startnum entries or not
                if( ($startnum > 0) && ($skip_hits < $startnum-1) ) {
                    $skip_hits++;
                } else {
                    // check if we have a limit and wether we have reached it or not
                    if( ( ($limit > 0) && (count($searchresults) < $limit) ) || ($limit==0) ) {
                        array_push($searchresults, $sresult);
                    }
                }
                $total_hits++;
            }
        }
    }

    pnfCloseDB($result);
    return array($searchresults, $total_hits);
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
function pnForum_userapi_getfavorites($args)
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
    $tree = pnModAPIFunc('pnForum', 'user', 'readcategorytree', array('last_visit' => $last_visit ));
    
    // if they are anonymous they can't have favorites
    if (!pnUserLoggedIn()) {
        return $tree;
    }

    if (!isset($user_id)) {
        $user_id = (int)pnUserGetVar('uid');
    }

    list($dbconn, $pntable) = pnfOpenDB();
    
    $sql = "SELECT f.forum_id
            FROM ".$pntable['pnforum_forum_favorites']." AS f
            WHERE f.user_id='" . (int)pnVarPrepForStore($user_id) . "'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    
    // make sure we start with an empty array
    $favoritesArray = array();
    while(!$result->EOF) {
        list($forum_id) = $result->fields;
        // add this favorite to the favorites array
        array_push($favoritesArray, (int)$forum_id);
        $result->MoveNext();
    }
    pnfCloseDB($result);
    
    // categoryCount is needed since the categories aren't stored as numerical
    // indexes.  They are stored as associative arrays.
    $categoryCount=0;
    // loop through all the forums and delete all forums that aren't part of
    // the favorites.
    $deleteMe = array();
    foreach ($tree as $categoryIndex=>$category) {
        // $count is needed because the index changes as we splice the array
        // but the foreach is working on a copy of the array so the $forumIndex
        // value will point to non-existent elements in the modified array.
        $count=0;
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
        if (count($tree[$categoryIndex]['forums'])===0) {
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
function pnForum_userapi_get_favorite_status($args)
{
    extract($args);
    unset($args);
    
    if (!isset($user_id)) {
        $user_id = (int)pnUserGetVar('uid');
    }

    list($dbconn, $pntable) = pnfOpenDB();
    $userstable = $pntable['pnforum_users'];
    $userscol    = $pntable['pnforum_users_column'];
    $sql = "SELECT $userscol[user_favorites]
            FROM $userstable 
            WHERE $userscol[user_id] = '".pnVarPrepForStore($user_id)."'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    list($favorite) = $result->fields;
    pnfCloseDB($result);
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
function pnForum_userapi_change_favorite_status($args)
{
    extract($args);
    unset($args);
    
    if (!isset($user_id)) {
        $user_id = (int)pnUserGetVar('uid');
    }

    $recentstatus = pnForum_userapi_get_favorite_status(array('user_id' => $user_id));
    $newstatus = ($recentstatus==true) ? 0 : 1;

    list($dbconn, $pntable) = pnfOpenDB();
    $userstable = $pntable['pnforum_users'];
    $userscol    = $pntable['pnforum_users_column'];
    $sql = "UPDATE $userstable
            SET $userscol[user_favorites] = $newstatus
            WHERE $userscol[user_id] = '".pnVarPrepForStore($user_id)."'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);
    return (bool)$newstatus;
} 

/** 
 * get_user_post_order
 * Determines the users desired post order for topics.
 * Either Newest First or Oldest First
 * Returns 'ASC' or 'DESC' on success, false on failure.
 *
 *@params user_id - The user id of the person who's order we
 * are trying to determine
 *@returns string on success, false on failure
 */
function pnForum_userapi_get_user_post_order($args)
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
            return pnModGetVar('pnForum', 'post_sort_order');
        }
    } else {
        // we didn't get a user_id passed into the function so if
        // the user is logged in then lets use their id.  If not
        // then return th default order.
        if ($loggedIn) {
            $user_id = pnUserGetVar('uid');
        } else {
            return pnModGetVar('pnForum', 'post_sort_order');
        }
    }

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT u.user_post_order 
            FROM  ".$pntable['pnforum_users']." u
            WHERE u.user_id = '".(int)pnVarPrepForStore($user_id)."'";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if(!$result->EOF) {
        list($post_order) = $result->fields;
        $post_order = ($post_order) ? 'DESC' : 'ASC';
    } else {
        $post_order = pnModGetVar('pnForum', 'post_sort_order');
    }
    pnfCloseDB($result2);
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
function pnForum_userapi_change_user_post_order($args)
{
    extract($args);
    unset($args);
    
    // if we didn't get a user_id and the user isn't logged in then
    // return false because there is no database entry to update
    if (!isset($user_id) && pnUserLoggedIn()) {
        $user_id = (int)pnUserGetVar('uid');
    } else {
        return false;
    }

    $post_order = pnModAPIFunc('pnForum','user','get_user_post_order'); 

    $new_post_order = ($post_order=='DESC') ? 0 : 1;

    list($dbconn, $pntable) = pnfOpenDB();
    $userstable = $pntable['pnforum_users'];
    $userscol    = $pntable['pnforum_users_column'];
    $sql = "UPDATE $userstable
            SET $userscol[user_post_order] = $new_post_order
            WHERE $userscol[user_id] = '".pnVarPrepForStore($user_id)."'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);
    return true;
} 
?>
