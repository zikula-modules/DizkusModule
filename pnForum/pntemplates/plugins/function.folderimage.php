<?php
// $Id$
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


// type, id
function smarty_function_folderimage($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(!pnModAPILoad('pnForum', 'user')) {
        $smarty->trigger_error("loading userapi failed", e_error);
        return;
    } 

    if(empty($forum)) {
        $smarty->trigger_error("folderimage: missing parameter 'forom'",e_error);
        return false;
    }
    $last_visit = pnSessionGetVar('pnForum_lastvisit');
    $folder_image = pnModGetVar('pnForum', 'folder_image');
    $newposts_image = pnModGetVar('pnForum', 'newposts_image');

					if ($forum_topics != 0) {
						// are there new topics since last_visit?
						if ($row['post_time'] > $last_visit) {
							// we have new posts
							$fldr_img = $newposts_image;
							$fldr_alt = _PNFORUM_NEWPOSTS;
						} else {
							// no new posts
							$fldr_img = $folder_image;
							$fldr_alt = _PNFORUM_NONEWPOSTS;
						}

					$posted_unixtime= strtotime ($row['post_time']);
					$posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($posted_unixtime));
					if ($posted_unixtime) {
						if ($row['pn_uid']==1) {
							$username = pnConfigGetVar('anonymous');
						} else {
							$username = $row['pn_uname'];
						}

					$last_post = sprintf(_PNFORUM_LASTPOSTSTRING, $posted_ml, $username);
					$last_post = $last_post." <a href=\"$baseurl&amp;action=viewtopic&amp;topic=".$row['topic_id']."\">"
								."<img src=\"modules/$ModName/images/icon_latest_topic.gif\" alt=\"".$posted_ml." ".$username."\" height=\"9\" width=\"18\"></a>";
				} else {
					// no posts in forum
					$last_post = _PNFORUM_NOPOSTS;
				}
			} else {
				// there are no posts in this forum
				$fldr_img = $folder_image;
				$fldr_alt = _PNFORUM_NONEWPOSTS;
				$last_post = _PNFORUM_NOPOSTS;
			}
    
    return pnModAPIFunc('pnForum', 'admin', 'boardstats',
                        array('id'   => $id,
                              'type' => $type));

}

?>