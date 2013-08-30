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
 * Zikula_View plugin
 * This file is a plugin for Zikula_View, the Zikula implementation of Smarty
 */

/**
 * Smarty modifier to prepare text for display
 *
 * Example
 *   {$text|dzkVarPrepHTMLDisplay}
 *
 * @param        string   $text     the string to transform
 * @return       string   the modified output
 */
function smarty_modifier_dzkVarPrepHTMLDisplay($text = null)
{
    if (!isset($text)) {
        return '';
    }

    return ModUtil::apiFunc('Dizkus', 'user', 'dzkVarPrepHTMLDisplay', $text);
}
