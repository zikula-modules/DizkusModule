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
 * printtopic_button plugin
 * adds the print topic button
 *
 *@params $params['cat_id'] int category id
 *@params $params['forum_id'] int forum id
 *@params $params['topic_id'] int topic id
 */
function smarty_function_printtopic_button($params, &$smarty)
{
    extract($params);
	unset($params);

    include_once('modules/pnForum/common.php');
    $out = "";
    if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        $lang = pnUserGetLang();
        $themeinfo = pnThemeInfo('Printer');
        if($themeinfo['active']) {
		    $out = "<a title=\"" . pnVarPrepHTMLDisplay(_PNFORUM_PRINT_TOPIC) . "\" href=\"". pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id))) . "&theme=Printer\"><img src=\"modules/pnForum/pnimages/$lang/printtopic.gif\" alt=\"" . pnVarPrepHTMLDisplay(_PNFORUM_PRINT_TOPIC) ."\" /></a>";
        } else {
		    $out = "<a title=\"" . pnVarPrepHTMLDisplay(_PNFORUM_PRINT_TOPIC) . "\" href=\"". pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'print', array('topic'=>$topic_id))) . "\"><img src=\"modules/pnForum/pnimages/$lang/printtopic.gif\" alt=\"" . pnVarPrepHTMLDisplay(_PNFORUM_PRINT_TOPIC) ."\" /></a>";
	    }
	}
    return $out;
}

?>
