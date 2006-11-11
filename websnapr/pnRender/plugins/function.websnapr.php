<?php
// $Id: function.closetable2.php,v 1.5.2.1.2.1 2005/08/30 15:58:08 markwest Exp $
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
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the PostNuke implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   pnRender
 * @version      $Id: function.closetable2.php, v2 2005/04/28 msandersen
 * @author       The PostNuke development team
 * @link         http://www.postnuke.com  The PostNuke Home Page
 * @copyright    Copyright (C) 2002 by the PostNuke Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * @author       Frank Schummertz
 * @since        09.11.206
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 */
function smarty_function_websnapr($params, &$smarty)
{
    
    $out = "<link rel=\"stylesheet\" href=\"modules/pnRender/pnstyle/websnapr.css\" type=\"text/css\" />\n"
          ."<script type=\"text/javascript\" src=\"modules/pnRender/pnjavascript/websnapr.js\"></script>\n"
          ."<script type=\"text/javascript\">\n"
          ."    webSnapr.setbaseurl('" . pnGetBaseURL() . "');\n" 
          ."    webSnapr.setimageuri('" . pnGetBaseURI() . "/modules/pnRender/pnimages');\n" 
          ."    webSnapr.addEvent(window, ['load'], webSnapr.init);\n"
          ."</script>\n\n";  
    return $out;
}
?>