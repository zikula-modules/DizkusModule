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
 * subscribeforum_button plugin
 * adds the subscribe forum button
 *
 *@params $params['cat_id'] int category id
 *@params $params['forum_id'] int forum id
 *@params $params['return_to'] string url to return to after subscribing, necessary because
 *                                    the subscription page can be reached from several places
 */
function smarty_function_subscribeforum_button($params, &$smarty) 
{
    extract($params); 
	unset($params);

    $userid = pnUserGetVar('uid');
    if (pnUserLoggedIn()) {
        include_once('modules/pnForum/common.php');
        if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            if(!pnModAPILoad('pnForum', 'user')) {
                $smarty->trigger_error("subscribetopic_button: unable to load userapi");
                return false;
            }
            $lang = pnUserGetLang();
            if(pnModAPIFunc('pnForum', 'user', 'get_forum_subscription_status',
                            array('userid'=>$userid, 
                                  'forum_id'=>$forum_id))==false) {
                $out = "<a title=\"".pnVarPrepForDisplay(_PNFORUM_SUBSCRIBE_FORUM)."\" href=\"".pnVarPrepHTMLDisplay(pnModURL('pnForum', 'user', 'prefs', array('act'=>'subscribe_forum', 'forum'=>$forum_id, 'return_to'=>$return_to)))."\"><img src=\"modules/pnForum/pnimages/$lang/f_abo_on.gif\" alt=\"".pnVarPrepHTMLDisplay(_PNFORUM_SUBSCRIBE_FORUM)."\" /></a>";
            } else {
                $out = "<a title=\"".pnVarPrepForDisplay(_PNFORUM_UNSUBSCRIBE_FORUM)."\" href=\"".pnVarPrepHTMLDisplay(pnModURL('pnForum', 'user', 'prefs', array('act'=>'unsubscribe_forum', 'forum'=>$forum_id, 'return_to'=>$return_to)))."\"><img src=\"modules/pnForum/pnimages/$lang/f_abo_off.gif\" alt=\"".pnVarPrepHTMLDisplay(_PNFORUM_UNSUBSCRIBE_FORUM)."\" /></a>";
            }
        }
    }
    return $out;
}

?>
