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
    $dizkusModuleName = "ZikulaDizkusModule";
    $dom = ZLanguage::getModuleDomain($dizkusModuleName);
    if (ModUtil::apiFunc($dizkusModuleName, 'Permission', 'canRead', $params['forum'])) {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName('Printer'));
        if ($themeinfo['state'] == ThemeUtil::STATE_ACTIVE) {
            return '<a class="icon-print tooltips" title="' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '" href="' . DataUtil::formatForDisplay(ModUtil::url($dizkusModuleName, 'user', 'viewtopic', array('theme' => 'Printer', 'topic' => $params['topic_id']))) . '"></a>';
        }
    }

    return '';
}
