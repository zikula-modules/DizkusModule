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
 * bbsmile plugin
 * shows all available smilies
 *
 *@params textfieldid
 */
function smarty_function_bbsmile($params, &$smarty)
{
    extract($params);
	unset($params);

    $out = "";
	if(pnModAvailable('pn_bbsmile') &&pnModIsHooked('pn_bbsmile', 'pnForum') && pnModLoad('pn_bbsmile', 'user') ) {
	    $out = pnModFunc('pn_bbsmile', 'user', 'bbsmiles',
	                     array('textfieldid' => $textfieldid));
	}
	return $out;
}
