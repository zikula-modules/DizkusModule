<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

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
    $string = DataUtil::formatForDisplay($string);

    if(!empty($class)) {
        $class = 'class="' . $class . '" ';
    }

    if(pnUserGetIDFromName($string) <> false) {
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
        return '<a ' . $class . 'title="'. DataUtil::formatForDisplay(_PNFORUM_PROFILE) . ': ' . $string . '" href="' . DataUtil::formatForDisplay(pnModURL('Profile', 'user', 'view', array('uname' =>  $string))) . '">' . $show . '</a>';
    } else {
        return $string;
    }
}
