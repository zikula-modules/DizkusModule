<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                               *
 ************************************************************************
 * Modified version of:                                                 *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License                                                              *
 ************************************************************************
 * This program is free software; you can redistribute it and/or modify *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation; either version 2 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * This program is distributed in the hope that it will be useful,      *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with this program; if not, write to the Free Software          *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 *
 * USA                                                                  *
 ************************************************************************
 *
 * fake PNphpBB2 module for redirects of old links
 * @version $Id: common.php,v 1.37 2006/03/25 08:25:42 landseer Exp $
 * @author Frank Schummertz
 * @copyright 2006 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

if (!defined('LOADED_AS_MODULE')) {
    die ('Access Denied');
}

modules_get_language();

$post_id = pnVarCleanFromInput('p');
$topic_id = pnVarCleanFromInput('t');

$pnr = new pnRender('PNphpBB2');
$pnr->caching = false;

// if topic_id is set we can directly go to the same topic_id in pnForum
if(isset($topic_id) &&!empty($topic_id) && is_numeric($topic_id)) {
    $pnr->assign('topic_id', $topic_id);
    $pnr->assign('url', pnModURL('pnForum', 'user', 'viewtopic', array('topic' => $topic_id)));
    include('header.php');
    echo $pnr->fetch('pnphpbb2_topicredirect.html');
    include('footer.php');
    exit();
}    


// if p iset we need to get the topicid first and find the pagewhere this post is
$topic_id = pnModAPIFunc('pnForum', 'user', 'get_topicid_by_postid',
                         array('post_id' => $post_id));
if($topic_id <> false) {
}

$pnr->assign('post_id', $post_id);
$pnr->assign('topic_id', $topic_id);
return $pnr->fetch('pnphpbb2_redirect.html');












?>