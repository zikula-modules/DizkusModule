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
 * topicpager plugin
 * creates a topic pager
 *
 *@param $params['total'] int total number of posts in this topic
 *@param $params['topic_id'] int topic id
 *@param $params['start'] int start value
 *@param $params['nonextprev'] string if set then do not show nextpage and prevpage text
 *@param $params['separator'] string  text to show between the pages, default |
 *
 */
function smarty_function_topicpager($params, &$smarty)
{
    extract($params);
	unset($params);

    if(empty($total) || empty($topic_id)) {
		$smarty->trigger_error(pnVarPrepForDisplay(_MODARGSERROR));
	}

	if(!pnModAPILoad('pnForum', 'admin')) {
		$smarty->trigger_error("loading pnForum adminapi failed");
	}

    if(empty($separator)) {
		$separator = "|";
	}
    $posts_per_page  = pnModGetVar('pnForum', 'posts_per_page');
    $pager = "";
    if($total > $posts_per_page) {
        $start = (!empty($start)) ? (int)$start : 0;
        $times = 1;
        $pager = "<div>".pnVarPrepForDisplay(_PNFORUM_GOTOPAGE)."&nbsp;:&nbsp;";
        $last_page = $start - $posts_per_page;
        if(($start > 0) && empty($nonextprev) ) {
            $pager .= "<a href=\"" . pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id,'start'=>$last_page))) . "\" title=\"" . pnVarPrepForDisplay(_PNFORUM_PREVPAGE) . "\">".pnVarPrepForDisplay(_PNFORUM_PREVPAGE).'</a> ';
        }
        for($x = 0; $x < $total; $x += $posts_per_page) {
            if($times != 1) {
                $pager .= " $separator ";
            }
            if($start && ($start == $x)) {
                $pager .= $times;
            } else if($start == 0 && $x == 0) {
                $pager .= "1";
            } else {
                $pager .= "<a href=\"" . pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id,'start'=>$x))) . "\" title=\"" . pnVarPrepForDisplay(_PNFORUM_GOTOPAGE) . " $times\">$times</a>";
            }
            $times++;
        }

        if( (($start + $posts_per_page) < $total) && empty($nonextprev) ) {
            $next_page = $start + $posts_per_page;
            $pager .= " <a href=\"" . pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id,'start'=>$next_page))) . "\" title=\"" . pnVarPrepForDisplay(_PNFORUM_NEXTPAGE) . "\">".pnVarPrepForDisplay(_PNFORUM_NEXTPAGE).'</a>';
        }
        $pager .= " </div>\n";
    }
    return $pager;
}

?>