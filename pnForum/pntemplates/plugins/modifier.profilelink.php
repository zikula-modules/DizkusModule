<?php
// $Id$
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2001 by the PostNuke Development Team.
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
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the PostNuke implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   pnRender
 * @version      $Id$
 * @author       The PostNuke development team
 * @link         http://www.postnuke.com  The PostNuke Home Page
 * @copyright    Copyright (C) 2002 by the PostNuke Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * Smarty modifier to create a link to a users profile
 *
 * Available parameters:
 *
 * Example
 *
 *   Simple version, shows $username
 *   <!--[$username|profilelink]-->
 *   Simple version, shows $username, using class="classname"
 *   <!--[$username|profilelink:classname]-->
 *   Using profile.gif instead of username, no class
 *   <!--[$username|profilelink:'':'images/profile.gif']-->
 *
 *   Using language depending image from pnimg. Note that we pass
 *   the pnimg result array to the modifier as-is
 *   <!--[ pnimg src='profile.gif' assign=profile]-->
 *   <!--[$username|profilelink:'classname':$profile]-->
 *
 * @author       Frank Schummertz
 * @author       The pnForum team
 * @version      $Id$
 * @param        $string    string       the users name
 * @param        $class     string       the class name for the link (optional)
 * @param        $image     string/array the image to show instead of the username (optional)
 *                                       may be an array as created by pnimg
 * @return       string   the output
 */
function smarty_modifier_profilelink($string, $class='', $image='')
{
    $string = pnVarPrepForDisplay($string);

    if(!empty($class)) {
        $class = 'class="' . $class . '" ';
    }

    if(!empty($image)) {
        if(is_array($image)) {
            // if it is an array we assume that it is an pnimg array
            $show = '<img src="' . $image['src'] . '" alt="' . $image['alt'] . '" width="' . $image['width'] . '" height="' . $image['height'] . '" />';

        } else {
            $show = '<img src="' . $image . '" alt="' . $string . '" />';
        }
    } else {
        $show = $string;
    }
    return '<a ' . $class . 'title="'. pnVarPrepForDisplay(_PNFORUM_PROFILE) . ': ' . $string . '" href="user.php?op=userinfo&amp;uname=' . $string . '">' . $show . '</a>';
}

?>