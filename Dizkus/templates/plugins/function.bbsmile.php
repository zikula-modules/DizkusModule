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
 * bbsmile plugin
 * shows all available smilies
 *
 * @params textfieldid
 */
function smarty_function_bbsmile($params, &$smarty)
{
    $out = "";
	  if (ModUtil::available('BBSmile') && ModUtil::isHooked('BBSmile', 'Dizkus')) {
	      $out = ModUtil::func('BBSmile', 'user', 'bbsmiles',
	                       array('textfieldid' => $params['textfieldid']));
	  }

	return $out;
}
