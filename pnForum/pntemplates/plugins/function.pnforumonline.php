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
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the PostNuke implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   pnRender
 * @version      $Id$
 * @author       The PostNuke development team
 * @link         http://www.postnuke.com  The PostNuke Home Page
 * @copyright    Copyright (C) 2002 by the PostNuke Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * Smarty function to read the users who are online
 *
 * This function returns an array (ig assign is used) or four variables
 * numguests : number of guests online
 * numusers: number of users online
 * total: numguests + numusers
 * unames: array of 'uid', (int, userid), 'uname' (string, username) and 'admin' (boolean, true if users is a moderator)
 *
 * Available parameters:
 *   - assign:  If set, the results are assigned to the corresponding variable
 *
 * Example
 *   <!--[pnforumonline assign="islogged"]-->
 *
 *
 * @author       Frank Chestnut
 * @since        10/10/2005
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       array
 */

include_once 'modules/pnForum/common.php';

function smarty_function_pnforumonline($params, &$smarty)
{
    extract($params);
    unset($params);

    list($dbconn, $pntable) = pnfOpenDB();

    $sessioninfocolumn = &$pntable['session_info_column'];
    $sessioninfotable = $pntable['session_info'];

    $activetime = time() - (pnConfigGetVar('secinactivemins') * 60);

    // set some defaults
    $numguests = 0;
    $numusers  = 0;
    $unames    = array();

    $moderators = pnModAPIFunc('pnForum', 'user', 'get_moderators', array());

    if (pnConfigGetVar('anonymoussessions')) {
        $anonwhere = "AND      $sessioninfocolumn[uid] >= '0' ";
    } else {
        $anonwhere = "AND      $sessioninfocolumn[uid] > '0'";
    }
    $sql = "SELECT DISTINCT $sessioninfocolumn[uid], $pntable[users].pn_uname
            FROM     $sessioninfotable, $pntable[users]
            WHERE    $sessioninfocolumn[lastused] > $activetime
            $anonwhere
            AND      IF($sessioninfocolumn[uid]='0','1',$sessioninfocolumn[uid]) = $pntable[users].pn_uid
            GROUP BY $sessioninfocolumn[ipaddr], $sessioninfocolumn[uid]";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $total = $result->RecordCount();

    for(; !$result->EOF; $result->MoveNext()) {
        list($uid, $uname) = $result->fields;

        if ($uid != 0) {
            $unames[] = array('uid'   => $uid,
                              'uname' => $uname,
                              'admin' => ($moderators[$uid] == $uname));
            $numusers++;
        } else {
            $numguests++;
        }
    }

    pnfCloseDB($result);

    foreach($unames as $user) {
        if ($user['admin'] == false) {
            $groups = pnModAPIFunc('Groups', 'user', 'getusergroups', array('uid' => $user['uid']));

            foreach($groups as $group) {
                if (isset($moderators[$group['gid']+1000000])) {
                    $user['admin'] = true;
                } else {
                    $user['admin'] = false;
                }
            }
        }

        $users[] = array('uid'    => $user['uid'],
                         'uname'  => $user['uname'],
                         'admin'  => $user['admin']);

    }

    $pnforumonline['numguests'] = $numguests;

    $pnforumonline['numusers']  = $numusers;
    $pnforumonline['total']     = $total;
    $pnforumonline['unames']    = $users;

    if (isset($assign)) {
        $smarty->assign($assign, $pnforumonline);
    } else {
        $smarty->assign($pnforumonline);
    }
    return;

}

?>