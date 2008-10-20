<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
                                                                                                                                                         
/**
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the Zikula implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   pnRender
 * @version      $Id$
 * @author       The Zikula development team
 * @link         http://www.zikula.org  The Zikula Home Page
 * @copyright    Copyright (C) 2002 by the Zikula Development Team
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
 *   - assign:       If set, the results are assigned to the corresponding variable
 *   - checkgroups:  If set, checks if the users found are in the moderator groups (perforance issue!) default is no group check
 *
 * Example
 *   <!--[dizkusonline assign="islogged"]-->
 *
 *
 * @author       Frank Chestnut
 * @since        10/10/2005
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       array
 */

Loader::includeOnce('modules/Dizkus/common.php');

function smarty_function_dizkusonline($params, &$smarty)
{
    extract($params);
    unset($params);

    $checkgroups = (isset($checkgroups)) ? true : false;
    
    list($dbconn, $pntable) = dzkOpenDB();

    $sessioninfocolumn = &$pntable['session_info_column'];
    $sessioninfotable = $pntable['session_info'];

    $activetime = DateUtil::getDateTime(time() - (pnConfigGetVar('secinactivemins') * 60));

    // set some defaults
    $numguests = 0;
    $numusers  = 0;
    $unames    = array();

    $moderators = pnModAPIFunc('Dizkus', 'user', 'get_moderators', array());

    if (pnConfigGetVar('anonymoussessions')) {
        $anonwhere = "AND      $pntable[session_info].pn_uid >= '0' ";
    } else {
        $anonwhere = "AND      $pntable[session_info].pn_uid > '0'";
    }
    $sql = "SELECT   $pntable[session_info].pn_uid, $pntable[users].pn_uname
            FROM     $pntable[session_info], $pntable[users]
            WHERE    $pntable[session_info].pn_lastused > '$activetime'
            $anonwhere
            AND      IF($pntable[session_info].pn_uid='0','1',$pntable[session_info].pn_uid) = $pntable[users].pn_uid
            GROUP BY $pntable[session_info].pn_ipaddr, $pntable[session_info].pn_uid";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $total = $result->RecordCount();

    for(; !$result->EOF; $result->MoveNext()) {
        list($uid, $uname) = $result->fields;

        if ($uid != 0) {
            $unames[$uid] = array('uid'   => $uid,
                                  'uname' => $uname,
                                  'admin' => ($moderators[$uid] == $uname) || allowedtoadmincategoryandforum($category_id, $forum_id, $uid));
            $numusers++;
        } else {
            $numguests++;
        }
    }

    dzkCloseDB($result);

    if($checkgroups == true) {
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
        
            $users[$user['uid']] = array('uid'    => $user['uid'],
                                         'uname'  => $user['uname'],
                                         'admin'  => $user['admin']);
        
        }
        $unames = $users;
    }
    usort($unames, 'cmp_userorder');

    $dizkusonline['numguests'] = $numguests;

    $dizkusonline['numusers']  = $numusers;
    $dizkusonline['total']     = $total;
    $dizkusonline['unames']    = $unames;

    if (isset($assign)) {
        $smarty->assign($assign, $dizkusonline);
    } else {
        $smarty->assign($dizkusonline);
    }
    return;

}
