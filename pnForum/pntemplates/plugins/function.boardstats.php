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


// type, id, assign (optional)
/**
 * boardstats plugin
 * reads some statistics by calling the pnForum_userapi_boardstats() function
 *
 *@params $params['type']   string see below
 *@params $params['id']     int    id, depending on $type
 *@params $params['assign'] string (optional) assign the result instead of returning it
 *
 * Possible values of $type and $id and what they deliver
 * ------------------------------------------------------
 * 'all' (id not important): total number of postings in all categories and forums
 * 'topic' (id = topic id) : total number of posts in the given topic
 * 'forumposts' (id = forum id): total number of postings in the given forum
 * 'forumtopics' (id= forum id): total number of topics in the given forum
 * 'category' (id not important): total number of categories
 */
function smarty_function_boardstats($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(!pnModAPILoad('pnForum', 'user')) {
        $smarty->trigger_error("loading userapi failed", e_error);
        return;
    } 

    $type = (!empty($type)) ? $type : "all";
    $id   = (!empty($id)) ? $id : "0";
    
    $count = pnModAPIFunc('pnForum', 'user', 'boardstats',
                          array('id'   => $id,
                                'type' => $type));
    if(!empty($assign)) {
        $smarty->assign($assign, $count);
        return;
    }
    return $count;
}

?>