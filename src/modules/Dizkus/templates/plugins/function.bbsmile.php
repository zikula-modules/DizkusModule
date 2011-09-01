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
 * bbsmile plugin
 * shows all available smilies
 *
 * @params textfieldid
 */
function smarty_function_bbsmile($params, &$smarty)
{
    extract($params);
    $out = "";
    if (ModUtil::available('BBSmile'))  {
        $out = ModUtil::func(
          'BBSmile', 
          'user',
          'bbsmiles',
          array('textfieldid' => $textfieldid)
        );
    }

    return $out;
}
