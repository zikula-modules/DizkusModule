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
    if(pnModAvailable('pnUpper') && pnModIsHooked('pnUpper', 'pnForum')) {
        $out = pnModFunc('pnUpper', 'user', 'showfilelist',
                         array('objectid' => $objectid));
    }
    return $out;
}
