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
 * forumbox
 * creates a dropdown list with all available forums for the user
 *
 */
function smarty_function_forumbox($params, &$smarty) 
{
	extract($params); 
	unset($params);

	if(!pnModAPILoad('pnForum', 'admin')) {
		$smarty->trigger_error("loading pnForum adminapi failed");
	} 

	$out = "";
	$forums = pnModAPIFunc('pnForum', 'admin', 'readforums');

	if(count($forums)>0) {
        include_once('modules/pnForum/common.php');
		$out ='<select name="pnForum_forum[]" id="pnForum_forum[]" size="1">';
		$out.='<option value="" selected>'. _SRCHALLTOPICS .'</option>';
		foreach($forums as $forum) {
			if(allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
				$out .= '<option value="'.$forum['forum_id'].'">'.pnVarPrepForDisplay($forum['cat_title']).'::'.pnVarPrepForDisplay($forum['forum_name']).'</option>';
			}
    }
	$out .= '</select>';
	return $out;
}
}
?>