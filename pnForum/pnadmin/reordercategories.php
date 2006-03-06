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
 * reordercategories
 *
 */
function pnForum_admin_reordercategories()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($direction) = pnVarCleanFromInput('direction');

    $categories = pnModAPIFunc('pnForum', 'admin', 'readcategories');

    if(!$direction) {
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('total_categories', count($categories));
        $pnr->assign('categories', $categories);
        return $pnr->fetch("pnforum_admin_reordercategories.html");
    } else {
        list( $cat_id,
              $cat_order,
              $direction ) = pnVarCleanFromInput('cat_id',
                                                 'cat_order',
                                                 'direction');
        pnModAPIFunc('pnForum', 'admin', 'reordercategoriessave',
                     array('cat_id'    => $cat_id,
                           'cat_order' => $cat_order,
                           'direction' => $direction));
    }
    return pnRedirect(pnModURL('pnForum', 'admin', 'reordercategories'));
}

?>