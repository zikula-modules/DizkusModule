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

/**
 * readstatistics
 * reads some statistics of the forum 
 * results are assign to
 *
 * $total_categories: total number of categories
 * $total_topics    : total number of topics 
 * $total_posts     : total number of posts
 * $total_forums    : total number of forums
 */
function smarty_function_readstatistics($params, &$smarty) 
{
    extract($params); 
	unset($params);

    // get some environment
    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT SUM(forum_topics) AS total_topics, 
          SUM(forum_posts) AS total_posts, 
          COUNT(*) AS total_forums
          FROM ".$pntable['pnforum_forums'];
          
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    list ($total_topics,$total_posts,$total_forums) = $result->fields;
    pnfCloseDB($result);
    
    $sql = "SELECT COUNT(*) FROM ".$pntable['pnforum_categories']."";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    list ($total_categories) = $result->fields;
    pnfCloseDB($result);
        
    $smarty->assign('total_categories', $total_categories);
    $smarty->assign('total_topics', $total_topics);
    $smarty->assign('total_posts', $total_posts);
    $smarty->assign('total_forums', $total_forums);
    return;
}

?>