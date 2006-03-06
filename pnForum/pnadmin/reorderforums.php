<?php

/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                               *
 *                                                                      *
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
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2005 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

include_once 'modules/pnForum/common.php';

/**
 * reorderforums
 *
 */
function pnForum_admin_reorderforums()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
        return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($direction,
            $cat_id,
            $forum_id,
            $direction,
            $editforumorder,
            $oldorder,
            $neworder) = pnVarCleanFromInput('direction',
                'cat_id',
                'forum_id',
                'direction',
                'editforumorder',
                'oldorder',
                'neworder');

    // we are re-sequencing with the arrow keys
    if (!empty($direction)) {
        // figure out the new order
        if ($direction=='up') {
            $neworder = $oldorder-1;
        } else {
            $neworder = $oldorder+1;
        }
    }

    // we either got the neworder because they were editing
    // an entry or because they used an arrow key and we calculated
    // it above
    if (isset($neworder) && is_numeric($neworder)) {
        // call the api function to figure out the new sequence for everything
        pnModAPIFunc('pnForum', 'admin', 'reorderforumssave',
                array('cat_id'      => $cat_id,
                    'forum_id'    => $forum_id,
                    'neworder'    => $neworder,
                    'oldorder'    => $oldorder));
    }

    // if we have been passed a cat_id then lets figure out which forums
    // belong to this category, and get the category details
    if(!empty($cat_id) && is_numeric($cat_id)) {
        // get the list of forums and their data
        $forums = pnModAPIFunc('pnForum', 'admin', 'readforums',
                array('cat_id' => $cat_id));
        // get the category information
        $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                array('cat_id' => $cat_id));
    }

    // show the list of forums and their order
    // NOTE: There is no need to do a pnRedirect because we figure
    // out the forum info after we set the new order if we were editing.
    $pnr =& new pnRender("pnForum");
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('forums', $forums);
    // editforumorder is used to determine if we want to edit the forum_order
    // and contains the forum_id of the forum we want to edit.
    $pnr->assign('editforumorder', $editforumorder);
    $pnr->assign('total_forums', count($forums));
    $pnr->assign('category', $category);
    return $pnr->fetch("pnforum_admin_reorderforums.html");
}


?>