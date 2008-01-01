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
 * pnforumallowedhtml plugin
 * lists all allowed html tags
 *
 */
function smarty_function_pnforumallowedhtml($params, &$smarty) 
{
    extract($params); 
	unset($params);
    $out = "<br />".DataUtil::formatForDisplay(_ALLOWEDHTML)."<br />";
    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    while (list($key, $access, ) = each($AllowableHTML)) {
    	if ($access > 0) $out .= " &lt;".$key."&gt;";
    }
    return $out;
}
