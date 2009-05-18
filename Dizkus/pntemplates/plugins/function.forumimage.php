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


// type, id
function smarty_function_forumimage($params, &$smarty) 
{
    extract($params); 
	unset($params);

    $assign = (!empty($params['assign'])) ? $params['assign'] : 'forumimage';
    
    if (empty($params['name'])) {
        $smarty->trigger_error("folderimage: missing parameter 'name'");
        return false;
    }

    $img = pnModGetVar('Dizkus', $params['name'], null);
    if (is_null($img)) {
        $smarty->trigger_error("folderimage: invalid value for parameter 'name'");
        return false;
    }
    
    $img_attr = function_exists('getimagesize') ? @getimagesize($img) : array(null, null);
    
    $smarty->assign($assign, array('name'   => $img,
                                   'width'  => $img_attr[0],
                                   'height' => $img_attr[1]));
                                   
}
