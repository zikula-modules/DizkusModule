<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

// type, id
function smarty_function_folderimage($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(!pnModAPILoad('Dizkus', 'user')) {
        $smarty->trigger_error("loading Dizkus userapi failed");
        return;
    } 

    if(empty($forum)) {
        $smarty->trigger_error("folderimage: missing parameter 'forom'");
        return false;
    }
    $last_visit = SessionUtil::getVar('Dizkus_lastvisit');
    $folder_image = pnModGetVar('Dizkus', 'folder_image');
    $newposts_image = pnModGetVar('Dizkus', 'newposts_image');

					if ($forum_topics != 0) {
						// are there new topics since last_visit?
						if ($row['post_time'] > $last_visit) {
							// we have new posts
							$fldr_img = $newposts_image;
							$fldr_alt = _DZK_NEWPOSTS;
						} else {
							// no new posts
							$fldr_img = $folder_image;
							$fldr_alt = _DZK_NONEWPOSTS;
						}

					$posted_unixtime= strtotime ($row['post_time']);
					$posted_ml = ml_ftime(_DATETIMEBRIEF, GetUserTime($posted_unixtime));
					if ($posted_unixtime) {
						if ($row['pn_uid']==1) {
							$username = pnModGetVar('Users', 'anonymous');
						} else {
							$username = $row['pn_uname'];
						}

					$last_post = sprintf(_DZK_LASTPOSTSTRING, $posted_ml, $username);
					$last_post = $last_post." <a href=\"$baseurl&amp;action=viewtopic&amp;topic=".$row['topic_id']."\">"
								."<img src=\"modules/$ModName/images/icon_latest_topic.gif\" alt=\"".$posted_ml." ".$username."\" height=\"9\" width=\"18\" /></a>";
				} else {
					// no posts in forum
					$last_post = _DZK_NOPOSTS;
				}
			} else {
				// there are no posts in this forum
				$fldr_img = $folder_image;
				$fldr_alt = _DZK_NONEWPOSTS;
				$last_post = _DZK_NOPOSTS;
			}
    
    return pnModAPIFunc('Dizkus', 'admin', 'boardstats',
                        array('id'   => $id,
                              'type' => $type));

}
