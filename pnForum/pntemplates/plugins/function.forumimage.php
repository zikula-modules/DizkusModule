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


// type, id
function smarty_function_forumimage($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(!pnModAPILoad('pnForum', 'user')) {
        $smarty->trigger_error("loading upnForum userapi failed");
        return;
    } 

    $assign = (!empty($assign)) ? $assign : 'forumimage';
    
    if(empty($name)) {
        $smarty->trigger_error("folderimage: missing parameter 'name'");
        return false;
    }

    $img = pnModGetVar('pnForum', $name);
    if(empty($name)) {
        $smarty->trigger_error("folderimage: invalid value for parameter 'name'");
        return false;
    }
    
    $img_attr = getimagesize($img);
    
    $smarty->assign($assign, array('name' => $img,
                                   'width' => $img_attr[0],
                                   'height' => $img_attr[1]));
                                   
}

?>