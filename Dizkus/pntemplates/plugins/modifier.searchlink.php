<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
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
 * Smarty modifier to create a a searxhlink for a given username
 *
 * Available parameters:

 * Example
 *
 *   {$pncore.user.uname|searchlink}
 *
 *
 * @author       Frank Schummertz
 * @author       The Dizkus team
 * @since        14. Sept. 2008
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_searchlink($uname=null)
{
    $link = pnModURL('Search', 'user', 'search', array('q'                  => $uname,
                                                       'active[Dizkus]'     => 1,
                                                       'Dizkus_searchwhere' => 'author'), null, null, null, true);
    return DataUtil::formatForDisplay($link);
}
