<?php
// $Id: function.forumpager.php 505 2006-03-11 14:35:55Z landseer $
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------

include_once 'modules/pnForum/common.php';

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
    // case for some Xanthia 3.0 themes in .8. It will be ignored in .764.
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

?>