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
 * readtopposters
 * reads the top $maxposters users depending on their post count and assigns them in the
 * variable topposters and the number of them in toppostercount
 *
 *@params maxposters (int) number of users to read, default = 3
 *
 */
function smarty_function_readtopposters($params, &$smarty) 
{
    extract($params); 
	unset($params);

    // get some enviroment
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $postermax = (!empty($maxposters)) ? $maxposters : 3;

    $sql = "SELECT user_id,user_posts
          FROM ".$pntable['pnforum_users']." 
          WHERE user_id <> 1
          AND user_posts > 0
          ORDER BY user_posts DESC";

    $result = $dbconn->SelectLimit($sql, $postermax);
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }
    $result_postermax = $result->PO_RecordCount();
    if ($result_postermax <= $postermax) {
      $postermax = $result_postermax;
    }

    $topposters = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            list($user_id, $user_posts) = $result->fields;
            $topposter = array();
            $topposter['user_id'] = $user_id;
            $topposter['user_name'] = pnUserGetVar('uname', $user_id);
            $topposter['user_posts'] = $user_posts;
            array_push($topposters, $topposter);
        }
    }

    $smarty->assign('toppostercount', count($topposters));
    $smarty->assign('topposters', $topposters);
    return;
}

?>