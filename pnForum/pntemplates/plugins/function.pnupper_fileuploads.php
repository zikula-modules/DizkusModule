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
 * pnupper_fileuploads plugin
 * show all files uploaded for give objectid
 *
 * @$params['objectid'] int the topic id
 *
 */
function smarty_function_pnupper_fileuploads($params, &$smarty)
{
    extract($params);
	unset($params);

    $out = '';
    if(pnModAvailable('pnUpper') && pnModIsHooked('pnUpper', 'Dizkus')) {
        $out = pnModFunc('pnUpper', 'user', 'showfilelist',
                         array('objectid' => $objectid));
    }
    return $out;
}
