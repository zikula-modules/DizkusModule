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

// cat_title, forum_name, forum_id, return_to
function smarty_function_subscribeforum_button($params, &$smarty) 
{
    extract($params); 
	unset($params);

    $userid = pnUserGetVar('uid');
    if (pnUserLoggedIn()) {
        if (pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_READ) || pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_READ)) 
	    {
            if(!pnModAPILoad('pnForum', 'user')) {
                $smarty->trigger_error("subscribetopic_button: unable to load userapi", e_error);
                return false;
            }
            if(pnModAPIFunc('pnForum', 'user', 'get_forum_subscription_status',
                            array('userid'=>$userid, 
                                  'forum_id'=>$forum_id))==false) {
                $out = "<a href=\"".pnModURL('pnForum', 'user', 'prefs', array('act'=>'subscribe_forum', 'forum'=>$forum_id, 'return_to'=>$return_to))."\">".pnVarPrepHTMLDisplay(_PNFORUM_SUBSCRIBE_FORUM)."</a>";
            } else {
                $out = "<a href=\"".pnModURL('pnForum', 'user', 'prefs', array('act'=>'unsubscribe_forum', 'forum'=>$forum_id, 'return_to'=>$return_to))."\">".pnVarPrepHTMLDisplay(_PNFORUM_UNSUBSCRIBE_FORUM)."</a>";
            }
        }
    }
    return $out;
}

?>