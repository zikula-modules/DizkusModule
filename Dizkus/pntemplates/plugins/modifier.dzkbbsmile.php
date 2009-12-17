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
 * Smarty modifier to apply the pn_bbsmile transform hooks
 * 
 * Available parameters:

 * Example
 * 
 *   <!--[$MyVar|dzkpnbbsmile]-->
 * 
 * 
 * @author       Frank Schummertz
 * @author       The Dizkus team
 * @since        16. Sept. 2003
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_dzkbbsmile($string)
{
	$extrainfo = array($string);

    if (pnModAvailable('bbsmile')) {
        list($string) = pnModAPIFunc('bbsmile', 'user', 'transform', array('objectid' => '', 'extrainfo' => $extrainfo));
    }

    return $string;                      
}
