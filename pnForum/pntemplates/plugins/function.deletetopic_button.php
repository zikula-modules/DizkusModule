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
 * deletetopic_button
 * adds a delete topic button
 *
 *@param $params['cat_id]   int category id
 *@param $params['forum_id] int forum id
 */
function smarty_function_deletetopic_button($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        $image = pnModGetVar('pnForum', 'deltopic_image');
        $img_attr = getimagesize($image);
        $out = "<a title=\"".pnVarPrepForDisplay(_PNFORUM_DELETETOPIC)."\" href=\"".pnModURL('pnForum', 'user', 'topicadmin', array('mode'=>'del', 'topic'=>$topic_id))."\"><img src=\"$image\" alt=\"".pnVarPrepForDisplay(_PNFORUM_DELETETOPIC)."\" ".$img_attr[3]." >".pnVarPrepForDisplay(_PNFORUM_DELETETOPIC)."</a>&nbsp;&nbsp;&nbsp;";
    }
    return $out;
}

?>