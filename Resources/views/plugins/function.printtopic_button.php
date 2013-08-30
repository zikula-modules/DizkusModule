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
 * printtopic_button plugin
 * adds the print topic button
 * requires the Printer theme
 *
 * @params $params['forum_id'] int forum id
 * @params $params['topic_id'] int topic id
 */
function smarty_function_printtopic_button($params, Zikula_View $view)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');
    if (ModUtil::apiFunc('Dizkus', 'Permission', 'canRead', $params['forum'])) {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName('Printer'));
        if ($themeinfo['state'] == ThemeUtil::STATE_ACTIVE) {
            return '<a class="dzk_arrow printlink tooltips" title="' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '" href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewtopic', array('theme' => 'Printer', 'topic' => $params['topic_id']))) . '">' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '</a>';
        }
    }

    return '';
}
