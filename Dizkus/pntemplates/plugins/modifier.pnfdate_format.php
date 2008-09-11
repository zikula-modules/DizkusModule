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
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the Zikula implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   pnRender
 * @version      $Id$
 * @author       The Zikula development team
 * @link         http://www.zikula.org  The Zikula Home Page
 * @copyright    Copyright (C) 2002 by the Zikula Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

/**
 * Include the {@link shared.make_timestamp.php} plugin
 */
require_once $smarty->_get_plugin_filepath('shared','make_timestamp');

/**
 * Smarty modifier to format datestamps via strftime according to
 * locale setting in Zikula
 *
 * @author   Frank Schummertz
 * @author   Steffen Voss
 * @since    15. Jan. 2004
 * @param    string   $string         input date string
 * @param    string   format          strftime format for output, either a name that corresponds to a define
 *                                    in the main language settings or a explicit time definition as defined
 *                                    at http://php.net/manual/en/function.strftime.php
 *                                    If the define used does not exist, datebrief + a red exclamation mark will
 *                                    be shown.
 * @param    string   $default_date   default date if $string is empty
 * @param    string   $usetzoffset    use users timezone offset if set
 * @return   string   the modified output
 * @uses     smarty_make_timestamp()
 */
function smarty_modifier_pnfdate_format($string, $format='datebrief', $default_date=null, $usetzoffset=null)
{
    if(empty($format)) {
        $format = 'datebrief';
    }

    if(substr_count($format, '%') == 0) {
        // format does not contain a % hence it is not a time format string but a name for a define
        $format = '_' . strtoupper($format);
        if(defined($format)) {
            $format = constant($format);
        } else {
            // just a dumb default (datebrief + a red exclamation mark)
            $format = _DATEBRIEF . '<span style="color: red;">!</span>';
        }
    }

    $tzoffset = 0;
    if(isset($usetzoffset)) {
        $useroffset = (pnUserLoggedIn()) ? (float)pnUserGetVar('timezone_offset') : (float)pnUserGetVar('timezone_offset', 1);
        $tzoffset = ($useroffset - (float)pnConfigGetVar('timezone_offset')) * 3600;
    }

    if($string != '') {
        return strftime($format, smarty_make_timestamp($string) + $tzoffset );
    } elseif (isset($default_date) && $default_date != '') {
        return strftime($format, smarty_make_timestamp($default_date) + $tzoffset );
    } else {
        return;
    }
}
