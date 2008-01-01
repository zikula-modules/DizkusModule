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

// id, type
/**
 * adminlink plugin
 * adds a link to the configuration of a category or forum
 *
 *@params $params['type'] string, either 'category' or 'forum'
 *@params $params['id']   int     category or forum id, depending of $type
 */ 
function smarty_function_adminlink($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if (pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        if(empty($id) || empty($type)) {
            $smarty->trigger_error("adminlink: missing parameter(s)");
            return;
        }
        
        if($type=="category") {
            return "<a href=\"".DataUtil::formatForDisplay(pnModURL('pnForum', 'admin', 'category', array('cat_id'=>(int)$id)))."\">[".DataUtil::formatForDisplay(_PNFORUM_ADMINCATEDIT)."]</a>";
        } elseif ($type=="forum") {
            return "<a href=\"".DataUtil::formatForDisplay(pnModURL('pnForum', 'admin', 'forum', array('forum_id'=>(int)$id)))."\">[".DataUtil::formatForDisplay(_PNFORUM_ADMINFORUMEDIT)."]</a>";
        }
    }
    return;
}
