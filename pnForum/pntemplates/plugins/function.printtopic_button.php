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
 * printtopic_button plugin
 * adds the print topic button
 *
 *@params $params['cat_id'] int category id
 *@params $params['forum_id'] int forum id
 *@params $params['topic_id'] int topic id
 *@params $params['image']    string the image filename (without path)
 */
function smarty_function_printtopic_button($params, &$smarty)
{
    extract($params);
    unset($params);

    include_once('modules/pnForum/common.php');
    if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        $themeinfo = pnThemeGetInfo('Printer');
        if($themeinfo['active']) {
            return '<a class="image printlink" title="' . DataUtil::formatForDisplay(_PNFORUM_PRINT_TOPIC) . '" href="' . DataUtil::formatForDisplay(pnModURL('pnForum', 'user', 'viewtopic', array('theme' => 'Printer', 'topic'=>$topic_id))) . '">' . DataUtil::formatForDisplay(_PNFORUM_PRINT_TOPIC) . '</a>';
        }
        return '<a class="image printlink" title="' . DataUtil::formatForDisplay(_PNFORUM_PRINT_TOPIC) . '" href="' . DataUtil::formatForDisplay(pnModURL('pnForum', 'user', 'print', array('topic'=>$topic_id))) . '">' . DataUtil::formatForDisplay(_PNFORUM_PRINT_TOPIC) . '</a>';
    }
    return '';
}
