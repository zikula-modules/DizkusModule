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
* @author       Carsten Volmer (herr.vorragend)
* @author       The Dizkus team
* @version      $Id$
* @param        $string    string       the users name
* @param        $class     string       the class name for the link (optional)
* @param        $image     string/array the image to show instead of the username (optional)
*                                       may be an array as created by pnimg
* @return       string   the output
*/

function smarty_modifier_dzkprofilelink($string, $class = '', $image = '', $maxLength = 0)
{

    $string     = DataUtil::formatForDisplay($string);
    $class      = DataUtil::formatForDisplay($class);
    $image      = DataUtil::formatForDisplay($image);
    $maxLength  = DataUtil::formatForDisplay($maxLength);

    if (($string == 'Anonymous') || ($string == pnModGetVar('Users', 'anonymous'))) {
        if (empty($image)) {
            $result = $string;
        } else {
            $result = '';
        }
    } else {
        require_once('system/pnRender/plugins/modifier.userprofilelink.php');
        $result = smarty_modifier_userprofilelink($string, $class, $image , $maxLength);
    }

    return $result;
}
