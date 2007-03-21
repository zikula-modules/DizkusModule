<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                            *
 ************************************************************************
 * Modified version of: *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License *
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
 * user module
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2004 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

include_once('modules/pnForum/common.php');

/**
 * reply
 *
 */
function pnForum_ajax_reply()
{
    list($topic_id,
         $message,
         $attach_signature,
         $subscribe_topic,
         $preview) = pnVarCleanFromInput('topic',
                                         'message',
                                         'attach_signature',
                                         'subscribe_topic',
                                         'preview');
    $preview          = ($preview=='1') ? true : false;

    pnSessionSetVar('pn_ajax_call', 'ajax');
    
    $message = pnfstriptags(pnf_convert_from_utf8($message));
    // check for maximum message size
    if( (strlen($message) +  strlen('[addsig]')) > 65535  ) {
        pnf_ajaxerror(_PNFORUM_ILLEGALMESSAGESIZE);
    }

    if($preview==false) {
        if (!pnSecConfirmAuthKey()) {
           pnf_ajaxerror(_BADAUTHKEY);
        }

        list($start,
             $post_id ) = pnModAPIFunc('pnForum', 'user', 'storereply',
                                       array('topic_id'         => $topic_id,
                                             'message'          => $message,
                                             'attach_signature' => $attach_signature,
                                             'subscribe_topic'  => $subscribe_topic));

        $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                             array('post_id' => $post_id));
    } else {
        // preview == true, create fake post
        $post['post_id']      = 0;
        $post['topic_id']     = $topic_id;
        $post['poster_data'] = pnModAPIFunc('pnForum', 'user', 'get_userdata_from_id', array('userid' => pnUserGetVar('uid')));
        // create unix timestamp
        $post['post_unixtime'] = time();
        $post['posted_unixtime'] = $post['post_unixtime'];

        $post['post_textdisplay'] = phpbb_br2nl($message);
        if($attach_signature == 1) {
            $post['post_textdisplay'] .= '[addsig]';
            $post['post_textdisplay'] = pnForum_replacesignature($post['post_textdisplay'], $post['poster_data']['pn_user_sig']);
        }
        // call hooks for $message_display ($message remains untouched for the textarea)
        list($post['post_textdisplay']) = pnModCallHooks('item', 'transform', $post['post_id'], array($post['post_textdisplay']));
        $post['post_textdisplay'] =pnVarCensor(nl2br($post['post_textdisplay']));

        $post['post_text'] = $post['post_textdisplay'];

    }

    $pnr = new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('post', $post);
    $pnr->assign('preview', $preview);
    pnf_jsonizeoutput(array('data'    => $pnr->fetch('pnforum_user_singlepost.html'),
                            'post_id' => $post['post_id']),
                      true);
    exit;
}

/**
 * preparequote
 *
 */
function pnForum_ajax_preparequote()
{
    $post_id = pnVarCleanFromInput('post');
    pnSessionSetVar('pn_ajax_call', 'ajax');

    if(!empty($post_id)) {
        $post = pnModAPIFunc('pnForum', 'user', 'preparereply',
                             array('post_id'     => $post_id,
                                   'quote'       => true,
                                   'reply_start' => true));
        pnf_jsonizeoutput($post, false);
        exit;
    }
    pnf_ajaxerror('internal error: no post id in pnForum_ajax_preparequote()');
}

/**
 * readpost
 *
 */
function pnForum_ajax_readpost()
{
    $post_id = pnVarCleanFromInput('post');
    pnSessionSetVar('pn_ajax_call', 'ajax');

    if(!empty($post_id)) {
        $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                             array('post_id'     => $post_id));
        if($post['poster_data']['edit'] == true) {
            pnf_jsonizeoutput($post, false);
            exit;
        } else {
            pnf_ajaxerror(_PNFORUM_NOAUTH);
        }
    }
    pnf_ajaxerror('internal error: no post id in pnForum_ajax_readpost()');
}

/**
 * editpost
 *
 */
function pnForum_ajax_editpost()
{
    $post_id = pnVarCleanFromInput('post');
    pnSessionSetVar('pn_ajax_call', 'ajax');

    if(!empty($post_id)) {
        $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                             array('post_id'     => $post_id));
        if($post['poster_data']['edit'] == true) {
            $pnr = new pnRender('pnForum');
            $pnr->caching = false;
            $pnr->add_core_data();
            $pnr->assign('post', $post);
            // simplify our live
            $pnr->assign('postingtextareaid', 'postingtext_' . $post['post_id'] . '_edit');

            pnf_jsonizeoutput(array('data'    => $pnr->fetch('pnforum_ajax_editpost.html'),
                                    'post_id' => $post['post_id']),
                                    true);
            exit;
        } else {
            pnf_ajaxerror(_PNFORUM_NOAUTH);
        }
    }
    pnf_ajaxerror('internal error: no post id in pnForum_ajax_readrawtext()');

}

/**
 * updatepost
 *
 */
function pnForum_ajax_updatepost()
{
    list($post_id,
         $subject,
         $message,
         $delete,
         $attach_signature) = pnVarCleanFromInput('post',
                                                  'subject',
                                                  'message',
                                                  'delete',
                                                  'attach_signature');
    pnSessionSetVar('pn_ajax_call', 'ajax');
    if(!empty($post_id)) {
        if (!pnSecConfirmAuthKey()) {
            pnf_ajaxerror(_BADAUTHKEY);
        }
 
        $message = pnfstriptags(pnf_convert_from_utf8($message));
        // check for maximum message size
        if( (strlen($message) +  strlen('[addsig]')) > 65535  ) {
            pnf_ajaxerror(_PNFORUM_ILLEGALMESSAGESIZE);
        }
        pnModAPIFunc('pnForum', 'user', 'updatepost',
                     array('post_id'          => $post_id,
                           'subject'          => pnf_convert_from_utf8($subject),
                           'message'          => $message,
                           'delete'           => $delete,
                           'attach_signature' => ($attach_signature==1)));
        if($delete <> '1') {
            $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                                 array('post_id'     => $post_id));
            $post['action'] = 'updated';
        } else {
            $post = array('action'  => 'deleted',
                          'post_id' => $post_id);
        }
        pnf_jsonizeoutput($post, true);
        exit;
    }
    pnf_ajaxerror('internal error: no post id in pnForum_ajax_updatepost()');

}

/**
 * lockunlocktopic
 *
 */
function pnForum_ajax_lockunlocktopic()
{
    list($topic_id, $mode) = pnVarCleanFromInput('topic', 'mode');
    pnSessionSetVar('pn_ajax_call', 'ajax');

    if (!pnSecConfirmAuthKey()) {
       //pnf_ajaxerror(_BADAUTHKEY);
    }

    if(empty($topic_id)) {
        pnf_ajaxerror('internal error: no topic id in pnForum_ajax_lockunlocktopic()');
    }
    if( empty($mode) || (($mode <> 'lock') && ($mode <> 'unlock')) ) {
        pnf_ajaxerror('internal error: no or illegal mode (' . pnVarPrepForDisplay($mode) . ') parameter in pnForum_ajax_lockunlocktopic()');
    }

    list($forum_id, $cat_id) = pnModAPIFunc('pnForum', 'user', 'get_forumid_and_categoryid_from_topicid',
                                            array('topic_id' => $topic_id));

    if(!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        return pnf_ajaxerror(_PNFORUM_NOAUTH_TOMODERATE);
    }

    pnModAPIFunc('pnForum', 'user', 'lockunlocktopic',
                 array('topic_id' => $topic_id,
                       'mode'     => $mode));
    $newmode = ($mode=='lock') ? 'locked' : 'unlocked';
    pnf_jsonizeoutput($newmode);
    exit;
}

/**
 * stickyunstickytopic
 *
 */
function pnForum_ajax_stickyunstickytopic()
{
    list($topic_id, $mode) = pnVarCleanFromInput('topic', 'mode');
    pnSessionSetVar('pn_ajax_call', 'ajax');

    if (!pnSecConfirmAuthKey()) {
       //pnf_ajaxerror(_BADAUTHKEY);
    }

    if(empty($topic_id)) {
        pnf_ajaxerror('internal error: no topic id in pnForum_ajax_stickyunstickytopic()');
    }
    if( empty($mode) || (($mode <> 'sticky') && ($mode <> 'unsticky')) ) {
        pnf_ajaxerror('internal error: no or illegal mode (' . pnVarPrepForDisplay($mode) . ') parameter in pnForum_ajax_stickyunstickytopic()');
    }

    list($forum_id, $cat_id) = pnModAPIFunc('pnForum', 'user', 'get_forumid_and_categoryid_from_topicid',
                                            array('topic_id' => $topic_id));

    if(!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        return pnf_ajaxerror(_PNFORUM_NOAUTH_TOMODERATE);
    }

    pnModAPIFunc('pnForum', 'user', 'stickyunstickytopic',
                 array('topic_id' => $topic_id,
                       'mode'     => $mode));
    pnf_jsonizeoutput($mode);
    exit;
}

/**
 * subscribeunsubscribetopic
 *
 */
function pnForum_ajax_subscribeunsubscribetopic()
{
    pnSessionSetVar('pn_ajax_call', 'ajax');
    list($topic_id, $mode) = pnVarCleanFromInput('topic', 'mode');

    if (!pnSecConfirmAuthKey()) {
       //pnf_ajaxerror(_BADAUTHKEY);
    }

    if(empty($topic_id)) {
        pnf_ajaxerror('internal error: no topic id in pnForum_ajax_subscribeunsubscribetopic()');
    }

    list($forum_id, $cat_id) = pnModAPIFunc('pnForum', 'user', 'get_forumid_and_categoryid_from_topicid',
                                            array('topic_id' => $topic_id));

    if(!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        return pnf_ajaxerror(_PNFORUM_NOAUTH_TOREAD);
    }

    switch($mode) {
        case 'subscribe':
            pnModAPIFunc('pnForum', 'user', 'subscribe_topic',
                         array('topic_id' => $topic_id,
                               'silent'   => true));
            $newmode = 'subscribed';
            break;
        case 'unsubscribe':
            pnModAPIFunc('pnForum', 'user', 'unsubscribe_topic',
                         array('topic_id' => $topic_id,
                               'silent'   => true));
            $newmode = 'unsubscribed';
            break;
        default:
        pnf_ajaxerror('internal error: no or illegal mode (' . pnVarPrepForDisplay($mode) . ') parameter in pnForum_ajax_subscribeunsubscribetopic()');
    }


    pnSessionDelVar('pn_ajax_call');
    pnf_jsonizeoutput($newmode);
    exit;
}

/**
 * subscribeunsubscribeforum
 *
 */
function pnForum_ajax_subscribeunsubscribeforum()
{
    pnSessionSetVar('pn_ajax_call', 'ajax');
    list($forum_id, $mode) = pnVarCleanFromInput('forum', 'mode');

    if (!pnSecConfirmAuthKey()) {
       //pnf_ajaxerror(_BADAUTHKEY);
    }

    if(empty($forum_id)) {
        pnf_ajaxerror('internal error: no forum id in pnForum_ajax_subscribeunsubscribeforum()');
    }

    $cat_id = pnModAPIFunc('pnForum', 'user', 'get_forum_category',
                           array('forum_id' => $forum_id));

    if(!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        return pnf_ajaxerror(_PNFORUM_NOAUTH_TOREAD);
    }

    switch($mode) {
        case 'subscribe':
            pnModAPIFunc('pnForum', 'user', 'subscribe_forum',
                         array('forum_id' => $forum_id,
                               'silent'   => true));
            $newmode = 'subscribed';
            break;
        case 'unsubscribe':
            pnModAPIFunc('pnForum', 'user', 'unsubscribe_forum',
                         array('forum_id' => $forum_id,
                               'silent'   => true));
            $newmode = 'unsubscribed';
            break;
        default:
        pnf_ajaxerror('internal error: no or illegal mode (' . pnVarPrepForDisplay($mode) . ') parameter in pnForum_ajax_subscribeunsubscribeforum()');
    }


    pnSessionDelVar('pn_ajax_call');
    pnf_jsonizeoutput(array('newmode' => $newmode,
                            'forum_id' => $forum_id));
    exit;
}

/**
 * addremovefavorite
 *
 */
function pnForum_ajax_addremovefavorite()
{
    pnSessionSetVar('pn_ajax_call', 'ajax');
    if(pnModGetVar('pnForum', 'favorites_enabled')=='no') {
        pnf_ajaxerror(_PNFORUM_FAVORITESDISABLED);
    }

    list($forum_id, $mode) = pnVarCleanFromInput('forum', 'mode');

    if (!pnSecConfirmAuthKey()) {
       //pnf_ajaxerror(_BADAUTHKEY);
    }

    if(empty($forum_id)) {
        pnf_ajaxerror('internal error: no forum id in pnForum_ajax_addremovefavorite()');
    }

    $cat_id = pnModAPIFunc('pnForum', 'user', 'get_forum_category',
                           array('forum_id' => $forum_id));

    if(!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        return pnf_ajaxerror(_PNFORUM_NOAUTH_TOREAD);
    }

    switch($mode) {
        case 'add':
            pnModAPIFunc('pnForum', 'user', 'add_favorite_forum',
                         array('forum_id' => $forum_id ));
            $newmode = 'added';
            break;
        case 'remove':
            pnModAPIFunc('pnForum', 'user', 'remove_favorite_forum',
                         array('forum_id' => $forum_id ));
            $newmode = 'removed';
            break;
        default:
        pnf_ajaxerror('internal error: no or illegal mode (' . pnVarPrepForDisplay($mode) . ') parameter in pnForum_ajax_addremovefavorite()');
    }


    pnSessionDelVar('pn_ajax_call');
    pnf_jsonizeoutput(array('newmode' => $newmode,
                            'forum_id' => $forum_id));
    exit;
}

/**
 * edittopicsubject
 *
 */
function pnForum_ajax_edittopicsubject()
{
    pnSessionSetVar('pn_ajax_call', 'ajax');
    $topic_id = pnVarCleanFromInput('topic');

    if(!empty($topic_id)) {
        $topic = pnModAPIFunc('pnForum', 'user', 'readtopic',
                             array('topic_id' => $topic_id,
                                   'count'    => false,
                                   'complete' => false  ));
        if($topic['access_topicsubjectedit'] == true) {
            $pnr = new pnRender('pnForum');
            $pnr->caching = false;
            $pnr->add_core_data();
            $pnr->assign('topic', $topic);
            pnf_jsonizeoutput(array('data' => $pnr->fetch('pnforum_ajax_edittopicsubject.html'),
                                    'topic_id' => $topic_id), true);
            exit;
        } else {
            pnf_ajaxerror(_PNFORUM_NOAUTH);
        }
    }
    pnf_ajaxerror('internal error: no topic id in pnForum_ajax_readtopic()');
}

/**
 * updatetopicsubject
 *
 */
function pnForum_ajax_updatetopicsubject()
{
    pnSessionSetVar('pn_ajax_call', 'ajax');
    list($topic_id,
         $subject) = pnVarCleanFromInput('topic',
                                         'subject');
    
    if(!empty($topic_id)) {
        if (!pnSecConfirmAuthKey()) {
           pnf_ajaxerror(_BADAUTHKEY);
        }

        $topic = pnModAPIFunc('pnForum', 'user', 'readtopic',
                             array('topic_id' => $topic_id,
                                   'count'    => false,
                                   'complete' => false  ));
        if(!$topic['access_topicsubjectedit']) {
            return pnf_ajaxerror(_PNFORUM_NOAUTH_TOMODERATE);
        }


        $subject = trim(pnf_convert_from_utf8($subject));
        if(empty($subject)) {
            pnf_ajaxerror(_PNFORUM_NOSUBJECT);
        }

        list($dbconn, $pntable) = pnfOpenDB();

        $sql = "UPDATE ".$pntable['pnforum_topics']."
                SET topic_title = '" . pnVarPrepForStore($subject) . "'
                WHERE topic_id = '".(int)pnVarPrepForStore($topic_id)."'";

        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);
        // Let any hooks know that we have updated an item.
        pnModCallHooks('item', 'update', $topic_id, array('module' => 'pnForum',
                                                          'topic_id' => $topic_id));
        pnf_jsonizeoutput(array('topic_title' => pnVarPrepForDisplay($subject),
                                'topic_id' => $topic_id),
                          true);

    }
    pnf_ajaxerror('internal error: no topic id in pnForum_ajax_updatetopicsubject()');
}

/**
 * changesortorder
 *
 */
function pnForum_ajax_changesortorder()
{
    pnSessionSetVar('pn_ajax_call', 'ajax');

    if(!pnUserLoggedIn()) {
       pnf_ajaxerror(_PNFORUM_USERLOGINTITLE);
    }

    if (!pnSecConfirmAuthKey()) {
       pnf_ajaxerror(_BADAUTHKEY);
    }

    pnModAPIFunc('pnForum', 'user', 'change_user_post_order');
    $newmode = strtolower(pnModAPIFunc('pnForum','user','get_user_post_order'));
    pnf_jsonizeoutput($newmode, true, true);
    exit;
}

/**
 * newtopic
 *
 */
function pnForum_ajax_newtopic()
{
    pnSessionSetVar('pn_ajax_call', 'ajax');

    if (!pnSecConfirmAuthKey()) {
       pnf_ajaxerror(_BADAUTHKEY);
    }

    list($forum_id,
         $message,
         $subject,
         $attach_signature,
         $subscribe_topic,
         $preview) = pnVarCleanFromInput('forum',
                                         'message',
                                         'subject',
                                         'attach_signature',
                                         'subscribe_topic',
                                         'preview');

    $cat_id = pnModAPIFunc('pnForum', 'user', 'get_forum_category',
                           array('forum_id' => $forum_id));

    if(!allowedtowritetocategoryandforum($cat_id, $forum_id)) {
        return pnf_ajaxerror(_PNFORUM_NOAUTH_TOWRITE);
    }

    $preview          = ($preview=='1') ? true : false;
    //$attach_signature = ($attach_signature=='1') ? true : false;
    //$subscribe_topic  = ($subscribe_topic=='1') ? true : false;

    $message = pnfstriptags(pnf_convert_from_utf8($message));
    // check for maximum message size
    if( (strlen($message) +  strlen('[addsig]')) > 65535  ) {
        pnf_ajaxerror(_PNFORUM_ILLEGALMESSAGESIZE);
    }
    if(strlen($message)==0) {
        pnf_ajaxerror(_PNFORUM_EMPTYMSG);
    }

    $subject = pnf_convert_from_utf8($subject);
    if(strlen($subject)==0) {
        pnf_ajaxerror(_PNFORUM_NOSUBJECT);
    }

    $pnr = new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();

    if($preview == false) {
        // store new topic
        $topic_id = pnModAPIFunc('pnForum', 'user', 'storenewtopic',
                                 array('forum_id'         => $forum_id,
                                       'subject'          => $subject,
                                       'message'          => $message,
                                       'attach_signature' => $attach_signature,
                                       'subscribe_topic'  => $subscribe_topic));
        $topic = pnModAPIFunc('pnForum', 'user', 'readtopic',
                              array('topic_id' => $topic_id));
        if(pnModGetVar('pnForum', 'newtopicconfirmation') == 'yes') {
            $pnr->assign('topic', $topic);
            $confirmation = $pnr->fetch('pnforum_ajax_newtopicconfirmation.html');
        } else {
            $confirmation = false;
        }
        pnf_jsonizeoutput(array('topic'        => $topic,
                                'confirmation' => $confirmation,
                                'redirect'     => pnModURL('pnForum', 'user', 'viewtopic',
                                                           array('topic' => $topic_id))),
                          true);

    }

    // preview == true, create fake topic
    $newtopic['cat_id']     = $cat_id;
    $newtopic['forum_id']   = $forum_id;
//    $newtopic['forum_name'] = pnVarPrepForDisplay($myrow['forum_name']);
//    $newtopic['cat_title']  = pnVarPrepForDisplay($myrow['cat_title']);

    $newtopic['topic_unixtime'] = time();

    // need at least "comment" to add newtopic
    if(!allowedtowritetocategoryandforum($newtopic['cat_id'], $newtopic['forum_id'])) {
        // user is not allowed to post
        return showforumerror(_PNFORUM_NOAUTH_TOWRITE, __FILE__, __LINE__);
    }
    $newtopic['poster_data'] = pnForum_userapi_get_userdata_from_id(array('userid' => pnUserGetVar('uid')));

    $newtopic['subject'] = $subject;
    $newtopic['message'] = $message;
    $newtopic['message_display'] = $message; // phpbb_br2nl($message);

    if($attach_signature==1) {
        $newtopic['message_display'] .= '[addsig]';
        $newtopic['message_display'] = pnForum_replacesignature($newtopic['message_display'], $newtopic['poster_data']['pn_user_sig']);
    }

    list($newtopic['message_display']) = pnModCallHooks('item', 'transform', '', array($newtopic['message_display']));
    $newtopic['message_display'] = nl2br($newtopic['message_display']);

    $topic_start = (empty($subject) && empty($message));
    if(pnUserLoggedIn()) {
        if($topic_start==true) {
            $newtopic['attach_signature'] = 1;
            $newtopic['subscribe_topic']  = (pnModGetVar('pnForum', 'autosubscribe')=='yes') ? 1 : 0;
        } else {
            $newtopic['attach_signature'] = $attach_signature;
            $newtopic['subscribe_topic']  = $subscribe_topic;
        }
    } else {
        $newtopic['attach_signature'] = 0;
        $newtopic['subscribe_topic']  = 0;
    }

    $pnr->assign('newtopic', $newtopic);
    pnf_jsonizeoutput(array('data'     => $pnr->fetch('pnforum_user_newtopicpreview.html'),
                            'newtopic' => $newtopic),
                      true);
}

/**
 * forumusers
 * update the "users online" section in the footer
 * original version by gf
 *
 */
function pnForum_ajax_forumusers ()
{
    $pnRender = new pnRender('pnForum');
    $pnRender->caching = false;
    if(is_dot8()) {
        Loader::includeOnce('system/Theme/plugins/outputfilter.shorturls.php');
    } else {
        include_once 'modules/Xanthia/plugins/outputfilter.shorturls.php';
    }
    $pnRender->register_outputfilter('smarty_outputfilter_shorturls');
    $pnRender->display('pnforum_ajax_forumusers.html');
    exit;
}

/**
 * newposts
 * update the "new posts" block
 * original version by gf
 *
 */
function pnForum_ajax_newposts ()
{
    $pnRender = new pnRender('pnForum');
    $pnRender->caching = false;
    if(is_dot8()) {
        Loader::includeOnce('system/Theme/plugins/outputfilter.shorturls.php');
    } else {
        include_once 'modules/Xanthia/plugins/outputfilter.shorturls.php';
    }
    $pnRender->register_outputfilter('smarty_outputfilter_shorturls');
    $pnRender->display('pnforum_ajax_newposts.html');
    exit;
}
?>