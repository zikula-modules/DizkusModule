<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.post-nuke.net/                                            *
 ************************************************************************
 * Modified version of: *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License *
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
 * @link http://www.post-nuke.net
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

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

    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

    if(!$result->EOF) {
        $userdata = array();
        for($i=0; $i<count($userkeys); $i++) {
            $userdata[$userkeys[$i]] = $result->fields[$i];
        }
        $result->Close();

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
        $rank_result = $dbconn->Execute($sql);
        if($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }
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
        $rank_result->Close();
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

            $userresult = $dbconn->Execute($userhack);

            if($dbconn->ErrorNo() != 0) {
                return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$userhack,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
            }
            $online_state = $userresult->GetRowAssoc(false);
            $userdata['online'] = false;
            if($online_state['pn_uid'] == $userdata['pn_uid']) {
                $userdata['online'] = true; //$online_state[$userdata['pn_uid']];
            }
            $userresult->Close();

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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

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
        }
    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    list ($total) = $result->fields;
    $result->Close();
    return $total;
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
    extract($args);
    unset($args);

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();
    
    $sql = "SELECT c.cat_id,
                    c.cat_title,
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
            ORDER BY c.cat_order, f.forum_order";
    
    $result = $dbconn->Execute($sql);
    
    if ($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

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
        $forum['forum_id']     = $row['forum_id'];
        $forum['forum_name']   = $row['forum_name'];
        $forum['forum_desc']   = $row['forum_desc'];
        $forum['forum_topics'] = $row['forum_topics'];
        $forum['forum_posts']  = $row['forum_posts'];
        $forum['pn_uname']     = $row['pn_uname'];
        $forum['pn_uid']       = $row['pn_uid'];
        $forum['topic_id']     = $row['topic_id'];
        $forum['post_time']    = $row['post_time'];
//        if(pnSecAuthAction(0, 'pnForum::Category', $cat['cat_title'] ."::", ACCESS_READ)) {
        if(allowedtoseecategoryandforum($cat['cat_id'], $forum['forum_id'])) {
            if(!array_key_exists( $cat['cat_title'], $tree)) {
                $tree[$cat['cat_title']] = $cat;
            }
            if(!empty($forum['forum_id'])) {
//              if(pnSecAuthAction(0, 'pnForum::Forum', $forum['forum_name']."::", ACCESS_READ)) {
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
//              }
            }
        }
        $result->MoveNext();
    }
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
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $sql = "SELECT u.pn_uname, u.pn_uid 
            FROM ".$pntable['users']." u, ".$pntable['pnforum_forum_mods']." f 
            WHERE f.forum_id = '".(int)pnVarPrepForStore($forum_id)."' 
            AND u.pn_uid = f.user_id";

    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    $mods = array();
    while(!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $mods[$row['pn_uid']] = $row['pn_uname'];
        $result->MoveNext();
    }
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
    // set LastVisitTemp cookie, which only gets the time from the LastVisit and lasts for 5 min
    setcookie('phpBBLastVisitTemp', $temptime, time()+300);
    
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

//    if(!pnSecAuthAction(0, 'pnForum::', ":".$forum_id.":", ACCESS_READ)) {

    $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                          array('forum_id' => $forum_id));
    if($forum==false) {
        return showforumerror(_PNFORUM_FORUM_NOEXIST, __FILE__, __LINE__);
    }

    if(!allowedtoseecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        return showforumerror(_PNFORUM_NOAUTH_TOSEE, __FILE__, __LINE__);
    } 
    
//    if ( !pnSecAuthAction(0, 'pnForum::Forum', $forum['forum_name'] . "::", ACCESS_READ) || 
//        !pnSecAuthAction(0, 'pnForum::Category', $forum['cat_title'] . "::", ACCESS_READ) )   {
//        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__);
//    } 

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();


    $posts_per_page     = pnModGetVar('pnForum', 'posts_per_page');
    $topics_per_page    = pnModGetVar('pnForum', 'topics_per_page');
    $hot_threshold      = pnModGetVar('pnForum', 'hot_threshold');
    $folder_image       = pnModGetVar('pnForum', 'folder_image');
    $hot_folder_image   = pnModGetVar('pnForum', 'hot_folder_image');
    $newposts_image     = pnModGetVar('pnForum', 'newposts_image');
    $hot_newposts_image = pnModGetVar('pnForum', 'hot_newposts_image');
    $posticon           = pnModGetVar('pnForum', 'posticon');
    $locked_image       = pnModGetVar('pnForum', 'locked_image');
    $stickytopic_image  = pnModGetVar('pnForum', 'stickytopic_image');
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
                   u.pn_uname, 
                   u2.pn_uname as last_poster, 
                   p.post_time
            FROM ".$pntable['pnforum_topics']." AS t
            LEFT JOIN ".$pntable['users']." AS u ON t.topic_poster = u.pn_uid
            LEFT JOIN ".$pntable['pnforum_posts']." AS p ON t.topic_last_post_id = p.post_id
            LEFT JOIN ".$pntable['users']." AS u2 ON p.poster_id = u2.pn_uid
            WHERE t.forum_id = '".(int)pnVarPrepForStore($forum_id)."'
            ORDER BY t.sticky DESC, topic_time DESC";
    
    $result = $dbconn->SelectLimit($sql, $topics_per_page, $start);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    $forum['forum_id'] = $forum_id;
    $forum['topics'] = array();
    while(!$result->EOF) {
        $topic = array();
        $row = $result->GetRowAssoc(false);
        $topic = $row;
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
        
        if($topic_status == 1) {
            $image = $locked_image;
            $altimage = "locked_image";
        }
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
        
            $newp = $dbconn->Execute($sql);
        
            if (!$newp->EOF) {
                $temp = $newp->GetRowAssoc(false);
                $topic['newest_postid'] = $temp['post_id'];
            }
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
                    $pagination .= "<a href=\"".pnModURL('pnForum', 'user', 'viewtopic', array('start' => $start))."\" title=\"$topic_title #$pagenr\">$pagenr</a>";
                }
        
                $pagenr++;
            }
            $pagination .= ")</span>";
        }
        $topic['pagination'] = $pagination;
        
        array_push( $forum['topics'], $topic );
        $result->MoveNext(); 
    }


    $topics_start = $start;


    return $forum;
}

/** 
 * readtopic
 * reads a topic with the last posts_per_page answers (incl the initial posting when on page #1)
 *
 *@params $args['topic_id'] it the topics id
 *@params $args['start'] int number of posting to start with (if on page 1+)
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
    $post_sort_order = pnModGetVar('pnForum', 'post_sort_order');

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $sql = "SELECT t.topic_title, 
                   t.topic_status, 
                   t.forum_id, 
                   t.sticky, 
                   f.forum_name, 
                   f.cat_id, 
                   c.cat_title
            FROM  ".$pntable['pnforum_topics']." t
            LEFT JOIN ".$pntable['pnforum_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['pnforum_categories']." AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";

    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    $topic = array();
    if(!$result->EOF) {
        $topic = $result->GetRowAssoc(false);
        $topic['topic_id'] = $topic_id;

//        if ((!pnSecAuthAction(0, 'pnForum::Forum', $topic['forum_name'] ."::", ACCESS_READ)) || 
//            (!pnSecAuthAction(0, 'pnForum::Category', $topic['cat_title'] ."::", ACCESS_READ))) {
//            return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__);
//        }

        if(!allowedtoreadcategoryandforum($topic['cat_id'], $topic['forum_id'])) {
            return showforumerror(_PNFORUM_NOAUTH_TOREAD, __FILE__, __LINE__);
        } 

//        if ((pnSecAuthAction(0, 'pnForum::Forum', $topic['forum_name'] ."::", ACCESS_COMMENT)) || 
//            (pnSecAuthAction(0, 'pnForum::Category', $topic['cat_title'] ."::", ACCESS_COMMENT))) {
//            $topic['access_comment'] = true;
//        }

        $topic['access_comment'] = false;
        if(allowedtowritetocategoryandforum($topic['cat_id'], $topic['forum_id'])) {
            $topic['access_comment'] = true;
        } 
//        if(pnSecAuthAction(0, 'pnForum::', ":".$topic['forum_id'].":", ACCESS_COMMENT)) {
//            $topic['access_comment'] = true;
//        }                                                                                                                                              
        $topic['forum_mods'] = pnForum_userapi_get_moderators(array('forum_id' => $topic['forum_id']));
        
        /**
         * update topic counter
         */
        $sql = "UPDATE ".$pntable['pnforum_topics']."
                SET topic_views = topic_views + 1
                WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
        $result = $dbconn->Execute($sql);
        if($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }
        
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

        if(isset($start)) {
            // $start is given
            $result2 = $dbconn->SelectLimit($sql2, $posts_per_page, $start);
        } else {
            $result2 = $dbconn->SelectLimit($sql2, $posts_per_page);
        }
        if($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql2,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
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
            $sig = $posterdata['pn_user_sig'];
            if (!empty($post['poster_data']['pn_user_sig'])){
                    $post['post_text'] = eregi_replace("\[addsig]$", "\n_________________\n".$post['poster_data']['pn_user_sig'], $post['post_text']);
            } else {
                    $post['post_text'] = eregi_replace("\[addsig]$", "", $post['post_text']);
            }
            // call hooks for $message
            list($post['post_text']) = pnModCallHooks('item', 'transform', '', array($post['post_text']));
            $post['post_text'] = pnVarPrepHTMLDisplay(pnVarCensor(nl2br($post['post_text'])));

            //display table footer
//            if($topic['topic_status'] != 1) {
                // topic is not locked
            $pn_uid = pnUserGetVar('uid');
            if ($post['poster_data']['pn_uid']==$pn_uid) {
//                    allowedtomoderatecategoryandforum($topic['cat_id'], $topic['forum_id'])) {
//                    pnSecAuthAction(0, 'pnForum::Forum', $topic['forum_name'] ."::", ACCESS_MODERATE) || pnSecAuthAction(0, 'pnForum::Category', $topic['cat_title'] ."::", ACCESS_MODERATE)) {
                    // user is allowed to moderate || own post
                $post['poster_data']['moderate'] = true;
            }
//                if (pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_COMMENT) || pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_COMMENT)) {
            if(allowedtowritetocategoryandforum($topic['cat_id'], $topic['forum_id'])) {
                // user is allowed to reply
                $post['poster_data']['reply'] = true;
            }
//            } else {
//                // topic is locked
//            }

//            if( (pnSecAuthAction(0, 'pnForum::Forum', $topic['forum_name'] ."::", ACCESS_MODERATE) || 
//                pnSecAuthAction(0, 'pnForum::Category', $topic['cat_title'] ."::", ACCESS_MODERATE) ) && 
            if(allowedtomoderatecategoryandforum($topic['cat_id'], $topic['forum_id']) &&
                pnModGetVar('pnForum', 'log_ip') == "yes") {
                // user is allowed to see ip
                $post['poster_data']['seeip'] = true;
                $post['poster_data']['moderate'] = true;
            }
            array_push($topic['posts'], $post);
            $result2->MoveNext();
        }
        $result2->Close();
    } else {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    }
    $result->Close();
    
    return $topic;
}

/** 
 * preparereply
 * prepapare a reply to a posting by reading the last ten postign in revers order for review
 *
 *@params $args['forum_id'] int the forums id
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $reply = array();

    if(!empty($post_id)) {
        // We have a post id, so include that in the checks..
        $sql = "SELECT f.forum_name,
                       c.cat_id,
                       c.cat_title,
                       t.topic_title,
                       t.topic_status
                FROM ".$pntable[pnforum_forums]." AS f, 
                     ".$pntable[pnforum_topics]." AS t, 
                     ".$pntable[pnforum_posts]." AS p,
                     ".$pntable[pnforum_categories]." AS c
                WHERE (f.forum_id = '".(int)pnVarPrepForStore($forum_id)."')
                AND (t.topic_id = '".(int)pnVarPrepForStore($topic_id)."')
                AND (p.post_id = '".(int)pnVarPrepForStore($post_id)."')
                AND (t.forum_id = f.forum_id)
                AND (p.forum_id = f.forum_id)
                AND (p.topic_id = t.topic_id)
                AND (c.cat_id = f.cat_id)";
    } else {
        // No post id, just check forum and topic.
        $sql = "SELECT f.forum_name,
                       c.cat_id,
                       c.cat_title,
                       t.topic_title,
                       t.topic_status
                FROM ".$pntable[pnforum_forums]." AS f, 
                     ".$pntable[pnforum_topics]." AS t,
                     ".$pntable[pnforum_categories]." AS c
                WHERE (f.forum_id = '".(int)pnVarPrepForStore($forum_id)."')
                AND (t.topic_id = '".(int)pnVarPrepForStore($topic_id)."')
                AND (t.forum_id = f.forum_id)
                AND (c.cat_id = f.cat_id)";
    }
    
    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg());
    }
    
    if ($result->EOF) {
        return showforumerror(_PNFORUM_FORUM_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    
    $reply['forum_name'] = pnVarPrepForDisplay($myrow['forum_name']);
    $reply['forum_id'] = pnVarPrepForDisplay($forum_id);
    $reply['cat_id'] = pnVarPrepForDisplay($cat_id);
    $reply['cat_title'] = pnVarPrepForDisplay($myrow['cat_title']);
    $reply['topic_subject'] = pnVarPrepForDisplay($myrow['topic_title']);
    $reply['topic_status'] = pnVarPrepForDisplay($myrow['topic_status']);
    $reply['topic_id'] = pnVarPrepForDisplay($topic_id);

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
    
//    if (!pnSecAuthAction(0, 'pnForum::Forum', $reply['forum_name'] ."::", ACCESS_COMMENT) && 
//        !pnSecAuthAction(0, 'pnForum::Category', $reply['cat_title'] ."::", ACCESS_COMMENT)) {
    if(!allowedtowritetocategoryandforum($reply['cat_id'], $reply['forum_id'])) {
        return showforumerror( _PNFORUM_NOAUTH_TOWRITE, __FILE__, __LINE__);
    }

    if($quote==true) {
        $sql = "SELECT pt.post_text, 
                       p.post_time, 
                       u.pn_uname
                FROM ".$pntable['pnforum_posts']." p,
                    ".$pntable['users']." u,
                    ".$pntable['pnforum_posts_text']." pt
                WHERE p.post_id = '".(int)pnVarPrepForStore($post_id)."'
                AND p.poster_id = u.pn_uid
                AND pt.post_id = p.post_id";
    
        $r = $dbconn->Execute($sql);
    
        if($dbconn->ErrorNo() == 0) {
            $m = $r->GetRowAssoc(false);
            // just for backwards compatibility
            // does read unused smiles tables - do we need this??
            //$text = desmile($m['post_text']);
            // just for backwards compatibility
            $text = pn_bbdecode($m['post_text']);
            $text = eregi_replace("\[quote\]", ">>", $text);
            $text = eregi_replace("\[/quote\]", "<<", $text);
            $text = preg_replace('/(<br[ \/]*?>)/i', "", $text);
            // just for backwards compatibility
            $text = undo_make_clickable($text);
            $text = str_replace("[addsig]", "", $text);
            $reply['message'] = '[quote]'.$text.'[/quote]';
        } else {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg());
        }
        $r->Close();
    } else {
        $reply['message'] = "";
    }

    // Topic review (show last 10)
    $sql = "SELECT p.poster_id, 
                   p.post_time, 
                   pt.post_text, 
                   t.topic_title
                    FROM $pntable[pnforum_posts_text] pt, $pntable[pnforum_posts] p
                        LEFT JOIN $pntable[pnforum_topics] t ON t.topic_id=p.topic_id
                        WHERE p.topic_id = '$topic_id' AND p.post_id = pt.post_id
                        ORDER BY p.post_id DESC";

    $result = $dbconn->SelectLimit($sql, 10);

    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql2,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

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
        $posted_unixtime= strtotime ($review['post_time']);
        $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($posted_unixtime));
    
        $message = $review['post_text'];
        // we use br2nl here for backward compatibility
        $message = phpbb_br2nl($message);
        // Before we insert the sig, we have to strip its HTML if HTML is disabled by the admin.
    
        // We do this _before_ pn_bbencode(), otherwise we'd kill the bbcode's html.
        $sig = $review['poster_data']['pn_user_sig'];
        if(!empty($sig)){
            $message = eregi_replace("\[addsig]$", "\n_________________\n$sig", $message);
        }
        else {
            $message = eregi_replace("\[addsig]$", "", $message);
        }

        // call hooks for $message
        list($message) = pnModCallHooks('item', 'transform', '', array($message));
        $review['post_text'] = $message;
        
        array_push($reply['topic_review'], $review);
        $result->MoveNext();
    }
    return $reply;
}

/**
 * storereply
 * store the users reply in the database
 *
 *@params $args['message'] string the text
 *@params $args['topic_id'] int the topics id
 *@params $args['forum_id'] int the forums id
 *@returns int the number of the posting to show in the topic affter adding 
 */
function pnForum_userapi_storereply($args)
{
    extract($args);
    unset($args);
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

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
        $message .= "[addsig]";
        $pn_uid = pnUserGetVar('uid');
    } else {
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

    // Prep for DB
    $time = date("Y-m-d H:i");
    $topic_id = pnVarPrepForStore($topic_id);
    $message = pnVarPrepForStore($message);
    $forum_id = pnVarPrepForStore($forum_id);
    $pn_uid = pnVarPrepForStore($pn_uid);
    $time = pnVarPrepForStore($time);
    $poster_ip = pnVarPrepForStore($poster_ip);

    // insert values into posts-table
    $postid = $dbconn->GenID($pntable['pnforum_posts']);
    $sql = "INSERT INTO $pntable[pnforum_posts]
                        (post_id, topic_id, forum_id, poster_id, post_time, poster_ip)
                        VALUES
                        ('".pnVarPrepForStore($postid)."', '$topic_id', '$forum_id', '$pn_uid','$time', '$poster_ip')";

    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg());
    }

    $this_post = $dbconn->PO_Insert_ID($pntable['pnforum_posts'], 'post_id');
    if($this_post) {
        $sql = "INSERT INTO $pntable[pnforum_posts_text]
                (post_id, post_text)
                VALUES
                ('".pnVarPrepForStore($this_post)."', '$message')";

        $result = $dbconn->Execute($sql);

        if($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg());
        }
    }

    // update topics-table
    $sql = "UPDATE $pntable[pnforum_topics]
            SET topic_replies = topic_replies+1, topic_last_post_id = '".pnVarPrepForStore($this_post)."', topic_time = '$time'
            WHERE topic_id = '$topic_id'";

    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg());
    }

    if(pnUserLoggedIn()) {
        // user logged in we have to update users-table
        $sql = "UPDATE $pntable[pnforum_users]
                SET user_posts=user_posts+1
                WHERE (user_id = $pn_uid)";

        $result = $dbconn->Execute($sql);

        if ($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg());
        }
    }

    // update forums-table
    $sql = "UPDATE $pntable[pnforum_forums]
            SET forum_posts = forum_posts+1, forum_last_post_id = '" . pnVarPrepForStore($this_post) . "'
            WHERE forum_id = '$forum_id'";

    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
       return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

    //$topic_id=$topic;
    
    pnForum_userapi_notify_by_email(array('topic_id'=>$topic_id, 'poster_id'=>$pn_uid, 'post_message'=>$posted_message, 'type'=>'2'));
    
    // get topic_replies for correct redirect
    $sql = "SELECT topic_replies FROM $pntable[pnforum_topics] WHERE topic_id = '$topic_id'";
    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg());
    }
    list ($topic_replies) = $result->fields;

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
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $sql = "SELECT user_id from ".$pntable['pnforum_topic_subscription']." 
            WHERE user_id = '".(int)pnVarPrepForStore($userid)."' AND topic_id = '".(int)pnVarPrepForStore($topic_id)."'";

    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
       return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    if($result->RecordCount()>0) {
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
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $sql = "SELECT user_id from ".$pntable['pnforum_subscription']." 
            WHERE user_id = '".(int)pnVarPrepForStore($userid)."' AND forum_id = '".(int)pnVarPrepForStore($forum_id)."'";
    
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
       return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    if($result->RecordCount()>0) {
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
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

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
    
    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
       return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
        
    $myrow = $result->GetRowAssoc(false);
    $newtopic['cat_id']     = $myrow['cat_id'];
    $newtopic['forum_name'] = pnVarPrepForDisplay($myrow['forum_name']);
    $newtopic['cat_title']  = pnVarPrepForDisplay($myrow['cat_title']);

    // need at least "comment" to add newtopic
//    if (!pnSecAuthAction(0, 'pnForum::Forum', $newtopic['forum_name'] ."::", ACCESS_COMMENT) && 
//        !pnSecAuthAction(0, 'pnForum::Category', $newtopic['cat_title'] ."::", ACCESS_COMMENT)) {
    if(!allowedtowritetocategoryandforum($newtopic['cat_id'], $newtopic['forum_id'])) {
        // user is not allowed to post
        return showforumerror(_PNFORUM_NOAUTH_TOWRITE, __FILE__, __LINE__);
    }
    $newtopic['poster_data'] = pnForum_userapi_get_userdata_from_id(array('userid' => pnUserGetVar('uid')));

    $newtopic['subject'] = $subject;
    $newtopic['message'] = $message;
    $newtopic['message_display'] = phpbb_br2nl($message);
    $sig = $newtopic['poster_data']['pn_user_sig'];
    if ($sig != ''){
        $newtopic['message'] .= "<br />_________________<br />$sig";
    }
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
 *@returns int the new topics id
 */
function pnForum_userapi_storenewtopic($args)
{
    extract($args);
    unset($args);
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

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
        $message .= "[addsig]";
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
    
    $time = date("Y-m-d H:i");

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

    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
   
    //  insert values into posts-table   
    $topic_id = $dbconn->PO_Insert_ID($pntable['pnforum_topics'], 'topic_id');
    
    $post_id = $dbconn->GenID($pntable['pnforum_posts']);
    $sql = "INSERT INTO ".$pntable['pnforum_posts']." 
            (post_id, topic_id, forum_id, poster_id, post_time, poster_ip) 
            VALUES 
            ('".pnVarPrepForStore($post_id)."', '".pnVarPrepForStore($topic_id)."', '$forum_id', '$pn_uid', '$time', '$poster_ip')";

    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    } else {
        $post_id = $dbconn->PO_Insert_ID($pntable['pnforum_posts'], 'post_id');
        if($post_id)
        {
            //  insert values into posts_text-table
            $sql = "INSERT INTO ".$pntable['pnforum_posts_text']." 
            (post_id, post_text) 
            VALUES ('".pnVarPrepForStore($post_id)."', '$message')";

            $result = $dbconn->Execute($sql);
            
            if($dbconn->ErrorNo() != 0)
            {
                return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
            }
            //  updates topics-table
            $sql = "UPDATE ".$pntable['pnforum_topics']." 
                    SET topic_last_post_id = '".pnVarPrepForStore($post_id)."' 
                    WHERE topic_id = '".pnVarPrepForStore($topic_id)."'";

            $result = $dbconn->Execute($sql);
        
            if($dbconn->ErrorNo() != 0)
            {
                return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
            }
        }
    }

    if(pnUserLoggedin()) {
        // user logged in we have to update users-table
        $sql = "UPDATE $pntable[pnforum_users] 
                SET user_posts=user_posts+1 
                WHERE (user_id = $pn_uid)";

        $result = $dbconn->Execute($sql);
        
        if ($dbconn->ErrorNo() != 0) {
           return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }
    }
    //  update forums-table
    $sql = "UPDATE $pntable[pnforum_forums] 
            SET forum_posts = forum_posts+1, forum_topics = forum_topics+1, forum_last_post_id = '" . pnVarPrepForStore($post_id) . "' 
            WHERE forum_id = '$forum_id'";

    $result = $dbconn->Execute($sql);
    
    if ($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
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
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();
    
    // we know about the topic_id, let's find out the forum and catgeory name for permission checks
    $sql = "SELECT p.post_id, 
                    p.post_time, 
                    pt.post_text, 
                    p.poster_id, 
                    t.topic_id,
                    t.topic_title, 
                    t.topic_notify,
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

    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }

    $post = array();
    $post['post_id']      = pnVarPrepForDisplay($myrow['post_id']);
    $post['post_time']    = pnVarPrepForDisplay($myrow['post_time']);
    $message              = pnVarPrepForDisplay($myrow['post_text']);
    $post['topic_id']     = pnVarPrepForDisplay($myrow['topic_id']);
    $post['topic_subject']= pnVarPrepForDisplay($myrow['topic_title']);
    $post['topic_notify'] = pnVarPrepForDisplay($myrow['topic_notify']);
    $post['forum_id']     = pnVarPrepForDisplay($myrow['forum_id']);
    $post['forum_name']   = pnVarPrepForDisplay($myrow['forum_name']);
    $post['cat_title']    = pnVarPrepForDisplay($myrow['cat_title']);
    $post['cat_id']       = pnVarPrepForDisplay($myrow['cat_id']);
    $post['poster_data'] = pnForum_userapi_get_userdata_from_id(array('userid' => $myrow['poster_id']));

//    if (!pnSecAuthAction(0, 'pnForum::Forum', $post['forum_name'] ."::", ACCESS_COMMENT) && 
//        !pnSecAuthAction(0, 'pnForum::Category', $post['cat_title'] ."::", ACCESS_COMMENT)) {
    if(!allowedtowritetocategoryandforum($post['cat_id'], $post['forum_id'])) {
        return showforumerror(_PNFORUM_NOAUTH_TOWRITE, __FILE__, __LINE__);
    }

    $pn_uid = pnUserGetVar('uid');   
//    if (pnSecAuthAction(0, 'pnForum::Forum', $post['forum_name'] ."::", ACCESS_MODERATE) || 
//        pnSecAuthAction(0, 'pnForum::Category', $post['cat_title'] ."::", ACCESS_MODERATE) || 
    if(allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id']) ||
        ($pn_uid == $post['poster_data']['pn_uid']))   {
        // user is allowed to edit the post
        $post['access_edit'] = true;
    } else {
        //return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__);
        $post['access_edit'] = false;
    }

    $message_display = nl2br($message);  // phpbb_br2nl($message);
    $sig = $post['poster_data']['pn_user_sig'];
    if ($sig != ''){
        $message_display .= "<br />_________________<br />$sig";
    }
    // call hooks for $message_display ($message remains untouched for the textarea)
    list($message_display) = pnModCallHooks('item', 'transform', '', array($message_display));
    $post['message_display'] = nl2br($message_display);

    //  remove [addsig]
    $message = eregi_replace("\[addsig]$", "", $message);
    //  remove <!-- editby -->
    $message = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $message);
    //  convert <br /> to \n (since nl2br only inserts additional <br /> we just need to remove them
    //$message = eregi_replace('<br />', "", $message);
    $message = phpbb_br2nl($message);
    //  convert smilies (just for backwards compatibility)
    // does read unused smiles tables - do we need this??
    //$message = desmile($message);
    //  convert bbcode (just for backwards compatibility)
    $message = pn_bbdecode($message);
    //  convert autolinks (just for backwards compatibility)
    $message = undo_make_clickable($message);
    $post['message'] = $message;

    // allow to edit the subject if irst post
    $post['first_post'] = pnForum_userapi_is_first_post(array('topic_id' => $topic_id, 'post_id' => $post_id));

    $post['moderate'] = false;
//    if(pnSecAuthAction(0, 'pnForum::Forum', $post['forum_name'] . "::", ACCESS_MODERATE) || 
//        pnSecAuthAction(0, 'pnForum::Category', $post['cat_title'] ."::", ACCESS_MODERATE) || 
    if(allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id']) ||
        ($pn_uid == $post['poster_data']['pn_uid'])) { 
        $post['moderate'] = true; 
    }
     
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $sql = "SELECT post_id FROM ".$pntable['pnforum_posts']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."' 
            ORDER BY post_id 
            LIMIT 1";
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
       return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    if($result->RecordCount()>0) {
        list($read_post_id) = $result->fields;
        if($post_id == $read_post_id) {
            return true;
        }
    }
    return false;
}

/**
 * update post
 * updates a posting in the db after editing it
 *
 *@params $args['topic_id'] int the topics id
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $sql = "SELECT t.topic_title, 
                   t.topic_status,
                   t.forum_id, 
                   f.forum_name, 
                   f.cat_id, 
                   c.cat_title
            FROM  ".$pntable['pnforum_topics']." t
            LEFT JOIN ".$pntable['pnforum_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['pnforum_categories']." AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";

    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
       return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    
    $forum_id = pnVarPrepForDisplay($myrow['forum_id']);
    $forum_name = pnVarPrepForDisplay($myrow['forum_name']);
    $cat_id = pnVarPrepForDisplay($myrow['cat_id']);
    $cat_title = pnVarPrepForDisplay($myrow['cat_title']);
    $topic_subject = pnVarPrepForDisplay($myrow['topic_title']);
    $topic_status = pnVarPrepForDisplay($myrow['toic_status']);

    $sql = "SELECT p.*, u.pn_uname, 
                   u.pn_uid                 
            FROM ".$pntable['pnforum_posts']." p, 
                ".$pntable['users']." u 
            WHERE (p.post_id = '".(int)pnVarPrepForStore($post_id)."')  
            AND (p.poster_id = u.pn_uid)";

    $result = $dbconn->Execute($sql);
    
    if ($dbconn->ErrorNo() != 0) {
       return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    if ($result->PO_RecordCount() <= 0) {
       return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    $myrow = $result->GetRowAssoc(false);
    $poster_id = $myrow['pn_uid'];
    $pn_uid = pnUserGetVar('uid');
//    $forum_id = $myrow['forum_id'];
//    $topic_id = $myrow['topic_id'];
    $this_post_time = $myrow['post_time'];
    $edit_date = ml_ftime(_DATETIMEBRIEF, GetUserTime(time()));
//  $forum_name = pnVarPrepForDisplay(get_forum_name($forum_id));
//  $cat_title = pnVarPrepForDisplay(get_category_name($forum_id));

    if (!($pn_uid == $poster_id) && 
        !allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
//        !pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_MODERATE) && 
//        !pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_MODERATE)) {
        // user is not allowed to edit post
        return showforumerror( _PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }
    
    if(($topic_status == 1) && 
        allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
//        !pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_MODERATE) && 
//        !pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_MODERATE)) {
        // topic is locked, user is not moderator
        return showforumerror( _PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }

    if(trim($message) == '') {
        // no message
        return showforumerror( _PNFORUM_EMPTYMSG, __FILE__, __LINE__);
    }

    /**
     * it's a submitted page and message is not empty
     */
    
//    if ( !pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_ADMIN) && 
//        !pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_ADMIN) ) {
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
        $message .= " <!-- editby --><br /><br /><em>"._PNFORUM_EDITBY." $editname, $edit_date</em><!-- end editby --> ";
    }

    // add signature placeholder
    if ($poster_id <> 1){
        $message .= "[addsig]";
    }

    $message = pnVarPrepForStore($message);

    if (empty($delete)) {
        $delete=false;
    }

    if (empty($delete)) {
        //  topic should not be deleted
        $topic = $topic_id;
        $forum = $forum_id;
        $sql = "UPDATE ".$pntable['pnforum_posts_text']." 
                SET post_text = '$message' 
                WHERE (post_id = '".(int)pnVarPrepForStore($post_id)."')";
        
        $result = $dbconn->Execute($sql);
        
        if($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }
        
        if (!empty ($subject)) { 
            //  topic has a new subject
            if (trim($subject) != '') {
                //$subject = censor($subject);
                $subject = pnVarPrepForStore($subject);
                $sql = "UPDATE ".$pntable['pnforum_topics']." 
                        SET topic_title = '$subject' 
                        WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
                
                $result = $dbconn->Execute($sql);
                
                if ($dbconn->ErrorNo() != 0) {
                    return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
                }
            }
        }
        
        return pnModURL('pnForum', 'user', 'viewtopic',
                        array('topic' => $topic_id)); 

    } else {
        /**
         * we are going to delete message
         */
        $now_hour = date('H');
        $now_min = date('i');
        list($hour, $min) = split(':', $time);

        // NOT ((time is good) OR (user is allowed to moderate this forum))
        if (! ( (($now_hour == $hour && $now_min - 30 < $min) 
                || ($now_hour == $hour +1 && $now_min - 30 > 0)
                && ($pn_uid == $poster_id)) || 
                allowedtomoderatecategoryandforum($cat_id, $forum_id)) ){
//                pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_MODERATE || 
//                pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_MODERATE)) ) ) {
            return showforumerror( _PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
        }
        $last_post_in_thread = pnForum_userapi_get_last_boardpost(array('id'=>$topic_id, 'type'=> "time_fix"));

        // get the original author so that we can decrement his postcount later on
        $result=$dbconn->Execute("SELECT poster_id 
                                    FROM ".$pntable['pnforum_posts']." 
                                    WHERE post_id = '".(int)pnVarPrepForStore($post_id)."'");
        list($pn_uid) = $result->fields;
            
        // delete the post from the posts table
        $sql = "DELETE FROM ".$pntable['pnforum_posts']." 
                WHERE post_id = '".(int)pnVarPrepForStore($post_id)."'";
        
        $result = $dbconn->Execute($sql);
        
        if ($dbconn->ErrorNo() != 0) {
           return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }

        // delete the post from the posts_text table
        $sql = "DELETE FROM ".$pntable['pnforum_posts_text']." 
                WHERE post_id = '".(int)pnVarPrepForStore($post_id)."'";
        $result = $dbconn->Execute($sql);

        if ($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        } elseif ($last_post_in_thread == $this_post_time) {
            // update the last posts stats
            $topic_time_fixed = pnForum_userapi_get_last_boardpost(array('id' => $topic_id, 'type' => 'time_fix'));
            $sql = "UPDATE ".$pntable['pnforum_topics']." 
                    SET topic_time = '".pnVarPrepForStore($topic_time_fixed)."' 
                    WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
            $result = $dbconn->Execute($sql);
            if ($dbconn->ErrorNo() != 0) {
                return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
            }
        }
        $topic_removed = false;
        
        if(pnForum_userapi_boardstats(array('id' => $topic_id, 'type' => "topic")) == 0) {
            // it was the last post in the thread, update topics table
            $sql = "DELETE FROM ".$pntable['pnforum_topics']." 
                    WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
            $result = $dbconn->Execute($sql);
            if($dbconn->ErrorNo() != 0) {
                return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
            }
            $topic_removed = true;
        }
        
        if(!empty($pn_uid)) {
            // decrement the author's posting count
            $sql = "UPDATE ".$pntable['pnforum_users']." 
                    SET user_posts = user_posts - 1 
                    WHERE user_id = '".(int)pnVarPrepForStore($pn_uid)."'";
            $result = $dbconn->Execute($sql);
            if ($dbconn->ErrorNo() != 0) {
                return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
            }
        }
        pnModAPIFunc('pnForum', 'admin', 'sync',
                     array('id' => $forum_id,
                           'type' => 'forum'));
        if (!$topic_removed) {
            pnModAPIFunc('pnForum', 'admin', 'sync',
                         array('id' => $topic_id,
                               'type' => 'topic'));
        }
        
        // we need to check here if this topic exists
        $sql = "SELECT * FROM $pntable[pnforum_posts] 
                WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
        $result = $dbconn->Execute($sql);
        if ($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }
        
        if ($result->PO_RecordCount() == 0) {
            // the post was last in topic, redirect to the forum
            return pnModURL('pnForum', 'user', 'viewforum',
                            array('forum' => $forum_id)); 
        } else {
            // redirect to the topic
            return pnModURL('pnForum', 'user', 'viewtopic',
                            array('topic' => $topic_id)); 
        }    
    }
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
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $sql = "SELECT p.post_time, u.pn_uname 
            FROM ".$pntable['pnforum_posts']." p, ".$pntable['users']." u 
            WHERE p.topic_id = '".(int)pnVarPrepForStore($id)."' 
            AND p.poster_id = u.pn_uid 
            ORDER BY post_time DESC";

    $result=$dbconn->SelectLimit($sql, 1);
    $row = $result->GetRowAssoc(false);
    $uname = $row['pn_uname'];
    $post_time = $row['post_time'];
    
    // format the return string
    if ($type == 'topic') {
        $userlink = "<a href=\"user.php?op=userinfo&amp;uname=".$uname."\">".$uname."</a>";
        // correct the time
        $posted_unixtime= strtotime ($post_time);
        $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($posted_unixtime));
        $val = "<td><span class=\"pn-normal\">$userlink</span></td><td><span class=\"pn-normal\">$posted_ml</span></td>";
    }
    if ($type == 'time_fix') {
        $val = $post_time;
    }
    return($val);   
}
 
/** 
 * get_viewip_data
 *
 *@params $args['pos_id] int the postings id
 *@returns array with informstion ...
 */
function pnForum_userapi_get_viewip_data($args)
{
    extract($args);
    unset($args);
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $viewip = array();
    
    $sql = "SELECT u.pn_uname, p.poster_ip 
            FROM ".$pntable['users']." u, ".$pntable['pnforum_posts']." p 
            WHERE p.post_id = '".(int)pnVarPrepForStore($post_id)."'  
            AND u.pn_uid = p.poster_id";
    $r = $dbconn->execute($sql);
    
    if($dbconn->ErrorNo() != 0)
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    if($r->EOF) {
        // TODO we have valid user here, but he didn't has posts
        return showforumerror(_PNFORUM_NOUSER_OR_POST, __FILE__, __LINE__);
    } else {
        $m = $r->GetRowAssoc(false);
    }
    $viewip['poster_ip']     = $m['poster_ip'];
    $viewip['poster_host'] = gethostbyaddr($m['poster_ip']);

    $sql = "SELECT pn_uid, pn_uname, count(*) AS postcount 
            FROM ".$pntable['pnforum_posts']." p, ".$pntable['users']." u 
            WHERE poster_ip='".$m['poster_ip']."' && p.poster_id = u.pn_uid 
            GROUP BY pn_uid";
    
    $r = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0)
    {
        error_die(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg());
    }

    $viewip['users'] = array();
    while (!$r->EOF) {
        $row = $r->GetRowAssoc(false);
        $user = array();
        $user['pn_uid']    = $row['pn_uid'];
        $user['pn_uname']  = $row['pn_uname'];
        $user['postcount'] = $row['postcount'];
        array_push($viewip['users'], $user);
        $r->MoveNext();
    }
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    list($forum_name, $cat_title, $forum_id, $cat_id) = pnForum_userapi_get_forumtitle_and_categorytitle_from_topicid(array('topic_id'=>$topic_id));
    if(!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
//    if (!pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_MODERATE) && !pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_MODERATE)) {
        return showforumerror(_PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }

    $new_status = ($mode=="lock") ? 1 : 0; 

    $sql = "UPDATE ".$pntable['pnforum_topics']." 
            SET topic_status = $new_status 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    list($forum_name, $cat_title, $forum_id, $cat_id) = pnForum_userapi_get_forumtitle_and_categorytitle_from_topicid(array('topic_id'=>$topic_id));
//    if (!pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_MODERATE) && !pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_MODERATE)) {
    if(!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        return showforumerror(_PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }

    $new_sticky = ($mode=="sticky") ? 1 : 0; 

    $sql = "UPDATE ".$pntable['pnforum_topics']." 
            SET sticky = '$new_sticky' 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    return;
}

/**
 * get_forumtitle_and categorytitle_from_topicid
 * used for permission checks
 *
 *@params $args['topic_id'] int the topics id
 *@returns array(forum_name, category_title, forum_id, category_id)
 */
function pnForum_userapi_get_forumtitle_and_categorytitle_from_topicid($args)
{
    extract($args);
    unset($args);
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    // we know about the topic_id, let's find out the forum and catgeory name for permission checks
    $sql = "SELECT f.forum_name,
                   f.forum_id,
                   c.cat_title,
                   c.cat_id
            FROM  ".$pntable['pnforum_topics']." t
            LEFT JOIN ".$pntable['pnforum_forums']." f ON f.forum_id = t.forum_id
            LEFT JOIN ".$pntable['pnforum_categories']." AS c ON c.cat_id = f.cat_id
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    
    $forum_name = pnVarPrepForDisplay($myrow['forum_name']);
    $cat_title = pnVarPrepForDisplay($myrow['cat_title']);
    $forum_id = pnVarPrepForDisplay($myrow['forum_id']);
    $cat_id = pnVarPrepForDisplay($myrow['cat_id']);
    
    return array( $forum_name, $cat_title, $forum_id, $cat_id);
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
//    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_READ)) { 
            return showforumerror(_PNFORUM_NOAUTH_TOSEE, __FILE__, __LINE__); 
        }
    }
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();
    
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

    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

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
//            if  (pnSecAuthAction(0, 'pnForum::Forum', $forum['forum_name'] . "::", ACCESS_READ) && pnSecAuthAction(0, 'pnForum::Category', $forum['cat_title'] . "::", ACCESS_READ)) {
            if(allowedtoseecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
                array_push( $forums, $forum );
            }
        }
    }
    $result->Close();
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();
      
    // get the old forum id
    $sql = "SELECT t.forum_id
            FROM  ".$pntable['pnforum_topics']." t
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    
    $oldforum_id = pnVarPrepForDisplay($myrow['forum_id']);

    if($oldforum_id <> $forum_id) {
        // set new forum id
        $sql = "UPDATE ".$pntable['pnforum_topics']." 
                SET forum_id = '".(int)pnVarPrepForStore($forum_id)."' 
                WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
        $result = $dbconn->Execute($sql);
        if($dbconn->ErrorNo() != 0)
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        
        $sql = "UPDATE ".$pntable['pnforum_posts']." 
                SET forum_id = '".(int)pnVarPrepForStore($forum_id)."' 
                WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
        $result = $dbconn->Execute($sql);
        if ($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    // get the forum id
    $sql = "SELECT t.forum_id
            FROM  ".$pntable['pnforum_topics']." t
            WHERE t.topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    
    $forum_id = pnVarPrepForDisplay($myrow['forum_id']);

    // Update the users's post count, this might be slow on big topics but it makes other parts of the
    // forum faster so we win out in the long run.
    $sql = "SELECT poster_id, post_id 
            FROM ".$pntable['pnforum_posts']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $r = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    while (!$r->EOF) {
        $row = $r->GetRowAssoc(false);
        if($row['poster_id'] != -1) {
            $sql = "UPDATE ".$pntable['pnforum_users']." 
                    SET user_posts = user_posts - 1 
                    WHERE user_id = '".$row['poster_id']."'";
                
            $result = $dbconn->Execute($sql);
        }
        $r->MoveNext();
    }

    // Get the post ID's we have to remove.
    $sql = "SELECT post_id FROM ".$pntable['pnforum_posts']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
        
    $r = $dbconn->Execute($sql);
        
    if($dbconn->ErrorNo() != 0) {
          return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    // we need to put a check here if we have more posts...     
    while (!$r->EOF) {
        $row = $r->GetRowAssoc(false);
        $posts_to_remove[] = $row['post_id'];
        $r->MoveNext();
    }

    $sql = "DELETE FROM ".$pntable['pnforum_posts']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
    
    $result = $dbconn->Execute($sql);
      
    if($dbconn->ErrorNo() != 0) {
          return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    $sql = "DELETE FROM ".$pntable['pnforum_topics']." 
            WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";
      
    $result = $dbconn->Execute($sql);
      
    if($dbconn->ErrorNo() != 0) {
          return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    $sql = "DELETE FROM ".$pntable['pnforum_posts_text']." 
            WHERE ";
    for($x = 0; $x < count($posts_to_remove); $x++) {
        if(isset($set)) {
            $sql .= " OR ";
        }
        $sql .= "post_id = '".$posts_to_remove[$x]."'";
        $set = TRUE;
    }
    
    $result = $dbconn->Execute($sql);
        
    if($dbconn->ErrorNo() != 0) {
          return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

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
        
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();
    
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

    $result = $dbconn->Execute($sql);
        
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_FORUM_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }

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
    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

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

    //  get list of topic_subscribers
    $sql = "SELECT user_id 
            FROM ".$pntable['pnforum_topic_subscription']." 
            WHERE topic_id=".(int)pnVarPrepForStore($topic_id)."";

    $result = $dbconn->Execute($sql);
        
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
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
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $userid = pnUserGetVar('uid');

    list($forum_name, $cat_title, $forum_id, $cat_id) = pnForum_userapi_get_forumtitle_and_categorytitle_from_topicid(array('topic_id'=>$topic_id));
    if(!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
//    if (!pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_READ) && !pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_READ)) {
        return showforumerror(_PNFORUM_NOAUTH_TOREAD, __FILE__, __LINE__);
    }
    
    if (pnForum_userapi_get_topic_subscription_status(array('userid'=>$userid, 'topic_id'=>$topic_id)) == false) {
        // add user only if not already subscribed to the topic
        $sql = "INSERT INTO ".$pntable['pnforum_topic_subscription']." (user_id, forum_id, topic_id) 
                VALUES ('".(int)pnVarPrepForStore($userid)."','".(int)pnVarPrepForStore($forum_id)."','".(int)pnVarPrepForStore($topic_id)."')";
        $result = $dbconn->Execute($sql);
        if($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }
    }
    return;
}

/**
 * unsubscribe_topic
 *
 *@params $args['topic_id'] int the topics id
 *@returns void
 */
function pnForum_userapi_unsubscribe_topic($args)
{
    extract($args);
    unset($args);
    
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $userid = pnUserGetVar('uid');

    if (pnForum_userapi_get_topic_subscription_status(array('userid'=>$userid, 'topic_id'=>$topic_id)) == true) {
        // user is subscribed, delete subscription
        $sql = "DELETE FROM ".$pntable['pnforum_topic_subscription']." 
                WHERE user_id='".(int)pnVarPrepForStore($userid)."' 
                AND topic_id='".(int)pnVarPrepForStore($topic_id)."'";              
        $result = $dbconn->Execute($sql);
        if($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }
    } else {
        // user is not subscribed
        return showforumerror(_PNFORUM_NOTSUBSCRIBED, __FILE__, __LINE__);
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $userid = pnUserGetVar('uid');

    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 
    $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                          array('forum_id' => $forum_id));
    if(!allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
//    if (!pnSecAuthAction(0, 'pnForum::Category', $forum['cat_title'] ."::", ACCESS_READ) && !pnSecAuthAction(0, 'pnForum::Forum', $forum['forum_name'] ."::", ACCESS_READ)) {
        return showforumerror(_PNFORUM_NOAUTH_TOREAD, __FILE__, __LINE__);
    }
    
    if (pnForum_userapi_get_forum_subscription_status(array('userid'=>$userid, 'forum_id'=>$forum_id)) == false) {
        // add user only if not already subscribed to the forum
        $sql = "INSERT INTO ".$pntable['pnforum_subscription']." (user_id, forum_id) 
                VALUES ('".(int)pnVarPrepForStore($userid)."','".(int)pnVarPrepForStore($forum_id)."')";
                
        $result = $dbconn->Execute($sql);
        if($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }
    }
    return;
}

/**
 * subscribe_forum
 *
 *@params $args['forum_id'] int the forums id
 *@returns void
 */
function pnForum_userapi_unsubscribe_forum($args)
{
    extract($args);
    unset($args);

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();
    
    $userid = pnUserGetVar('uid');
    
    if (pnForum_userapi_get_forum_subscription_status(array('userid'=>$userid, 'forum_id'=>$forum_id)) == true) {
        // user is subscribed, delete subscription
        $sql = "DELETE FROM ".$pntable['pnforum_subscription']." 
                WHERE user_id='".(int)pnVarPrepForStore($userid)."' 
                AND forum_id='".(int)pnVarPrepForStore($forum_id)."'";
                
        $result = $dbconn->Execute($sql);
        if($dbconn->ErrorNo() != 0) {
            return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
        }
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();
    
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
    
    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    
    if($result->EOF) {
        // no results - topic does not exist
        return showforumerror(_PNFORUM_TOPIC_NOEXIST, __FILE__, __LINE__);
    } else {
        $myrow = $result->GetRowAssoc(false);
    }
    
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
//    if ((!pnSecAuthAction(0, 'pnForum::Forum', $topic['forum_name'] ."::", ACCESS_READ)) || 
//        (!pnSecAuthAction(0, 'pnForum::Category', $topic['cat_title'] ."::", ACCESS_READ))) {
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

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();
    
    $posts_per_page = pnModGetVar('pnForum', 'posts_per_page');
    $post_sort_order = pnModGetVar('pnForum', 'post_sort_order');
    
    // some tricky sql
    $part1 = "SELECT    t.topic_id,
                        t.topic_title,
                        f.forum_id,
                        f.forum_name, 
                        c.cat_id,
                        c.cat_title,
                        t.topic_replies
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

    $result = $dbconn->Execute($sql);
    
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    $posts = array();
    while ((list($topic_id, $topic_title, $forum_id, $forum_name, $cat_id, $cat_title, $topic_replies) = $result->FetchRow()) ) {
        $post=array();
        $post['topic_id'] = pnVarPrepForDisplay($topic_id);
        $post['topic_title'] = pnVarPrepForDisplay(pnVarCensor($topic_title));
        $post['forum_id'] = pnVarPrepForDisplay($forum_id);
        $post['forum_name'] = pnVarPrepForDisplay($forum_name);
        $post['cat_id'] = pnVarPrepForDisplay($cat_id);
        $post['cat_title'] = pnVarPrepForDisplay($cat_title);
        $post['topic_replies'] = pnVarPrepForDisplay($topic_replies);
        
        // check permission before display
        if(allowedtoreadcategoryandforum($post['cat_id'], $post['forum_id'])) {
//        if ((pnSecAuthAction(0, 'pnForum::Forum', $post['forum_name'] ."::", ACCESS_READ))
//           && (pnSecAuthAction(0, 'pnForum::Category', $post['cat_title'] ."::", ACCESS_READ)))   {
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
            $post['last_boardpost'] = pnForum_userapi_get_last_boardpost(array('id'=>$topic_id, 'type'=> "topic"));

            array_push($posts, $post);
        }
    }
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

?>