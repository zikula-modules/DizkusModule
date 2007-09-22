<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

Loader::includeOnce('modules/pnForum/common.php');

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
