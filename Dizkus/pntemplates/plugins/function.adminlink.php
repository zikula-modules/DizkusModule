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
    if (SecurityUtil::checkPermission(0, 'Dizkus::', "::", ACCESS_ADMIN)) { 
        if(empty($params['id']) || empty($params['type'])) {
            $smarty->trigger_error("adminlink: missing parameter(s)");
            return;
        }
        
        if($params['type'] == 'category') {
            return '<a href="' . DataUtil::formatForDisplay(pnModURL('Dizkus', 'admin', 'category', array('cat_id'=>(int)$params['id']))) . '">[' . DataUtil::formatForDisplay(_DZK_ADMINCATEDIT) . ']</a>';
        } elseif ($type=='forum') {
            return '<a href="' . DataUtil::formatForDisplay(pnModURL('Dizkus', 'admin', 'forum', array('forum_id'=>(int)$params['id']))) . '">['.DataUtil::formatForDisplay(_DZK_ADMINFORUMEDIT) . ']</a>';
        }
    }
    return;
}
