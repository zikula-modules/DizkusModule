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
 * subscribetopicm_button plugin
 * adds the subscribe topic button
 *
 *@params $params['cat_id'] int category id
 *@params $params['forum_id'] int forum id
 *@params $params['topic_id'] int topic id
 */
function smarty_function_subscribetopic_button($params, &$smarty) 
{
    extract($params); 
	unset($params);

    $userid = pnUserGetVar('uid');
    if (pnUserLoggedIn()) {
        if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            if(!pnModAPILoad('pnForum', 'user')) {
                $smarty->trigger_error("subscribetopic_button: unable to load userapi", e_error);
                return false;
            }
            $lang = pnUserGetLang();
            if(pnModAPIFunc('pnForum', 'user', 'get_topic_subscription_status',
                            array('userid'=>$userid, 
                                  'topic_id'=>$topic_id))==false) {
                $out = "<a title=\"".pnVarPrepHTMLDisplay(_PNFORUM_SUBSCRIBE_TOPIC)."\" href=\"".pnModURL('pnForum', 'user', 'prefs', array('act'=>'subscribe_topic', 'topic'=>$topic_id))."\"><img src=\"modules/pnForum/pnimages/$lang/t_abo_on.gif\" alt=\"".pnVarPrepHTMLDisplay(_PNFORUM_SUBSCRIBE_TOPIC)."\" /></a>";
            } else {
                $out = "<a title=\"".pnVarPrepHTMLDisplay(_PNFORUM_UNSUBSCRIBE_TOPIC)."\" href=\"".pnModURL('pnForum', 'user', 'prefs', array('act'=>'unsubscribe_topic', 'topic'=>$topic_id))."\"><img src=\"modules/pnForum/pnimages/$lang/t_abo_off.gif\" alt=\"".pnVarPrepHTMLDisplay(_PNFORUM_UNSUBSCRIBE_TOPIC)."\" /></a>";
            }
        }
    }
    return $out;
}

?>
