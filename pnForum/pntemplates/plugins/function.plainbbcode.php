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

/**
 * bbcode plugin
 * shows all available bbcode tags
 *
 *@params $params $images boolean if true then show images instead of text links
 *@params $params $textfieldis string id of the textfield to update
 */
function smarty_function_plainbbcode($params, &$smarty)
{
    extract($params);
	unset($params);

    $out = "";
    $args = array();
    if(!empty($textfieldid)) {
        $args['textfieldid'] = $textfieldid;
    }
    $args['images'] = $images;

	if(pnModAvailable('pn_bbcode') && pnModIsHooked('pn_bbcode', 'pnForum')) {
	    $out = pnModFunc('pn_bbcode', 'user', 'bbcodes', $args);
	}
	return $out;
}
