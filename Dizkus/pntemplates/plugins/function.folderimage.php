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
    $dom = ZLanguage::getModuleDomain('Dizkus');

    // TODO deprecate the use of extract
    extract($params); 
    unset($params);

    if (!pnModAPILoad('Dizkus', 'user')) {
        $smarty->trigger_error("Error! Could not load Dizkus user API.");
        return;
    } 

    if (empty($forum)) {
        $smarty->trigger_error("Error! Missing 'forum' parameter for folder image.");
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
            $fldr_alt = __('New posts since your last visit', $dom);
        } else {
            // no new posts
            $fldr_img = $folder_image;
            $fldr_alt = __('New posts since your last visit', $dom);
        }

        $posted_unixtime= strtotime ($row['post_time']);
        $posted_ml = DateUtil::formatDatetime($posted_unixtime, '%b %d, %Y - %I:%M %p');
        if ($posted_unixtime) {
            if ($row['pn_uid']==1) {
                $username = pnModGetVar('Users', 'anonymous');
            } else {
                $username = $row['pn_uname'];
            }

            $last_post = __f('%1$s<br />by %2$s', array($posted_ml, $username), $dom);
            $last_post = $last_post." <a href=\"$baseurl&amp;action=viewtopic&amp;topic=".$row['topic_id']."\">"
              ."<img src=\"modules/$ModName/images/icon_latest_topic.gif\" alt=\"".$posted_ml." ".$username."\" height=\"9\" width=\"18\" /></a>";
        } else {
            // no posts in forum
            $last_post = __('No posts', $dom);
        }
    } else {
        // there are no posts in this forum
        $fldr_img = $folder_image;
        $fldr_alt = __('New posts since your last visit', $dom);
        $last_post = __('No posts', $dom);
    }

    return pnModAPIFunc('Dizkus', 'admin', 'boardstats',
                        array('id'   => $id,
                              'type' => $type));

}
