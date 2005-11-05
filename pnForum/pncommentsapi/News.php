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
/*
 * param: objectid
 */

function pnForum_commentsapi_News($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();
    $pnstoriestable = $pntable['stories'];
    $pnstoriescolumn = $pntable['stories_column'];

    $sql = "SELECT $pnstoriescolumn[bodytext],
                   $pnstoriescolumn[hometext],
                   $pnstoriescolumn[notes],
                   $pnstoriescolumn[title],
                   $pnstoriescolumn[topic],
                   $pnstoriescolumn[aid],
                   $pnstoriescolumn[format_type]
            FROM $pnstoriestable
            WHERE $pnstoriescolumn[sid] ='" . pnVarPrepForStore($objectid) . "'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    if(!$result->EOF) {
        list($bodytext,
             $hometext,
             $notes,
             $title,
             $topic,
             $authorid,
             $format_type) = $result->fields;
        pnfCloseDB($result);
    }

    // workaround for bug in AddStories html fixed on 11-05-2005
    $authorid = (int)$authorid;

    $totaltext = $hometext . "\n\n" . $bodytext . "\n\n" . $notes . "\n\n";

    $url = 'index.php?name=News&file=article&sid=' . $objectid;
    if(pnModIsHooked('pn_bbcode', 'pnForum')) {
        $totaltext .= '[url=' . pnGetBaseURL() . $url . ']' . _PNFORUM_BACKTOSUBMISSION . "[/url]\n\n";
    } else {
        $totaltext .= pnGetBaseURL() . $url;
    }
    return array($title, $totaltext , $topic, $authorid);
}

?>