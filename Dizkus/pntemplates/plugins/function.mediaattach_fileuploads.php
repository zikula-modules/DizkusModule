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
 * mediaattach_fileuploads plugin
 * show all files uploaded for give objectid
 *
 * @$params['objectid'] int the topic id
 *
 */
function smarty_function_mediaattach_fileuploads($params, &$smarty)
{
    if (!isset($params['objectid'])) {
        $smarty->trigger_error("smarty_function_mediaattach_fileuploads: missing parameter 'objectid'");
        return false;
    }

    $out = '';
    if(pnModAvailable('MediaAttach') && pnModIsHooked('MediaAttach', 'Dizkus')) {
        $out = pnModFunc('MediaAttach', 'user', 'showfilelist',
                         array('objectid' => $params['objectid']));
    }
    return $out;
}
