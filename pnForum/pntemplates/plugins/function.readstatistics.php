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

// maxposts
function smarty_function_readstatistics($params, &$smarty) 
{
    extract($params); 
	unset($params);

    // get some enviroment
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $sql = "SELECT SUM(forum_topics) AS total_topics, 
          SUM(forum_posts) AS total_posts, 
          COUNT(*) AS total_forums
          FROM ".$pntable['pnforum_forums'];
          
    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) {    
      return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

    list ($total_topics,$total_posts,$total_forums) = $result->fields;
    
    $sql = "SELECT COUNT(*) FROM ".$pntable['pnforum_categories']."";
    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) {
       return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    list ($total_categories) = $result->fields;
        
    $smarty->assign('total_categories', $total_categories);
    $smarty->assign('total_topics', $total_topics);
    $smarty->assign('total_posts', $total_posts);
    $smarty->assign('total_forums', $total_forums);
    return;
}

?>