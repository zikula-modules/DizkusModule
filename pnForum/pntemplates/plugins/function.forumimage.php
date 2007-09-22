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


// type, id
function smarty_function_forumimage($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(!pnModAPILoad('pnForum', 'user')) {
        $smarty->trigger_error("loading upnForum userapi failed");
        return;
    } 

    $assign = (!empty($assign)) ? $assign : 'forumimage';
    
    if(empty($name)) {
        $smarty->trigger_error("folderimage: missing parameter 'name'");
        return false;
    }

    $img = pnModGetVar('pnForum', $name);
    if(empty($name)) {
        $smarty->trigger_error("folderimage: invalid value for parameter 'name'");
        return false;
    }
    
    $img_attr = getimagesize($img);
    
    $smarty->assign($assign, array('name' => $img,
                                   'width' => $img_attr[0],
                                   'height' => $img_attr[1]));
                                   
}
