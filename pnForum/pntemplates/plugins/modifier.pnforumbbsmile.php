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
 * Smarty modifier to apply the pn_bbsmile transform hooks
 * 
 * Available parameters:

 * Example
 * 
 *   <!--[$MyVar|pnforumpnbbsmile]-->
 * 
 * 
 * @author       Frank Schummertz
 * @author       The pnForum team
 * @since        16. Sept. 2003
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_pnforumbbsmile($string)
{
	$extrainfo = array($string);

    if(pnModAvailable('pn_bbsmile') && pnModAPILoad('pn_bbsmile', 'user')) {
        list($string) = pnModAPIFunc('pn_bbsmile', 'user', 'transform', array('objectid' => '', 'extrainfo' => $extrainfo));
    }
    return $string;                      
}
