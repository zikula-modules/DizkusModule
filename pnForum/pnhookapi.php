<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                               *
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
 * Hook API functions
 * @version $Id$
 * @author Andreas Krapohl, Frank Schummertz, Steffen Voss
 * @copyright 2004 by Andreas Krapohl, Frank Schummertz, Steffen Voss
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

include_once('modules/pnForum/common.php');

/**
 * createbyitem
 * createhook function (open new topic)
 *
 */
function pnForum_hookapi_createbyitem($args)
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

    $topic_id = pnModAPIFunc('pnForum', 'user', 'get_topicid_by_reference',
                             array('reference' => $reference));

    if($topic_id == false) {
        // not found

        // we need some input for the initial posting in the topic, but having the
        // topmost module and the objectid is not enough.
        // this means we need to have several "plugins" for modules like News,
        // photoshare or Pagesetter, which can deliver this information
        // to pnForum.
        // these plugins will be stored in modules/pnForum/pncommentsapi/<modulename>.php
        // this way we can enhance the comments funtionality very easily be adding
        // new files there
        //
        // if there is no special file, we just display standard text portions

        $subject   = _PNFORUM_AUTOMATICDISCUSSIONSUBJECT;
        $message   = _PNFORUM_AUTOMATICDISCUSSIONMESSAGE;
        $pntopic   = 0;
        $authorid  = pnUserGetVar('uid');

        $functionfilename = pnVarPrepForStore('modules/pnForum/pncommentsapi/' . $modname . '.php');
        if(file_exists($functionfilename) && is_readable($functionfilename)) {
            list($subject, $message, $pntopic, $authorid) = pnModAPIFunc('pnForum', 'comments', $modname, array('objectid' => $objectid));
        }

        // get the target forum
        list($dbconn, $pntable) = pnfOpenDB();
        $forumtable = $pntable['pnforum_forums'];
        $forumcolumn = $pntable['pnforum_forums_column'];

        $sql = "SELECT $forumcolumn[forum_id],
                       $forumcolumn[forum_pntopic]
                FROM $forumtable
                WHERE $forumcolumn[forum_moduleref]='" . pnVarPrepForStore($modid) . "'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

        $forumsfound = array();
        while(!$result->EOF) {
            list($forum_id, $forum_pntopic) = $result->fields;
            $forumsfound[$forum_pntopic] = $forum_id;
            $result->MoveNext();
        }
        pnfCloseDB($result);

        if(count($forumsfound)<>0) {
            ksort($forumsfound);
            // thanks to Franky Chestnut to figure out the following logic
            if(array_key_exists($pntopic, $forumsfound) || isset($forumsfound['-1'])) {
                $forum_id = (!isset($forumsfound[$pntopic]) ? $forumsfound['-1'] : $forumsfound[$pntopic]);
                pnModAPIFunc('pnForum', 'user', 'storenewtopic',
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
function pnForum_hookapi_updatebyitem($args)
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

    $topic_id = pnModAPIFunc('pnForum', 'user', 'get_topicid_by_reference',
                             array('reference' => $reference));

    if($topic_id <> false) {
        // found

        // get first post id
        $post_id = pnModAPIFunc('pnForum', 'user', 'get_firstlast_post_in_topic',
                                array('topic_id' => $topic_id,
                                      'first'    => true,
                                      'id_only'  => true));

        if($post_id <> false) {
            $functionfilename = pnVarPrepForStore('modules/pnForum/pncommentsapi/' . $modname . '.php');
            if(file_exists($functionfilename) && is_readable($functionfilename)) {
                list($subject, $message, $pntopic, $authorid) = pnModAPIFunc('pnForum', 'comments', $modname, array('objectid' => $objectid));
            }
            pnModAPIFunc('pnForum', 'user', 'updatepost',
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
function pnForum_hookapi_deletebyitem($args)
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

    $topic_id = pnModAPIFunc('pnForum', 'user', 'get_topicid_by_reference',
                             array('reference' => $reference));

    if($topic_id <> false) {
        if(pnModGetVar('pnForum', 'deletehookaction') == 'remove') {
            pnModAPIFunc('pnForum', 'user', 'deletetopic', array('topic_id' => $topic_id));
        } else {
            pnModAPIFunc('pnForum', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode' => 'lock'));
        }
    }

    return true;
}

?>