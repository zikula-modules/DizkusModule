<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id: pnajax.php 815 2007-09-22 13:12:50Z landseer $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

Loader::includeOnce('modules/pnForum/common.php');

/**
 * pnforumcolorcss plugin
 * creates css styles for using colors
 * use this plugin in your themes head section if your theme generates and supports
 * color definitions. If not, use the class defintions in the main style.css
 *
 */
function smarty_function_pnforumcolorcss($params, &$smarty)
{
    // The second parameter in pnThemeGetVar() defines the default value
    // which will be used when nothing else is defined (which might be the
    // case for some Xanthia 3.0 themes in Zikula 1.0. It will be ignored in .764.
    // If you do not like those defaults, remove the pnforumcolorcss plugin
    // from your theme and define the colors in your pnForums style.css
    // See modules/pnForum/pnstyle/style.css for more information about this
    //
    $css = "\n<style type=\"text/css\">\n"
          .".pnf_bgcolor1 { background-color: " . pnThemeGetVar('bgcolor1', '#FFFFFF') . "; }\n"
          .".pnf_bgcolor2 { background-color: " . pnThemeGetVar('bgcolor2', '#AFBFC8') . "; }\n"
          .".pnf_bgcolor3 { background-color: " . pnThemeGetVar('bgcolor3', '#CEDEE7') . "; }\n"
          .".pnf_bgcolor4 { background-color: " . pnThemeGetVar('bgcolor4', '#EDF3F7') . "; }\n"
          .".pnf_bgcolor5 { background-color: " . pnThemeGetVar('bgcolor5', '#EDF3F7') . "; }\n"
          .".pnf_bordercolor1 { border-color: " . pnThemeGetVar('bgcolor1', '#FFFFFF') . "; }\n"
          .".pnf_bordercolor2 { border-color: " . pnThemeGetVar('bgcolor2', '#AFBFC8') . "; }\n"
          .".pnf_bordercolor3 { border-color: " . pnThemeGetVar('bgcolor3', '#CEDEE7') . "; }\n"
          .".pnf_bordercolor4 { border-color: " . pnThemeGetVar('bgcolor4', '#EDF3F7') . "; }\n"
          .".pnf_bordercolor5 { border-color: " . pnThemeGetVar('bgcolor5', '#EDF3F7') . "; }\n"
          ."</style>\n\n";
    return $css;
}
