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
 *@params $objectid string the id of the item to be discussed in the forum
 */
function Dizkus_hook_showdiscussionlink($args)
{
    extract($args);
    unset($args);

    if(!isset($objectid) || empty($objectid) ) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }


    $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_reference',
                             array('reference' => pnModGetIDFromName(pnModGetName()) . '-' . $objectid));

    if($topic_id <> false) {
        list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');
        $topic = pnModAPIFunc('Dizkus', 'user', 'readtopic',
                              array('topic_id'   => $topic_id,
                                    'last_visit' => $last_visit,
                                    'count'      => false));
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('topic', $topic);
        return $pnr->fetch('dizkus_hook_display.html');
    }
    return;
}
