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

/**
 * pnforumcolorcss plugin
 * creates css styles for using colors
 * use this plugin in your themes head section!
 *
 */
function smarty_function_pnforumcolorcss($params, &$smarty)
{
    $css = "\n<style type=\"text/css\">\n"
          .".pnf_bgcolor1 {\n"
          ."    background-color: " . pnThemeGetVar('bgcolor1') . "\n"
          ."}\n\n"
          .".pnf_bgcolor2 {\n"
          ."    background-color: " . pnThemeGetVar('bgcolor2') . "\n"
          ."}\n\n"
          .".pnf_bgcolor3 {\n"
          ."    background-color: " . pnThemeGetVar('bgcolor3') . "\n"
          ."}\n\n"
          .".pnf_bgcolor4 {\n"
          ."    background-color: " . pnThemeGetVar('bgcolor4') . "\n"
          ."}\n\n"
          .".pnf_bgcolor5 {\n"
          ."    background-color: " . pnThemeGetVar('bgcolor5') . "\n"
          ."}\n\n"
          .".pnf_bordercolor1 {\n"
          ."    border-color: " . pnThemeGetVar('bgcolor1') . "\n"
          ."}\n\n"
          .".pnf_bordercolor2 {\n"
          ."    border-color: " . pnThemeGetVar('bgcolor2') . "\n"
          ."}\n\n"
          .".pnf_bordercolor3 {\n"
          ."    border-color: " . pnThemeGetVar('bgcolor3') . "\n"
          ."}\n\n"
          .".pnf_bordercolor4 {\n"
          ."    border-color: " . pnThemeGetVar('bgcolor4') . "\n"
          ."}\n\n"
          .".pnf_bordercolor5 {\n"
          ."    border-color: " . pnThemeGetVar('bgcolor5') . "\n"
          ."}\n"
          ."</style>\n\n";
    return $css;
}

?>