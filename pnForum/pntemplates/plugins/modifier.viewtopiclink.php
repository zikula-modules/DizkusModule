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
 * Smarty modifier to create a link to a topic
 *
 * Available parameters:

 * Example
 *
 *   <!--[$username|viewtopiclink]-->
 *
 *
 * @author       Frank Schummertz
 * @author       The pnForum team
 * @since        16. Sept. 2003
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_viewtopiclink($topic_id=null, $subject=null, $forum_name=null, $class=null, $start=null, $last_post_id=null)
{
    if(!isset($topic_id)) {
        return '';
    }

    if(isset($class) && !empty($class)) {
        $class = 'class="' . $class . '"';
    }

    $args = array('topic' => (int)$topic_id);
    if(isset($start)) {
        $args['start'] = (int)$start;
    }

    $url = pnModURL('pnForum', 'user', 'viewtopic', $args);
    if(isset($last_post_id)) {
        $url .= 'pid' . (int)$last_post_id;
    }
    $title = _PNFORUM_GOTO_TOPIC;
    if(isset($forum_name) && !empty($forum_name)) {
        $title .= ' ' . $forum_name . ' ::';
    }
    if(isset($subject) && !empty($subject)) {
        $title .= ' ' . $subject;
    }
    return pnVarPrepHTMLDisplay('<a '. pnVarPrepHTMLDisplay($class) .' href="' . pnVarPrepHTMLDisplay($url) . '" title="' . pnVarPrepHTMLDisplay($title) .'">' . pnVarPrepForDisplay($subject) . '</a>');
}

?>