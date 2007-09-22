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
 * reply
 *
 */
function pnForum_ajax_reply()
{
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    
    $message = pnfstriptags(DataUtil::convertFromUTF8($message));
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
            $post['post_textdisplay'] = pnForum_replacesignature($post['post_textdisplay'], $post['poster_data']['_SIGNATURE']);
        }
        // call hooks for $message_display ($message remains untouched for the textarea)
        list($post['post_textdisplay']) = pnModCallHooks('item', 'transform', $post['post_id'], array($post['post_textdisplay']));
        $post['post_textdisplay'] =pnfVarPrepHTMLDisplay($post['post_textdisplay']);

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
 
        $message = pnfstriptags(DataUtil::convertFromUTF8($message));
        // check for maximum message size
        if( (strlen($message) +  strlen('[addsig]')) > 65535  ) {
            pnf_ajaxerror(_PNFORUM_ILLEGALMESSAGESIZE);
        }
        pnModAPIFunc('pnForum', 'user', 'updatepost',
                     array('post_id'          => $post_id,
                           'subject'          => DataUtil::convertFromUTF8($subject),
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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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


        $subject = trim(DataUtil::convertFromUTF8($subject));
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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

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

    $message = pnfstriptags(DataUtil::convertFromUTF8($message));
    // check for maximum message size
    if( (strlen($message) +  strlen('[addsig]')) > 65535  ) {
        pnf_ajaxerror(_PNFORUM_ILLEGALMESSAGESIZE);
    }
    if(strlen($message)==0) {
        pnf_ajaxerror(_PNFORUM_EMPTYMSG);
    }

    $subject = DataUtil::convertFromUTF8($subject);
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
        $newtopic['message_display'] = pnForum_replacesignature($newtopic['message_display'], $newtopic['poster_data']['_SIGNATURE']);
    }

    list($newtopic['message_display']) = pnModCallHooks('item', 'transform', '', array($newtopic['message_display']));
    $newtopic['message_display'] = pnfVarPrepHTMLDisplay($newtopic['message_display']);

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
    if(pnf_available(false) == false) {
       pnf_ajaxerror(strip_tags(pnModGetVar('pnForum', 'forum_disabled_info')));
    }

    $pnRender = new pnRender('pnForum');
    $pnRender->caching = false;
    Loader::includeOnce('system/Theme/plugins/outputfilter.shorturls.php');
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
    $disabled = pnf_available();
    if(!is_bool($disabled)) {
        echo $disabled;
        exit;
    }
    $pnRender = new pnRender('pnForum');
    $pnRender->caching = false;
    Loader::includeOnce('system/Theme/plugins/outputfilter.shorturls.php');
    $pnRender->register_outputfilter('smarty_outputfilter_shorturls');
    $pnRender->display('pnforum_ajax_newposts.html');
    exit;
}
