<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                            *
 ************************************************************************
 * Modified version of: *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License *
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
 * search include
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2004 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

include_once("modules/pnForum/common.php");

$search_modules[] = array(
    'title' => 'pnForum',
    'func_search' => 'search_pnForum',
    'func_opt' => 'search_pnForum_opt'
);

function search_pnForum_opt($vars)
{
    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    }
    $forums = pnModAPIFunc('pnForum', 'admin', 'readforums');

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('forums', $forums);
    return $pnr->fetch('pnforum_search.html');
}


function search_pnForum($vars)
{
    if(!isset($vars['active_pnForum'])) {
        return;
    }
    if(!isset($vars['pnForum_limit']) || empty($vars['pnForum_limit'])) {
        $vars['pnForum_limit'] = 10;
    }

    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    }

    // the search function for pnForum is in our pnuserapi.php
    list($searchresults,
         $total_hits) = pnModAPIFunc('pnForum', 'user', 'forumsearch',
                                     array('searchfor' => $vars['q'],
                                           'bool'      => $vars['bool'],
                                           'forums'    => $vars['pnForum_forum'],
                                           'author'    => $vars['pnForum_author'],
                                           'order'     => $vars['pnForum_order'],
                                           'limit'     => $vars['pnForum_limit'],
                                           'startnum'  => $vars['pnForum_startnum']));


    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->assign('searchresults', $searchresults);
    $pnr->assign('total_hits', $total_hits);
    $pnr->assign('perpage', $vars['pnForum_limit']);
    $pnr->assign('startnum', $vars['pnForum_startnum']);
    $pnr->assign('internalsearch', $vars['internalsearch']);
    $pnr->assign('searchfor', urlencode($vars['q']));
    return $pnr->fetch('pnforum_searchresults.html');
}
?>