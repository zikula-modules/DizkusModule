<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

/**
 * readlastposts
 * reads the last $maxposts postings of forum $forum_id and assign them in a
 * variable lastposts and the number of them in lastpostcount
 *
 *@params maxposts (int) number of posts to read, default = 5
 *@params forum_id (int) forum_id, if not set, all forums
 *@params user_id  (int) -1 = last postings of current user, otherwise its treated as an user_id
 *@params canread (bool) if set, only the forums that we have read access to [** flag is no longer supported, this is the default settings for now **]
 *@params favorites (bool) if set, only the favorite forums
 *@params show_m2f (bool) if set show postings from mail2forum forums
 *@params show_rss (bool) if set show postings from rss2forum forums
 *
 */
function smarty_function_readlastposts($params, &$smarty)
{
    extract($params);
    unset($params);

    $maxposts = (isset($maxposts) && is_numeric($maxposts) && $maxposts > 0) ? $maxposts : 5;
    // we limit maxposts to 100... just to be safe :-)
    $maxposts = ($maxposts>100) ? 100 : $maxposts;

    $loggedIn = pnUserLoggedIn();
    $uid = ($loggedIn == true) ? pnUserGetVar('uid') : 1;

    // get number of posts in db
    $numposts = pnModAPIFunc('pnForum', 'user', 'boardstats', array('type' => 'all'));
    if($numposts==0) {
        $smarty->assign('lastpostcount', 0);
        $smarty->assign('lastposts', array());
        return;
    }

    Loader::includeOnce('modules/pnForum/common.php');
    // get some enviroment
    list($dbconn, $pntable) = pnfOpenDB();

    $whereforum = "";
    if(!empty($forum_id) && is_numeric($forum_id)) {
        // get the category id and check permissions
        $cat_id = pnModAPIFunc('pnForum', 'user', 'get_forum_category',
                               array('forum_id' => $forum_id));
        if(!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            $smarty->assign('lastpostcount', 0);
            $smarty->assign('lastposts', array());
            return;
        }
        $whereforum = 't.forum_id = ' . DataUtil::formatForStore($forum_id) . ' AND ';
    } else {
        // no special forum_id set, get all forums the user is allowed to read
        // and build the where part of the sql statement
        $userforums = pnModAPIFunc('pnForum', 'user', 'readuserforums');
        if(!is_array($userforums) || count($userforums)==0) {
            // error or user is not allowed to read any forum at all
            $smarty->assign('lastpostcount', 0);
            $smarty->assign('lastposts', array());
            return;
        }
        
        foreach($userforums as $userforum) {
            if(strlen($whereforum)>0) {
                $whereforum .= ', ';
            }
            $whereforum .= $userforum['forum_id'];
        }
        $whereforum = 't.forum_id IN (' . DataUtil::formatForStore($whereforum) . ') AND';
       }

    $wherefavorites = '';
    // we only want to do this if $favorites is set and $whereforum is empty
    // and the user is logged in.
    // (Anonymous doesn't have favorites)
    if(isset($favorites) && $favorites && empty($whereforum) && $loggedIn) {
        // get the favorites
        $sql = 'SELECT fav.forum_id,
                       f.cat_id
                FROM ' . $pntable['pnforum_forum_favorites'] . ' fav
                LEFT JOIN ' . $pntable['pnforum_forums'] . ' f
                ON f.forum_id = fav.forum_id
                WHERE fav.user_id = ' . DataUtil::formatForStore($uid);
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        while (!$result->EOF) {
            list($forum_id, $cat_id) = $result->fields;
            if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
                $wherefavorites .= 'f.forum_id=' .  (int)DataUtil::formatForStore($forum_id) . ' OR ';
            }
            $result->MoveNext();
        }
        if (!empty($wherefavorites)) {
            $wherefavorites = '(' . rtrim($wherefavorites, 'OR ') . ') AND';
        }
        pnfCloseDB($result);
    }

    $wherespecial = ' (f.forum_pop3_active = 0';
    // if show_m2f is set we show contents of m2f forums where.
    // forum_pop3_active is set to 1
    if(isset($show_m2f) && $show_m2f==true) {
        $wherespecial .= ' OR f.forum_pop3_active = 1';
    }
    // if show_rss is set we show contents of rss2f forums where.
    // forum_pop3_active is set to 2
    if(isset($show_rss) && $show_rss==true) {
        $wherespecial .= ' OR f.forum_pop3_active = 2';
    }

    $wherespecial .= ') AND ';

    //check how much we have to read
    $postmax = ($numposts < $maxposts) ? $numposts : $maxposts;

    // user_id set?
    $whereuser = "";
    if(!empty($user_id)) {
        if($user_id==-1 && $loggedIn) {
            $whereuser = 'pt.poster_id = ' . DataUtil::formatForStore($uid) . ' AND ';
        } else {
            $whereuser = 'pt.poster_id = ' . DataUtil::formatForStore($user_id) . ' AND ';
        }
    }

    $sql = 'SELECT t.topic_id,
                   t.topic_title,
                   t.topic_poster,
                   t.topic_replies,
                   t.topic_time,
                   t.topic_last_post_id,
                   t.sticky,
                   t.topic_status,
                   t.topic_views,
                   f.forum_id,
                   f.forum_name,
                   c.cat_title,
                   c.cat_id,
                   p.poster_id,
                   p.post_id,
                   pt.post_text
        FROM ' . $pntable['pnforum_topics']     . ' as t,
             ' . $pntable['pnforum_forums']     . ' as f,
             ' . $pntable['pnforum_posts']      . ' as p,
             ' . $pntable['pnforum_posts_text'] . ' as pt,
             ' . $pntable['pnforum_categories'] . ' as c
        WHERE ' . $whereforum .'
              ' . $whereuser . '
              ' . $wherefavorites . '
              ' . $wherespecial . '
              t.forum_id = f.forum_id AND
              t.topic_last_post_id = p.post_id AND
              f.cat_id = c.cat_id AND
              pt.post_id = p.post_id
        ORDER by t.topic_time DESC';

    $lastposts = array();

    // if the user wants to see the last x postings we read 5 * x because
    // we might get to forums he is not allowed to see
    // we do this until we got the requested number of postings
    $result = pnfSelectLimit($dbconn, $sql, $postmax, 0, __FILE__, __LINE__);

    if($result->RecordCount()>0) {
        $post_sort_order = pnModAPIFunc('pnForum', 'user', 'get_user_post_order');
        $posts_per_page  = pnModGetVar('pnForum', 'posts_per_page');
        for (; !$result->EOF; $result->MoveNext()) {
            list($lastpost['topic_id'],
                 $lastpost['topic_title'],
                 $lastpost['topic_poster'],
                 $lastpost['topic_replies'],
                 $lastpost['topic_time'],
                 $lastpost['topic_last_post_id'],
                 $lastpost['sticky'],
                 $lastpost['topic_status'],
                 $lastpost['topic_views'],
                 $lastpost['forum_id'],
                 $lastpost['forum_name'],
                 $lastpost['cat_title'],
                 $lastpost['cat_id'],
                 $lastpost['poster_id'],
                 $lastpost['post_id'],
                 $lastpost['post_text']) = $result->fields;

            $lastpost['topic_title'] = DataUtil::formatforDisplay($lastpost['topic_title']);
            $lastpost['forum_name']  = DataUtil::formatforDisplay($lastpost['forum_name']);
            $lastpost['cat_title']   = DataUtil::formatforDisplay($lastpost['cat_title']);

            // backwards compatibility... :puke:
            $lastpost['title_tag'] = $lastpost['topic_title'];

            if($post_sort_order == "ASC") {
                $start = ((ceil(($lastpost['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page);
            } else {
                // latest topic is on top anyway...
                $start = 0;
            }
            $lastpost['start'] = $start;
            if ($lastpost['poster_id'] != 1) {
                $user_name = pnUserGetVar('uname', $lastpost['poster_id']);
                if ($user_name == "") {
                    // user deleted from the db?
                    $user_name = pnModGetVar('Users', 'anonymous');
                }
            } else {
                $user_name = pnModGetVar('Users', 'anonymous');
            }
            $lastpost['poster_name'] = DataUtil::formatForDisplay($user_name);

            $lastpost['post_text'] = pnForum_replacesignature($lastpost['post_text'], '');
            // call hooks for $message
            list($lastpost['post_text']) = pnModCallHooks('item', 'transform', '', array($lastpost['post_text']));
            $lastpost['post_text'] = DataUtil::formatForDisplay(nl2br($lastpost['post_text'])); // Removed pnVarCensor 

            $posted_unixtime= strtotime ($lastpost['topic_time']);
            $posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($posted_unixtime));
            $lastpost['posted_time'] =$posted_ml;
            $lastpost['posted_unixtime'] = $posted_unixtime;

            // we now create the url to the last post in the thread. This might be
            // on site 1, 2 or what ever in the thread, depending on topic_replies
            // count and the posts_per_page setting
            $lastpost['last_post_url'] = DataUtil::formatForDisplay(pnModURL('pnForum', 'user', 'viewtopic',
                                                             array('topic' => $lastpost['topic_id'],
                                                                   'start' => $lastpost['start'])));
            $lastpost['last_post_url_anchor'] = $lastpost['last_post_url'] . "#pid" . $lastpost['topic_last_post_id'];

            array_push($lastposts, $lastpost);
        }
    }

    pnfCloseDB($result);
    $smarty->assign('lastpostcount', count($lastposts));
    $smarty->assign('lastposts', $lastposts);
    return;
}
