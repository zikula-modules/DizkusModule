<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
/**
 * Zikula_View plugin
 * This file is a plugin for Zikula_View, the Zikula implementation of Smarty
 */

/**
 * Smarty modifier to transform bracket-tags in the text (e.g. [quote])
 *
 * Example
 *
 *   {$message|transformtags}
 *
 *
 * @author       The Dizkus team
 * @since        2013
 * @param        string   $message     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_transformtags($message = null)
{
    return ModUtil::apiFunc('ZikulaDizkusModule', 'ParseTags', 'transform', array('message' => $message));
}
