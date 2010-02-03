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

Loader::includeOnce('modules/Dizkus/common.php');

/**
 * showdiscussionlink
 * displayhook function
 *
 * @params $objectid string the id of the item to be discussed in the forum
 */
function Dizkus_hook_showdiscussionlink($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!isset($args['objectid']) || empty($args['objectid']) ) {
        return showforumerror(__('Error! The action you wanted to perform was not successful for some reason, maybe because of a problem with what you input. Please check and try again.', $dom), __FILE__, __LINE__);
    }

    $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_reference',
                             array('reference' => pnModGetIDFromName(pnModGetName()) . '-' . $args['objectid']));

    if ($topic_id <> false) {
        list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

        $topic = pnModAPIFunc('Dizkus', 'user', 'readtopic',
                              array('topic_id'   => $topic_id,
                                    'count'      => false));

        $render = pnRender::getInstance('Dizkus', false, null, true);

        $render->assign('topic', $topic);

        return $render->fetch('dizkus_hook_display.html');
    }

    return false;
}
