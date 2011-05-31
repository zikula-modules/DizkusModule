<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id: function.mediaattach_attachicon.php 1308 2010-06-13 16:40:00Z Landseer $
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
        $smarty->trigger_error("Error! In 'smarty_function_mediaattach_attachicon', the 'topics' parameter is missing.");
        return false;
    }

    $outTopics = array();
    foreach($params['topics'] as $topic) {
        $outTopics[$topic['topic_id']] = (ModUtil::apiFunc('MediaAttach', 'user', 'countuploads', array('moduleFilter' => 'Dizkus', 'objectidFilter' => $topic['topic_id'])) > 0);
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $outTopics);
    } else {
        return $outTopics;
    }
}
