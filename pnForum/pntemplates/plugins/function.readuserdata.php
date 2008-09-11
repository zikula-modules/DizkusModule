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

// uid (optional), assign
function smarty_function_readuserdata($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(!pnModAPILoad('Dizkus', 'user')) {
        $smarty->trigger_error("loading Dizkus userapi failed");
        return;
    } 

    $uid = (empty($uid)) ? pnUserGetVar('uid') : $uid;
    $assign = (empty($assign)) ? "userdata" : $assign;
    
    $smarty->assign($assign, pnModAPIFunc('Dizkus', 'user', 'get_userdata_from_id',
                                         array('userid'   => $uid)));
    return;
}
