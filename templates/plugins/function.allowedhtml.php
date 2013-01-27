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
 * allowedhtml plugin
 * lists all allowed html tags
 *
 */
function smarty_function_allowedhtml($params, &$smarty) 
{
    $out = "<br />".__('Allowed HTML:')."<br />";
    $AllowableHTML = System::getVar('AllowableHTML');
    while (list($key, $access, ) = each($AllowableHTML)) {
    	if ($access > 0) $out .= " &lt;".$key."&gt;";
    }

    return $out;
}
