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
 * dizkus quote plugin
 *
 * @param $params['uid']     int user id
 * @param $params['text']    string text to quote
 *
 *
 */
function smarty_function_dzkquote($params, &$smarty)
{
    if (empty($params['text'])) {
        return '';
    }



    if (!empty($params['uid'])) {
        $user = '='.UserUtil::getVar('uname', $params['uid']);
    } else {
        $user = '';
    }

    // ToDo: Remove bbcode
    return '[quote'.$user.']'.$params['text'].'[/quote]';
}
