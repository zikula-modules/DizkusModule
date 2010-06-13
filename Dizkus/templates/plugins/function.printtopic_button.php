<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * printtopic_button plugin
 * adds the print topic button
 *
 * @params $params['cat_id'] int category id
 * @params $params['forum_id'] int forum id
 * @params $params['topic_id'] int topic id
 */
function smarty_function_printtopic_button($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    include_once 'modules/Dizkus/common.php';
    if (allowedtoreadcategoryandforum($params['cat_id'], $params['forum_id'])) {
        $themeinfo = ThemeUtil::getInfo('Printer');
        if ($themeinfo['active']) {
            return '<a class="dzk_img printlink" title="' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '" href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewtopic', array('theme' => 'Printer', 'topic' => $params['topic_id']))) . '">' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '</a>';
        }
        return '<a class="dzk_img printlink" title="' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '" href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'printtopic', array('topic' => $params['topic_id']))) . '">' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '</a>';
    }

    return '';
}
