<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

// uid (optional), assign
function smarty_function_readuserdata($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(!pnModAPILoad('pnForum', 'user')) {
        $smarty->trigger_error("loading pnForum userapi failed");
        return;
    } 

    $uid = (empty($uid)) ? pnUserGetVar('uid') : $uid;
    $assign = (empty($assign)) ? "userdata" : $assign;
    
    $smarty->assign($asign, pnModAPIFunc('pnForum', 'user', 'get_userdata_from_id',
                                         array('userid'   => $uid)));
    return;
}
