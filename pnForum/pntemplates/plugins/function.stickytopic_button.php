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

// cat_id, forum_id, status, topic_id
function smarty_function_stickytopic_button($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
	    if($status != 1)
   	    {
            $image = pnModGetVar('pnForum', 'stickytopic_image');
	        $img_attr = getimagesize($image);
  	        $out = "<a title=\"".pnVarPrepForDisplay(_PNFORUM_STICKYTOPIC)."\" href=\"".pnModURL('pnForum', 'user', 'topicadmin', array('mode'=>'sticky', 'topic'=>$topic_id))."\"><img src=\"$image\" alt=\"".pnVarPrepForDisplay(_PNFORUM_STICKYTOPIC)."\" ".$img_attr[3]." >".pnVarPrepForDisplay(_PNFORUM_STICKYTOPIC)."</a>&nbsp;&nbsp;&nbsp;";
        }else{
            $image = pnModGetVar('pnForum', 'unstickytopic_image');
	        $img_attr = getimagesize($image);
  	        $out = "<a title=\"".pnVarPrepForDisplay(_PNFORUM_UNSTICKYTOPIC)."\" href=\"".pnModURL('pnForum', 'user', 'topicadmin', array('mode'=>'unsticky', 'topic'=>$topic_id))."\"><img src=\"$image\" alt=\"".pnVarPrepForDisplay(_PNFORUM_UNSTICKYTOPIC)."\" ".$img_attr[3]." >".pnVarPrepForDisplay(_PNFORUM_UNSTICKYTOPIC)."</a>&nbsp;&nbsp;&nbsp;";
        }
    }
    return $out;
}

?>