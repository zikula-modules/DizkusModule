<?php
// $Id$
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------


/**
 * readtopforums
 * reads the last $maxforums forums and assign them in a
 * variable topforums and the number of them in topforumscount
 *
 *@params maxforums (int) number of forums to read, default = 5
 *
 */
function smarty_function_readtopforums($params, &$smarty) 
{
    extract($params); 
	unset($params);

    include_once('modules/pnForum/common.php');
    // get some enviroment
    list($dbconn, $pntable) = pnfOpenDB();

    $forummax = (!empty($maxforums)) ? $maxforums : 5;
    
    $sql = "SELECT f.forum_id, 
                   f.forum_name, 
                   f.forum_topics, 
                   f.forum_posts, 
                   c.cat_title,
                   c.cat_id
          FROM ".$pntable['pnforum_forums']." AS f, 
               ".$pntable['pnforum_categories']." AS c
          WHERE f.cat_id = c.cat_id
          ORDER BY forum_posts DESC";

    $result = pnfSelectLimit($dbconn, $sql, $forummax, false, __FILE__, __LINE__);
    $result_forummax = $result->PO_RecordCount();
    if ($result_forummax <= $forummax) {
        $forummax = $result_forummax;
    }

    $topforums = array();
    while (list($forum_id, $forum_name, $forum_topics, $forum_posts, $cat_title, $cat_id) = $result->FetchRow()) {
        if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            $topforum = array();
            $topforum['forum_id'] = $forum_id;
            $topforum['forum_name'] = pnVarPrepForDisplay($forum_name);
            $topforum['forum_topics'] = $forum_topics;
            $topforum['forum_posts'] = $forum_posts;
            $topforum['cat_title'] = pnVarPrepForDisplay($cat_title);
            $topforum['cat_id'] = $cat_id;
            array_push($topforums, $topforum);
        }
    }
    pnfCloseDB($result);
    $smarty->assign('topforumscount', count($topforums));
    $smarty->assign('topforums', $topforums);
    return;
}

?>