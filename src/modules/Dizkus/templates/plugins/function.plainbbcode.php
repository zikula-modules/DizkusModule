<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * bbcode plugin
 * shows all available bbcode tags
 *
 * @params $params $images boolean if true then show images instead of text links
 * @params $params $textfieldid string id of the textfield to update
 */
function smarty_function_plainbbcode($params, &$smarty)
{
     /*$out = "";
    if (isset($params['textfieldid']) && !empty($params['textfieldid'])) {
	    if (ModUtil::available('LuMicuLa')) {
	        $out = ModUtil::func('LuMicuLa', 'user', 'transform', $params);
	    }
    }

	return $out;*/
    
    
    
    $out = "";
    if (isset($params['textfieldid']) && !empty($params['textfieldid'])) {
	    if (ModUtil::available('BBCode')) {
	        $out = ModUtil::func('BBCode', 'user', 'bbcodes', $params);
	    }
    }

	return $out;
}
