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

/**
 * mediaattach_attachicon plugin
 * show all files uploaded for give objectid
 *
 * @$params['topics'] topics array
 *
 */
function smarty_function_mediaattach_attachicon($params, &$smarty)
{
    if (!isset($params['topics']) || !is_array($params['topics'])) {
        $smarty->trigger_error("smarty_function_mediaattach_attachicon: missing parameter 'topics'");
        return false;
    }

    $outTopics = array();
    foreach($params['topics'] as $topic) {
        $outTopics[$topic['topic_id']] = (pnModAPIFunc('MediaAttach', 'user', 'countuploads', array('moduleFilter' => 'Dizkus', 'objectidFilter' => $topic['topic_id'])) > 0);
    }
//die(print_r($outTopics));
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $outTopics);
    }
    else {
        return $outTopics;
    }

}
