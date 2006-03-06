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
 * category
 *
 */
function pnForum_admin_category()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($submit, $cat_id) = pnVarCleanFromInput('submit', 'cat_id');
    if(!$submit)
    {
        if( $cat_id==-1) {
            $category = array('cat_title' => "",
                              'cat_id' => -1);
            $category['topic_count'] = 0;
            $category['post_count'] = 0;
        } else {
            $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                                     array( 'cat_id' => $cat_id ));
            $forums = pnModAPIFunc('pnForum', 'admin', 'readforums',
                       array('cat_id' => $cat_id));

            foreach($forums as $forum) {
                $category['topic_count'] += pnModAPIFunc('pnForum', 'user', 'boardstats',
                                                         array('type' => 'forumtopics',
                                                               'id'   => $forum['forum_id']));
                $category['post_count'] += pnModAPIFunc('pnForum', 'user', 'boardstats',
                                                        array('type' => 'forumposts',
                                                              'id'   => $forum['forum_id']));
            }
        }
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('category', $category );
        return $pnr->fetch("pnforum_admin_category.html");
    } else { // submit is set
        list($actiontype, $cat_title) = pnVarCleanFromInput('actiontype', 'cat_title');

        switch($actiontype)
        {
            case "Add":
                pnModAPIFunc('pnForum', 'admin', 'addcategory', array('cat_title' => $cat_title));
                break;
            case "Edit":
                pnModAPIFunc('pnForum', 'admin', 'updatecategory', array('cat_id' => $cat_id,
                                                                          'cat_title' => $cat_title));
                break;
            case "Delete":
                pnModAPIFunc('pnForum', 'admin', 'deletecategory', array('cat_id' => $cat_id));
                break;
            default:
        }
        return pnRedirect(pnModUrl('pnForum', 'admin', 'main'));
    }
}

?>