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
 * listforummods plugin
 * lists a forum mods
 *
 *@param $params['moderators'] array with key=userid, value=username of moderators
 *
 */
function smarty_function_listforummods($params, &$smarty) 
{
    extract($params); 
	unset($params);

    $out = "";
    foreach($moderators as $mod_id=>$mod_name) {
        if($count > 0) {
	        $out .= ", ";
	    }
	    $out .= '<a href="user.php?op=userinfo&amp;uname='.pnVarPrepForDisplay($mod_name).'">'.pnVarPrepForDisplay($mod_name).'</a>';
	    $count++;
    }
    return $out;
}

?>