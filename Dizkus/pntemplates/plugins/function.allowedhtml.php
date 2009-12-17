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

/**
 * allowedhtml plugin
 * lists all allowed html tags
 *
 */
function smarty_function_allowedhtml($params, &$smarty) 
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $out = "<br />".__('Allowed HTML:', $dom)."<br />";
    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    while (list($key, $access, ) = each($AllowableHTML)) {
    	if ($access > 0) $out .= " &lt;".$key."&gt;";
    }

    return $out;
}
