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
 * mediaattach_fileuploads plugin
 * show all files uploaded for give objectid
 *
 * @$params['objectid'] int the topic id
 *
 */
function smarty_function_mediaattach_fileuploads($params, &$smarty)
{
    if (!isset($params['objectid'])) {
        $smarty->trigger_error("Error! In 'smarty_function_mediaattach_fileuploads', the 'objectid' parameter is missing.");
        return false;
    }

    $out = '';
    if (ModUtil::available('MediaAttach') && ModUtil::isHooked('MediaAttach', 'Dizkus')) {
        $out = ModUtil::func('MediaAttach', 'user', 'showfilelist',
                         array('objectid' => $params['objectid']));
    }

    return $out;
}
