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
 * jumpbox plugin
 * creates a dropdown list with all available forums for the current user. 
 * seleting a forum issue a direct forward to the viewforum() function
 *
 */
function smarty_function_jumpbox($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(!pnModAPILoad('pnForum', 'admin')) {
        $smarty->trigger_error("loading adminapi failed", e_error);
        return;
    } 

    $out = "";
    $forums = pnModAPIFunc('pnForum', 'admin', 'readforums');
    if(count($forums)>0) {
        include_once('modules/pnForum/common.php');
        $out ='<form action="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'viewforum')) . '" method="get">
               <fieldset style="border:none;">
               <label for="pnforum_forum"><strong>' . pnVarPrepForDisplay(_PNFORUM_FORUM) . ': </strong></label>
               <select name="forum" id="pnforum_forum" onchange="location.href=this.options[this.selectedIndex].value">
	           <option value="'.pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'main')).'">' . pnVarPrepForDisplay(_PNFORUM_QUICKSELECTFORUM) . '</option>';
        foreach($forums as $forum) {
            if(allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
            	$out .= '<option value="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'viewforum', array('forum' => $forum['forum_id']))) . '">' . $forum['cat_title'] . '&nbsp;::&nbsp;' . $forum['forum_name'] . '</option>';
            } 
        }
        $out .= '</select>
        </fieldset>
                 </form>';
    }
    return $out;

}

?>
