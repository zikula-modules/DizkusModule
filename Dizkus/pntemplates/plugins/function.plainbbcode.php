<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
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
    $out = "";
    if (isset($params['textfieldid']) && !empty($params['textfieldid'])) {
	    if (pnModAvailable('bbcode') && pnModIsHooked('bbcode', 'Dizkus')) {
	        $out = pnModFunc('bbcode', 'user', 'bbcodes', $params);
	    }
    }

	return $out;
}
