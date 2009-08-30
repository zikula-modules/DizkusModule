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
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($params);
    unset($params);

    Loader::includeOnce('modules/Dizkus/common.php');
    if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        $themeinfo = pnThemeGetInfo('Printer');
        if($themeinfo['active']) {
            return '<a class="dzk_img printlink" title="' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '" href="' . DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewtopic', array('theme' => 'Printer', 'topic'=>$topic_id))) . '">' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '</a>';
        }
        return '<a class="dzk_img printlink" title="' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '" href="' . DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'print', array('topic'=>$topic_id))) . '">' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '</a>';
    }
    return '';
}
