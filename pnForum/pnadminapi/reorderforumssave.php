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
 * reorder forums save
 *@params $args['neworder'] int the new order number
 *@params $args['oldorder'] int the old order number
 *@params $args['forum_id'] int the forum id
 *@params $args['cat_id'] int the category id
 */
function pnForum_adminapi_reorderforumssave($args)
{
    extract($args);
    unset($args);

    if( !pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN) &&
        !pnSecAuthAction(0, 'pnForum::CreateForum', $cat_id . "::", ACCESS_EDIT) ) {
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__);
    }

    if(!isset($neworder) || !isset($oldorder)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn, $pntable) = pnfOpenDB();

    $forumtable   = $pntable['pnforum_forums'];
    $forumcolumn  = &$pntable['pnforum_forums_column'];

    $cat_id      = (int)pnVarPrepForStore($cat_id);
    $forum_id    = (int)pnVarPrepForStore($forum_id);
    $neworder    = (int)pnVarPrepForStore($neworder);
    $oldorder    = (int)pnVarPrepForStore($oldorder);

    if ((int)$oldorder > (int)$neworder) {
        if ($neworder < 0) {
            $neworder = 0;
        }
        if ($neworder == 0) {
            $sql = "SELECT $forumcolumn[forum_id],
                $forumcolumn[forum_order]
                    FROM $forumtable
                    WHERE $forumcolumn[forum_order] >= '" . (int)pnVarPrepForStore($oldorder) . "'
                    AND $forumcolumn[cat_id]='" . (int)pnVarPrepForStore($cat_id) . "'
                    ORDER BY $forumcolumn[forum_order] DESC";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            while(list($forumid, $currentOrder) = $result->fields) {
                $sql2 = "UPDATE $forumtable
                    SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($currentOrder-1) . "'
                    WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forumid) . "'";
                $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                pnfCloseDB($result2);
                $result->MoveNext();
            }
            pnfCloseDB($result);
            $sql2 = "UPDATE $forumtable
                SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($neworder) . "'
                WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
            $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
            pnfCloseDB($result2);
        } else {
            $sql = "SELECT $forumcolumn[forum_id],
                $forumcolumn[forum_order]
                    FROM $forumtable
                    WHERE $forumcolumn[forum_order] >= '" . (int)pnVarPrepForStore($neworder) . "'
                    AND $forumcolumn[forum_order] <= '" . (int)pnVarPrepForStore($oldorder) . "'
                    AND $forumcolumn[cat_id]='" . (int)pnVarPrepForStore($cat_id) . "'
                    AND $forumcolumn[forum_order] != '0'
                    ORDER BY $forumcolumn[forum_order] DESC";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            if ($result->EOF) {
                $sql2 = "UPDATE $forumtable
                    SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($neworder) . "'
                    WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
                $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                pnfCloseDB($result2);
            } else {
                while(list($forum_id, $currentOrder) = $result->fields) {
                    if ($currentOrder == $oldorder) {
                        // we are dealing with the old value so make it the new value
                        $currentOrder = $neworder;
                    } else {
                        $currentOrder++;
                    }
                    $sql2 = "UPDATE $forumtable
                        SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($currentOrder) . "'
                        WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
                    $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                    pnfCloseDB($result2);
                    $result->MoveNext();
                }
            }
            pnfCloseDB($result);
        }
    } else {
        // new order > old order

        $sql = "SELECT max(forum_order) AS maxorder
            FROM $forumtable
            WHERE $forumcolumn[cat_id] = '" . (int)pnVarPrepForStore($cat_id) . "'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        list($maxorder) = $result->fields;
        pnfCloseDB($result);

        // The new sequence is lower in the list
        //if the new requested sequence is bigger than
        //the maximum sequence number then set it to
        //the maximum number.  We don't want any spaces
        //in the sequence.
        if ($neworder > ($maxorder+1)) {
            $neworder = ((int)$maxorder)+1;
        }

        if ($oldorder == 0) {
            $sql = "SELECT $forumcolumn[forum_id],
                $forumcolumn[forum_order]
                    FROM $forumtable
                    WHERE $forumcolumn[forum_order] >= '" . (int)pnVarPrepForStore($neworder) . "'
                    AND $forumcolumn[cat_id]='" . (int)pnVarPrepForStore($cat_id) . "'
                    ORDER BY $forumcolumn[forum_order] DESC";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            while(list($forumid, $currentOrder) = $result->fields) {
                $sql2 = "UPDATE $forumtable
                    SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($currentOrder+1) . "'
                    WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forumid) . "'";
                $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                pnfCloseDB($result2);
                $result->MoveNext();
            }
            pnfCloseDB($result);
            $sql2 = "UPDATE $forumtable
                SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($neworder) . "'
                WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
            $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
            pnfCloseDB($result2);
        } else {
            $sql = "SELECT $forumcolumn[forum_id],
                $forumcolumn[forum_order]
                    FROM $forumtable
                    WHERE $forumcolumn[forum_order] >= '" . (int)pnVarPrepForStore($oldorder) . "'
                    AND $forumcolumn[forum_order] <= '" . (int)pnVarPrepForStore($neworder) . "'
                    AND $forumcolumn[cat_id]='" . (int)pnVarPrepForStore($cat_id) . "'
                    AND $forumcolumn[forum_order] != '0'
                    ORDER BY $forumcolumn[forum_order] ASC";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            if ($result->EOF) {
                $sql2 = "UPDATE $forumtable
                    SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($neworder) . "'
                    WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
                $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                pnfCloseDB($result2);
            } else {
                while(list($forum_id, $currentOrder) = $result->fields) {
                    if ($currentOrder == $oldorder) {
                        // we are dealing with the old value so make it the new value
                        $currentOrder = $neworder;
                    } else {
                        $currentOrder--;
                    }
                    $sql2 = "UPDATE $forumtable
                        SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($currentOrder) . "'
                        WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
                    $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                    pnfCloseDB($result2);
                    $result->MoveNext();
                }
            }
            pnfCloseDB($result);
        }

    }
    return;
}

?>