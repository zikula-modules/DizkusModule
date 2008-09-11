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
 * createbyitem
 * createhook function (open new topic)
 *
 */
function Dizkus_hookapi_createbyitem($args)
{
    extract($args);
    unset($args);

    if(!isset($extrainfo['module']) || empty($extrainfo['module'])) {
        $modname = pnModGetName();
    } else {
        $modname = $extrainfo['module'];
    }

    if(isset($extrainfo['itemid']) && !empty($extrainfo['itemid'])) {
        $objectid = $extrainfo['itemid'];
    }

    if(!isset($objectid) || empty($objectid)) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    // fix for news
    if($modname=='AddStory') {
        $modname = 'News';
    }

    // we have an objectid now, we combine this with the module id now for the
    // reference and check if it already exists
    $modid = pnModGetIDFromName($modname);
    $reference =  $modid . '-' . $objectid;

    $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_reference',
                             array('reference' => $reference));

    if($topic_id == false) {
        // not found

        // we need some input for the initial posting in the topic, but having the
        // topmost module and the objectid is not enough.
        // this means we need to have several "plugins" for modules like News,
        // photoshare or Pagesetter, which can deliver this information
        // to Dizkus.
        // these plugins will be stored in modules/Dizkus/pncommentsapi/<modulename>.php
        // this way we can enhance the comments funtionality very easily be adding
        // new files there
        //
        // if there is no special file, we just display standard text portions

        $subject   = _DZK_AUTOMATICDISCUSSIONSUBJECT;
        $message   = _DZK_AUTOMATICDISCUSSIONMESSAGE;
        $pntopic   = 0;
        $authorid  = pnUserGetVar('uid');

        $functionfilename = DataUtil::formatForStore('modules/Dizkus/pncommentsapi/' . $modname . '.php');
        if(file_exists($functionfilename) && is_readable($functionfilename)) {
            list($subject, $message, $pntopic, $authorid) = pnModAPIFunc('Dizkus', 'comments', $modname, array('objectid' => $objectid));
        }

        // get the target forum
        list($dbconn, $pntable) = dzkOpenDB();
        $forumtable = $pntable['dizkus_forums'];
        $forumcolumn = $pntable['dizkus_forums_column'];

        $sql = "SELECT $forumcolumn[forum_id],
                       $forumcolumn[forum_pntopic]
                FROM $forumtable
                WHERE $forumcolumn[forum_moduleref]='" . DataUtil::formatForStore($modid) . "'";
        $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

        $forumsfound = array();
        while(!$result->EOF) {
            list($forum_id, $forum_pntopic) = $result->fields;
            $forumsfound[$forum_pntopic] = $forum_id;
            $result->MoveNext();
        }
        dzkCloseDB($result);

        if(count($forumsfound)<>0) {
            ksort($forumsfound);
            // thanks to Franky Chestnut to figure out the following logic
            if(array_key_exists($pntopic, $forumsfound) || isset($forumsfound['-1'])) {
                $forum_id = (!isset($forumsfound[$pntopic]) ? $forumsfound['-1'] : $forumsfound[$pntopic]);
                pnModAPIFunc('Dizkus', 'user', 'storenewtopic',
                             array('forum_id'  => $forum_id,
                                   'subject'   => $subject,
                                   'message'   => $message,
                                   'reference' => $reference,
                                   'post_as'   => $authorid));
            }
        }

    }
    return $extrainfo;
}

/**
 * updatebyitem
 * updatehook function
 *
 */
function Dizkus_hookapi_updatebyitem($args)
{
    extract($args);
    unset($args);
    if(!isset($extrainfo['module']) || empty($extrainfo['module'])) {
        $modname = pnModGetName();
    } else {
        $modname = $extrainfo['module'];
    }

    if(isset($extrainfo['itemid']) && !empty($extrainfo['itemid'])) {
        $objectid = $extrainfo['itemid'];
    }

    if(!isset($objectid) || empty($objectid)) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    // fix for news
    if($modname=='AddStory') {
        $modname = 'News';
    }

    // we have an objectid now, we combine this with the module id now for the
    // reference and check if it already exists
    $modid = pnModGetIDFromName($modname);
    $reference =  $modid . '-' . $objectid;

    $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_reference',
                             array('reference' => $reference));

    if($topic_id <> false) {
        // found

        // get first post id
        $post_id = pnModAPIFunc('Dizkus', 'user', 'get_firstlast_post_in_topic',
                                array('topic_id' => $topic_id,
                                      'first'    => true,
                                      'id_only'  => true));

        if($post_id <> false) {
            $functionfilename = DataUtil::formatForStore('modules/Dizkus/pncommentsapi/' . $modname . '.php');
            if(file_exists($functionfilename) && is_readable($functionfilename)) {
                list($subject, $message, $pntopic, $authorid) = pnModAPIFunc('Dizkus', 'comments', $modname, array('objectid' => $objectid));
            }
            pnModAPIFunc('Dizkus', 'user', 'updatepost',
                         array('post_id'  => $post_id,
                               'subject'  => $subject,
                               'message'  => $message));
        }

    }
    return $extrainfo;
}

/**
 * deletebyitem
 * deletehook function (closes a topic or removes it depending on the setting)
 *
 */
function Dizkus_hookapi_deletebyitem($args)
{
    extract($args);
    unset($args);

    if(!isset($extrainfo['module']) || empty($extrainfo['module'])) {
        $modname = pnModGetName();
    } else {
        $modname = $extrainfo['module'];
    }

    if(isset($extrainfo['itemid']) && !empty($extrainfo['itemid'])) {
        $objectid = $extrainfo['itemid'];
    }

    if(!isset($objectid) || empty($objectid)) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }

    // fix for news
    if($modname=='AddStory') {
        $modname = 'News';
    }

    // we have an objectid now, we combine this with the module id now for the
    // reference and check if it already exists
    $modid = pnModGetIDFromName($modname);
    $reference =  $modid . '-' . $objectid;

    $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_reference',
                             array('reference' => $reference));

    if($topic_id <> false) {
        if(pnModGetVar('Dizkus', 'deletehookaction') == 'remove') {
            pnModAPIFunc('Dizkus', 'user', 'deletetopic', array('topic_id' => $topic_id));
        } else {
            pnModAPIFunc('Dizkus', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode' => 'lock'));
        }
    }

    return true;
}
