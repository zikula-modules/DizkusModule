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

include_once('modules/pnForum/common.php');

// maxforums
function smarty_function_readtopforums($params, &$smarty) 
{
    extract($params); 
	unset($params);

    // get some enviroment
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

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
    
    $result = $dbconn->SelectLimit($sql, $forummax);
    if($dbconn->ErrorNo() != 0) {    
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    $result_forummax = $result->PO_RecordCount();
    if ($result_forummax <= $forummax) {
        $forummax = $result_forummax;
    }

    $topforums = array();
    while (list($forum_id, $forum_name, $forum_topics, $forum_posts, $cat_title, $cat_id) = $result->FetchRow()) {
        if (pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_READ) && 
            pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_READ))   {
            $topforum = array();
            $topforum['forum_id'] = $forum_id;
            $topforum['forum_name'] = pnVarPrepForDisplay($forum_name);
            $topforum['forum_topics'] = $forum_topics;
            $topforum['forum_posts'] = $forum_posts;
            $topforum['cat_title'] = $cat_title;
            $topforum['cat_id'] = $cat_id;
            array_push($topforums, $topforum);
        }
    }
    $smarty->assign('topforumscount', count($topforums));
    $smarty->assign('topforums', $topforums);
    return;
}

?>