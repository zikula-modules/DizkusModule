<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                               *
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
 * Hook functions
 * @version $Id$
 * @author Andreas Krapohl, Frank Schummertz, Steffen Voss
 * @copyright 2004 by Andreas Krapohl, Frank Schummertz, Steffen Voss
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

include_once('modules/pnForum/common.php');

/**
 * showdiscussionlink
 * displayhook function
 *
 *@params $objectid string the id of the item to be discussed in the forum
 */
function pnForum_hook_showdiscussionlink($args)
{
    extract($args);
    unset($args);

    if(!isset($objectid) || empty($objectid) ) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }


    $topic_id = pnModAPIFunc('pnForum', 'user', 'get_topicid_by_reference',
                             array('reference' => pnModGetIDFromName(pnModGetName()) . '-' . $objectid));

    if($topic_id <> false) {
        list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');
        $topic = pnModAPIFunc('pnForum', 'user', 'readtopic',
                              array('topic_id'   => $topic_id,
                                    'last_visit' => $last_visit,
                                    'count'      => false));
        $pnr =& new pnRender('pnForum');

        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('topic', $topic);
        return $pnr->fetch('pnforum_hook_display.html');
    }
    return;
}

?>