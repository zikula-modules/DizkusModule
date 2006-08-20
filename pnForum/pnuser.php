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
 * main
 * show all categories and forums a user may see
 *
 *@params 'viewcat' int only expand the category, all others shall be hidden / collapsed
 */
function pnForum_user_main($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        $viewcat = (int)pnVarCleanFromInput('viewcat');
        $favorites = (bool)pnVarCleanFromInput('favorites');
    }
    $viewcat = (!empty($viewcat)) ? $viewcat : -1;

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');
    $loggedIn = pnUserLoggedIn();
    if(pnModGetVar('pnForum', 'favorites_enabled')=='yes') {
        if($loggedIn && empty($favorites)) {
            $favorites = pnModAPIFunc('pnForum', 'user', 'get_favorite_status');
        }
    }
    if ($loggedIn && $favorites) {
        $tree = pnModAPIFunc('pnForum', 'user', 'getFavorites', array('user_id' => (int)pnUserGetVar('uid'),
                                                                      'last_visit' => $last_visit ));
    } else {
        $tree = pnModAPIFunc('pnForum', 'user', 'readcategorytree', array('last_visit' => $last_visit ));

        if(pnModGetVar('pnForum', 'slimforum') == 'yes') {
            // this needs to be in here because we want to display the favorites
            // not go to it if there is only one
            // check if we have one category and one forum only
            if(count($tree)==1) {
                foreach($tree as $catname=>$forumarray) {
                    if(count($forumarray['forums'])==1) {
                        return pnRedirect(pnModURL('pnForum', 'user', 'viewforum', array('forum'=>$forumarray['forums'][0]['forum_id'])));
                    } else {
                        $viewcat = $tree[$catname]['cat_id'];
                    }
                }
            }
        }
    }

    $view_category_data = array();
    if($viewcat <> -1) {
        foreach($tree as $category) {
            if ($category['cat_id'] == $viewcat) {
                $view_category_data = $category;
                break;
            }
        }
    }

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign( 'favorites', $favorites);
    $pnr->assign( 'tree', $tree);
    $pnr->assign( 'view_category', $viewcat);
    $pnr->assign( 'view_category_data', $view_category_data);
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    $pnr->assign( 'numposts', pnModAPIFunc('pnForum', 'user', 'boardstats',
                                            array('id'   => '0',
                                                  'type' => 'all' )));
    return $pnr->fetch('pnforum_user_main.html');
}

/**
 * viewforum
 * opens a forum and shows the last postings
 *
 *@params 'forum' int the forum id
 *@params 'start' int the posting to start with if on page 1+
 */
function pnForum_user_viewforum($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        $forum_id = (int)pnVarCleanFromInput('forum');
        $start    = (int)pnVarCleanFromInput('start');
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    $forum = pnModAPIFunc('pnForum', 'user', 'readforum',
                          array('forum_id'        => $forum_id,
                                'start'           => $start,
                                'last_visit'      => $last_visit,
                                'last_visit_unix' => $last_visit_unix));

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign( 'forum', $forum);
    $pnr->assign( 'hot_threshold', pnModGetVar('pnForum', 'hot_threshold'));
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    return $pnr->fetch('pnforum_user_viewforum.html');
}

/**
 * viewtopic
 *
 */
function pnForum_user_viewtopic($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        $topic_id = (int)pnVarCleanFromInput('topic');
        $start    = (int)pnVarCleanFromInput('start');
        $view     = strtolower(pnVarCleanFromInput('view'));
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    if(!empty($view) && ($view=='next' || $view=='previous')) {
        $topic_id = pnModAPIFunc('pnForum', 'user', 'get_previous_or_next_topic_id',
                                 array('topic_id' => $topic_id,
                                       'view'     => $view));
        return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
                            array('topic' => $topic_id)));
    }
    $topic = pnModAPIFunc('pnForum', 'user', 'readtopic',
                          array('topic_id'   => $topic_id,
                                'start'      => $start,
                                'last_visit' => $last_visit));

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign( 'topic', $topic);
    $pnr->assign( 'post_count', count($topic['posts']));
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    return $pnr->fetch('pnforum_user_viewtopic.html');

}

/**
 * reply
 *
 */
function pnForum_user_reply($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($topic_id,
        	 $post_id,
        	 $message,
        	 $attach_signature,
        	 $subscribe_topic,
        	 $preview,
        	 $submit,
        	 $cancel ) = pnVarCleanFromInput('topic',
        									'post',
        									'message',
        									'attach_signature',
        									'subscribe_topic',
        									'preview',
        									'submit',
        									'cancel');
    }

    $post_id = (int)$post_id;
    $topic_id = (int)$topic_id;
    $attach_signature = (int)$attach_signature;
    $subscribe_topic = (int)$subscribe_topic;

    /**
     * if cancel is submitted move to forum-view
     */
    if(!empty($cancel)) {
    	return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=> $topic_id)));
    }

    $preview = (empty($preview)) ? false : true;
    $submit = (empty($submit)) ? false : true;

    $message = pnfstriptags($message);
    // check for maximum message size
    if( (strlen($message) +  strlen('[addsig]')) > 66535  ) {
        pnSessionSetVar('statusmsg', _PNFORUM_ILLEGALMESSAGESIZE);
        // switch to preview mode
        $preview = true;
    }

    if ($submit==true && $preview==false) {
        // Confirm authorisation code
        if (!pnSecConfirmAuthKey()) {
            return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }

        list($start,
             $post_id ) = pnModAPIFunc('pnForum', 'user', 'storereply',
                                       array('topic_id'         => $topic_id,
                                             'message'          => $message,
                                             'attach_signature' => $attach_signature,
                                             'subscribe_topic'  => $subscribe_topic));
        return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
                            array('topic' => $topic_id,
                                  'start' => $start)) . '#pid' . $post_id);
    } else {
        list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');
        $reply = pnModAPIFunc('pnForum', 'user', 'preparereply',
                              array('topic_id'   => $topic_id,
                                    'post_id'    => $post_id,
                                    'last_visit' => $last_visit,
                                    'reply_start'=> empty($message),
                                    'attach_signature' => $attach_signature,
                                    'subscribe_topic'  => $subscribe_topic));
        if($preview==true) {
            $reply['message'] = pnfVarPrepHTMLDisplay($message);
            list($reply['message_display']) = pnModCallHooks('item', 'transform', '', array($message));
            $reply['message_display'] = nl2br($reply['message_display']);
        }

        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign( 'reply', $reply);
        $pnr->assign( 'preview', $preview);
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('pnforum_user_reply.html');
    }
}

/**
 * newtopic
 *
 */
function pnForum_user_newtopic($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($forum_id,
        	 $message,
        	 $subject,
        	 $cancel,
        	 $submit,
        	 $attach_signature,
        	 $subscribe_topic,
        	 $preview) = pnVarCleanFromInput('forum',
        	  								 'message',
        									 'subject',
        									 'cancel',
        									 'submit',
        									 'attach_signature',
        									 'subscribe_topic',
        									 'preview');
    }

    $preview = (empty($preview)) ? false : true;
    $cancel  = (empty($cancel))  ? false : true;
    $submit  = (empty($submit))  ? false : true;

    //	if cancel is submitted move to forum-view
    if($cancel==true) {
        return pnRedirect(pnModURL('pnForum','user', 'viewforum', array('forum'=>$forum_id)));
    }

    $message = pnfstriptags($message);
    // check for maximum message size
    if( (strlen($message) +  strlen('[addsig]')) > 66535  ) {
        pnSessionSetVar('statusmsg', _PNFORUM_ILLEGALMESSAGESIZE);
        // switch to preview mode
        $preview = true;
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    $newtopic = pnModAPIFunc('pnForum', 'user', 'preparenewtopic',
                             array('forum_id'   => $forum_id,
                                   'subject'    => $subject,
                                   'message'    => $message,
                                   'topic_start'=> (empty($subject) && empty($message)),
                                   'attach_signature' => $attach_signature,
                                   'subscribe_topic'  => $subscribe_topic));
    if($submit==true && $preview==false) {
        // it's a submitted page
        // Confirm authorisation code
        if (!pnSecConfirmAuthKey()) {
            return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }

        //store the new topic
        $topic_id = pnModAPIFunc('pnForum', 'user', 'storenewtopic',
                                 array('forum_id'         => $forum_id,
                                       'subject'          => $subject,
                                       'message'          => $message,
                                       'attach_signature' => $attach_signature,
                                       'subscribe_topic'  => $subscribe_topic));
        if(pnModGetVar('pnForum', 'newtopicconfirmation') == 'yes') {
            $pnr =& new pnRender('pnForum');
            $pnr->caching = false;
            $pnr->add_core_data();
            $pnr->assign('topic', pnModAPIFunc('pnForum', 'user', 'readtopic', array('topic_id' => $topic_id)));
            return $pnr->fetch('pnforum_user_newtopicconfirmation.html');

        } else {
            return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
    	                        array('topic' => $topic_id)));
        }
    } else {
        // new topic
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign( 'preview', $preview);
        $pnr->assign( 'newtopic', $newtopic);
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('pnforum_user_newtopic.html');
    }
}

/**
 * editpost
 *
 */
function pnForum_user_editpost($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($post_id,
             $topic_id,
        	 $message,
        	 $subject,
        	 $submit,
        	 $delete,
        	 $attach_signature,
        	 $cancel,
        	 $preview) =  pnVarCleanFromInput('post',
        	                                  'topic',
                                              'message',
                                              'subject',
                                              'submit',
                                              'delete',
                                              'attach_signature',
                                              'cancel',
                                              'preview');
    }

    if(empty($post_id) || !is_numeric($post_id)) {
        return pnRedirect(pnModURL('pnForum', 'user', 'main'));
    }
    $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                         array('post_id'    => $post_id));
    if(!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])
       && ($post['poster_data']['pn_uid'] <> pnUserGetVar('uid')) ) {
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__);
    }

    $preview = (empty($preview)) ? false : true;

    //	if cancel is submitted move to forum-view
    if(!empty($cancel)) {
        return pnRedirect(pnModURL('pnForum','user', 'viewtopic', array('topic'=>$topic_id)));
    }

    $message = pnfstriptags($message);
    // check for maximum message size
    if( (strlen($message) +  strlen('[addsig]')) > 66535  ) {
        pnSessionSetVar('statusmsg', _PNFORUM_ILLEGALMESSAGESIZE);
        // switch to preview mode
        $preview = true;
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    if($submit && !$preview) {
        /**
         * Confirm authorisation code
         */
        if (!pnSecConfirmAuthKey()) {
            return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }
        //store the new topic
        $redirect = pnModAPIFunc('pnForum', 'user', 'updatepost',
                                 array('post_id'          => $post_id,
                                       'delete'           => $delete,
                                       'subject'          => $subject,
                                       'message'          => $message,
                                       'attach_signature' => ($attach_signature==1)));
    	return pnRedirect($redirect);

    } else {
        if(!empty($subject)) {
            $post['topic_subject'] = strip_tags($subject);
        }

        // if the current user is the original poster we allow to
        // edit the subject
        $firstpost = pnModAPIFunc('pnForum', 'user', 'get_firstlast_post_in_topic',
                                  array('topic_id' => $post['topic_id'],
                                        'first'    => true));
        if($post['poster_data']['pn_uid'] == $firstpost['poster_data']['pn_uid']) {
            $post['edit_subject'] = true;
        }

        if(!empty($message)) {
            $post['post_rawtext'] = $message;
            list($post['post_textdisplay']) = pnModCallHooks('item', 'transform', '', array(nl2br($message)));
        }
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign( 'preview', $preview);
        $pnr->assign( 'post', $post);
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('pnforum_user_editpost.html');
    }
}

/**
 * topicadmin
 *
 */
function pnForum_user_topicadmin($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        $topic_id = (int)pnVarCleanFromInput('topic');
        $post_id  = (int)pnVarCleanFromInput('post');
        $forum_id = (int)pnVarCleanFromInput('forum');  // for move
        $mode     = pnVarCleanFromInput('mode');
        $submit   = pnVarCleanFromInput('submit');
        $shadow   = pnVarCleanFromInput('createshadowtopic');
    }
    $shadow = (empty($shadow)) ? false : true;

    if(empty($topic_id) && !empty($post_id)) {
        $topic_id = pnModAPIFunc('pnForum', 'user', 'get_topicid_by_postid',
                                 array('post_id' => $post_id));
    }
    $topic = pnModAPIFunc('pnForum', 'user', 'readtopic',
                          array('topic_id' => $topic_id));
    if($topic['access_moderate']<>true) {
        return showforumerror(_PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('mode', $mode);
    $pnr->assign('topic_id', $topic_id);
    $pnr->assign('last_visit', $last_visit);
    $pnr->assign('last_visit_unix', $last_visit_unix);

    if(empty($submit)) {
        switch($mode) {
            case 'del':
            case 'delete':
                $templatename = 'pnforum_user_deletetopic.html';
                break;
            case 'move':
            case 'join':
                $pnr->assign('forums', pnModAPIFunc('pnForum', 'user', 'readuserforums'));
                $templatename = 'pnforum_user_movetopic.html';
                break;
            case 'lock':
            case 'unlock':
                $templatename = 'pnforum_user_locktopic.html';
                break;
            case 'sticky':
            case 'unsticky':
                $templatename = 'pnforum_user_stickytopic.html';
                break;
            case 'viewip':
                $pnr->assign('viewip', pnModAPIFunc('pnForum', 'user', 'get_viewip_data', array('post_id' => $post_id)));
                $templatename = 'pnforum_user_viewip.html';
                break;
            default:
                return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id)));
        }
        return $pnr->fetch($templatename);

    } else { // submit is set
    	if (!pnSecConfirmAuthKey()) {
          	return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }
        switch($mode) {
            case 'del':
            case 'delete':
                $forum_id = pnModAPIFunc('pnForum', 'user', 'deletetopic', array('topic_id'=>$topic_id));
                return pnRedirect(pnModURL('pnForum', 'user', 'viewforum', array('forum'=>$forum_id)));
                break;
            case 'move':
                pnModAPIFunc('pnForum', 'user', 'movetopic', array('topic_id' => $topic_id,
                                                                   'forum_id' => $forum_id,
                                                                   'shadow'   => $shadow ));
                break;
            case 'lock':
            case 'unlock':
                list($f_id, $c_id) = pnModAPIFunc('pnForum', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                  array('topic_id' => $topic_id));
                if(!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod',$f_id, 'forum', _PNFORUM_NOAUTH_TOMODERATE), __FILE__, __LINE__);
                }
                pnModAPIFunc('pnForum', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                break;
            case 'sticky':
            case 'unsticky':
                list($f_id, $c_id) = pnModAPIFunc('pnForum', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                  array('topic_id' => $topic_id));
                if(!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod',$f_id, 'forum', _PNFORUM_NOAUTH_TOMODERATE), __FILE__, __LINE__);
                }
                pnModAPIFunc('pnForum', 'user', 'stickyunstickytopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                break;
            case 'join':
                $to_topic_id = pnVarCleanFromInput('to_topic_id');
                pnModAPIFunc('pnForum', 'user', 'jointopics', array('from_topic_id' => $topic_id,
                                                                    'to_topic_id'   => $to_topic_id));
                return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic' => $to_topic_id)));
                break;
            default:
        }
        return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id)));
    }
}

/**
 * prefs
 *
 */
function pnForum_user_prefs($args=array())
{
    if(!pnUserLoggedIn()) {
        return pnModFunc('pnForum', 'user', 'login', array('redirect' => pnModURL('pnForum', 'user', 'prefs')));
    }

    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($act,
             $return_to,
             $topic_id,
             $forum_id,
             $user_id ) = pnVarCleanFromInput('act',
                                              'return_to',
                                              'topic',
                                              'forum',
                                              'user');
    }

    // user_id will only be used if we have admin permissions otherwise the
    // user can edit his prefs only but not others users prefs


    switch($act) {
        case 'subscribe_topic':
            $return_to = (!empty($return_to))? $return_to : 'viewtopic';
            pnModAPIFunc('pnForum', 'user', 'subscribe_topic',
                         array('topic_id' => $topic_id ));
            $params = array('topic'=>$topic_id);
            break;
        case 'unsubscribe_topic':
            $return_to = (!empty($return_to))? $return_to : 'viewtopic';
            pnModAPIFunc('pnForum', 'user', 'unsubscribe_topic',
                         array('topic_id' => $topic_id ));
            $params = array('topic'=>$topic_id);
            break;
        case 'subscribe_forum':
            $return_to = (!empty($return_to))? $return_to : 'viewforum';
            pnModAPIFunc('pnForum', 'user', 'subscribe_forum',
                         array('forum_id' => $forum_id ));
            $params = array('forum'=>$forum_id);
            break;
        case 'unsubscribe_forum':
            $return_to = (!empty($return_to))? $return_to : 'viewforum';
            pnModAPIFunc('pnForum', 'user', 'unsubscribe_forum',
                         array('forum_id' => $forum_id ));
            $params = array('forum'=>$forum_id);
            break;
        case 'add_favorite_forum':
            if(pnModGetVar('pnForum', 'favorites_enabled')=='yes') {
                $return_to = (!empty($return_to))? $return_to : 'viewforum';
                pnModAPIFunc('pnForum', 'user', 'add_favorite_forum',
                             array('forum_id' => $forum_id ));
                $params = array('forum'=>$forum_id);
            }
            break;
        case 'remove_favorite_forum':
            if(pnModGetVar('pnForum', 'favorites_enabled')=='yes') {
                $return_to = (!empty($return_to))? $return_to : 'viewforum';
                pnModAPIFunc('pnForum', 'user', 'remove_favorite_forum',
                             array('forum_id' => $forum_id ));
                $params = array('forum'=>$forum_id);
            }
            break;
        case 'change_post_order':
            $return_to = (!empty($return_to))? $return_to : 'viewtopic';
            pnModAPIFunc('pnForum', 'user', 'change_user_post_order');
            $params = array('topic'=>$topic_id);
            break;
        case 'showallforums':
        case 'showfavorites':
            if(pnModGetVar('pnForum', 'favorites_enabled')=='yes') {
                $return_to = (!empty($return_to))? $return_to : 'main';
                $favorites = pnModAPIFunc('pnForum', 'user', 'change_favorite_status');
                $params = array();
            }
            break;
        default:
            list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');
            $pnr =& new pnRender('pnForum');
            $pnr->caching = false;
            $pnr->add_core_data();
            $pnr->assign('last_visit', $last_visit);
            $pnr->assign('favorites_enabled', pnModGetVar('pnForum', 'favorites_enabled'));
            $pnr->assign('last_visit_unix', $last_visit_unix);
            $pnr->assign('post_order', strtolower(pnModAPIFunc('pnForum','user','get_user_post_order')));
            $pnr->assign('tree', pnModAPIFunc('pnForum', 'user', 'readcategorytree', array('last_visit' => $last_visit )));
            return $pnr->fetch('pnforum_user_prefs.html');
    }
    return pnRedirect(pnModURL('pnForum', 'user', $return_to, $params));
}

/**
 * emailtopic
 *
 */
function pnForum_user_emailtopic($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        $topic_id = (int)pnVarCleanFromInput('topic');
        $message = pnVarCleanFromInput('message');
        $emailsubject = pnVarCleanFromInput('emailsubject');
        $sendto_email = pnVarCleanFromInput('sendto_email');
        $submit = pnVarCleanFromInput('submit');
    }

    if(!pnUserLoggedIn()) {
        return showforumerror(_PNFORUM_NOTLOGGEDIN, __FILE__, __LINE__);
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    if(!empty($submit)) {
	    if (!pnVarValidate($sendto_email, 'email')) {
	    	// Empty e-mail is checked here too
        	$error_msg = pnVarPrepForDisplay(_PNFORUM_MAILTO_WRONGEMAIL);
        	$sendto_email = '';
        	unset($submit);
	    } else if ($message == '') {
        	$error_msg = pnVarPrepForDisplay(_PNFORUM_MAILTO_NOBODY);
        	unset($submit);
	    } else if ($emailsubject == '') {
        	$error_msg = pnVarPrepForDisplay(_PNFORUM_MAILTO_NOSUBJECT);
        	unset($submit);
	    }
    }

//    $topic = pnModAPIFunc('pnForum', 'user', 'prepareemailtopic',
//                          array('topic_id'   => $topic_id));

    if(!empty($submit)) {
        if (!pnSecConfirmAuthKey()) {
            return showforumerror(_PNFORUM_BADAUTHKEY, __FILE__, __LINE__);
        }

        pnModAPIFunc('pnForum', 'user', 'emailtopic',
                     array('sendto_email' => $sendto_email,
                           'message'      => $message,
                           'subject'      => $emailsubject));
        return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic' => $topic_id)));
    } else {
        $topic = pnModAPIFunc('pnForum', 'user', 'prepareemailtopic',
                              array('topic_id'   => $topic_id));
        $emailsubject = (!empty($emailsubject)) ? $emailsubject : $topic['topic_subject'];
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('topic', $topic);
        $pnr->assign('error_msg', $error_msg);
        $pnr->assign('sendto_email', $sendto_email);
        $pnr->assign('emailsubject', $emailsubject);
        $pnr->assign('message', pnVarPrepForDisplay(_PNFORUM_EMAILTOPICMSG) ."\n\n" . pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id)));
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('pnforum_user_emailtopic.html');
    }
}

/**
 * latest
 *
 */
function pnForum_user_viewlatest($args=array())
{
    if(useragent_is_bot() == true) {
        return pnRedirect(pnModURL('pnForum', 'user', 'main'));
    }

    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($selorder, $nohours, $unanswered) = pnVarCleanFromInput('selorder', 'nohours', 'unanswered');
    }

    if(empty($selorder) || !is_numeric($selorder)) {
    	$selorder = 1;
    }
    if(!empty($nohours) && !is_numeric($nohours)) {
    	unset($nohours);
    }
    // maximum two weeks back = 2 * 24 * 7 hours
    if(isset($nohours) && $nohours>336) {
        $nohours = 336;
    }
    if(empty($unanswered) || !is_numeric($unanswered)) {
    	$unanswered = 0;
    }
    if(!empty($nohours)) {
    	$selorder = 5;
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    list($posts, $m2fposts, $rssposts, $text) = pnModAPIFunc('pnForum', 'user', 'get_latest_posts',
                                                             array('selorder'   => $selorder,
                                                                   'nohours'    => $nohours,
                                                                   'unanswered' => $unanswered,
                                                                   'last_visit' => $last_visit,
                                                                   'last_visit_unix' => $last_visit_unix));

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('posts', $posts);
    $pnr->assign('m2fposts', $m2fposts);
    $pnr->assign('rssposts', $rssposts);
    $pnr->assign('text', $text);
    $pnr->assign('nohours', $nohours);
    $pnr->assign('last_visit', $last_visit);
    $pnr->assign('last_visit_unix', $last_visit_unix);
    $pnr->assign('numposts', pnModAPIFunc('pnForum', 'user', 'boardstats',
                                            array('id'   => '0',
                                                  'type' => 'all' )));
    return $pnr->fetch('pnforum_user_latestposts.html');

}

/**
 * splittopic
 *
 */
function pnForum_user_splittopic($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($post_id,
             $submit,
             $newsubject) = pnVarCleanFromInput('post',
                                                'submit',
                                                'newsubject');
    }

    $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                         array('post_id' => $post_id));

    if(!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod',$post['forum_id'], 'forum', _PNFORUM_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    if(!empty($submit)) {
        // Confirm authorisation code
        if (!pnSecConfirmAuthKey()) {
            return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }
        // submit is set, we split the topic now
        $post['topic_subject'] = $newsubject;
        $newtopic_id = pnModAPIFunc('pnForum', 'user', 'splittopic',
                                   array('post' => $post));
        return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
                                   array('topic' => $newtopic_id)));

    } else {
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('post', $post);
        return $pnr->fetch('pnforum_user_splittopic.html');
    }
}

/**
 * print
 * prepare print view of the selected posting or topic
 *
 */
function pnForum_user_print($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        $post_id  = (int)pnVarCleanFromInput('post');
        $topic_id = (int)pnVarCleanFromInput('topic');
    }

    if(useragent_is_bot() == true) {
        if($post_id <> 0 ) {
            $topic_id =pnModAPIFunc('pnForum', 'user', 'get_topicid_by_postid',
                                    array('post_id' => $post_id));
        }
        if(($topic_id <> 0) && ($topic_id<>false)) {
            return pnForum_user_viewtopic(array('topic' => $topic_id,
                                                'start'   => 0));
        } else {
            return pnRedirect(pnModURL('pnForum', 'user', 'main'));
        }
    } else {
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        if($post_id<>0) {
            $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                                 array('post_id' => $post_id));
            $pnr->assign('post', $post);
            $output = $pnr->fetch('pnforum_user_printpost.html');
        } elseif($topic_id<>0) {
            $topic = pnModAPIFunc('pnForum', 'user', 'readtopic',
                                 array('topic_id'  => $topic_id,
                                       'complete' => true ));
            $pnr->assign('topic', $topic);
            $output = $pnr->fetch('pnforum_user_printtopic.html');
        } else {
            return pnRedirect(pnModURL('pnForum', 'user', 'main'));
        }
        $lang = pnConfigGetVar('backend_language');
        echo "<?xml version=\"1.0\" encoding=\"iso-8859-15\"?>\n";
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
        echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"$lang\" xml:lang=\"$lang\">\n";
        echo "<head>\n";
        echo "<title>" . $topic['topic_title'] . "</title>\n";
        echo "<link rel=\"StyleSheet\" href=\"themes/" . pnUserGetTheme() . "/style/style.css\" type=\"text/css\" />\n";
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=". pnModGetVar('pnForum', 'default_lang') ."\" />\n";

        global $additional_header;
        if (is_array($additional_header))
        {
          foreach ($additional_header as $header)
            echo "$header\n";
        }
        echo "</head>\n";
        echo "<body class=\"printbody\">\n";
        echo $output;
        echo "</body>\n";
        echo "</html>\n";
        exit;
    }
}

/**
 * search
 * internal search function
 *
 */
function pnForum_user_search($args=array())
{
    $submit = pnVarCleanFromInput('submit');
    if(!$submit) {
        return pnModAPIFunc('pnForum', 'search', 'internalsearchoptions');
    } else {
        return pnModAPIFunc('pnForum', 'search', 'search');
    }
}

/**
 * movepost
 * Move a single post to another thread
 * added by by el_cuervo -- dev-postnuke.com
 *
 */
function pnForum_user_movepost($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($post_id,
             $submit,
			 $to_topic) = pnVarCleanFromInput('post',
                                               'submit',
											   'to_topic');
    }
    $post = pnModAPIFunc('pnForum', 'user', 'readpost', array('post_id' => $post_id));

    if(!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod', $post['forum_id'], 'forum', _PNFORUM_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    if(!empty($submit)) {
        if (!pnSecConfirmAuthKey()) {
            return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }
        // submit is set, we move the posting now
		// Existe el Topic ? --- Exists new Topic ?
		$topic = pnModAPIFunc('pnForum', 'user', 'readtopic', array('topic_id' => $to_topic,
		                                                            'complete' => false));
        $post['new_topic'] = $to_topic;
		$post['old_topic'] = $topic['topic_id'];
        $start = pnModAPIFunc('pnForum', 'user', 'movepost', array('post'     => $post,
                                                                   'to_topic' => $to_topic));
        return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
                                   array('topic' => $to_topic,
                                         'start' => $start)) . '#pid' . $post['post_id']);
    } else {
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('post', $post);
        return $pnr->fetch('pnforum_user_movepost.html');
    }
}

/**
 * jointopics
 * Join a topic with another toipic                                                                                                  ?>
 * by el_cuervo -- dev-postnuke.com
 *
 */
function pnForum_user_jointopics($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($post_id,
             $submit,
             $to_topic_id,
			 $from_topic_id) = pnVarCleanFromInput('post_id',
                                                   'submit',
                                                   'to_topic_id',
											       'from_topic_id');
    }

    $post = pnModAPIFunc('pnForum', 'user', 'readpost', array('post_id' => $post_id));

    if(!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod',$post['forum_id'], 'forum', _PNFORUM_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    if(!$submit) {
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('post', $post);
        return $pnr->fetch('pnforum_user_jointopics.html');
    } else {
    	if (!pnSecConfirmAuthKey()) {
          	return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }

		// check if from_topic exists. this function will return an error if not
		$from_topic = pnModAPIFunc('pnForum', 'user', 'readtopic', array('topic_id' => $from_topic_id, 'complete' => false));
		// check if to_topic exists. this function will return an error if not
		$to_topic = pnModAPIFunc('pnForum', 'user', 'readtopic', array('topic_id' => $to_topic_id, 'complete' => false));
        // submit is set, we split the topic now
        //$post['new_topic'] = $totopic;
		//$post['old_topic'] = $old_topic;
        $res = pnModAPIFunc('pnForum', 'user', 'jointopics', array('from_topic' => $from_topic,
                                                                   'to_topic'   => $to_topic));
        return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic' => $res)));
    }
}

/**
 * moderateforum
 * simple moderation of multiple topics
 *
 *@params to be documented :-)
 *
 */
function pnForum_user_moderateforum($args=array())
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        $forum_id = (int)pnVarCleanFromInput('forum');
        $start    = (int)pnVarCleanFromInput('start');
        $mode     = pnVarCleanFromInput('mode');
        $submit   = pnVarCleanFromInput('submit');
		$topic_ids= pnVarCleanFromInput('topic_id');
        $shadow   = pnVarCleanFromInput('createshadowtopic');
        $moveto   = pnVarCleanFromInput('moveto');
        $jointo   = pnVarCleanFromInput('jointo');
    }
    $shadow = (empty($shadow)) ? false : true;

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    // Get the Forum for Display and Permission-Check
    $forum = pnModAPIFunc('pnForum', 'user', 'readforum',
                          array('forum_id'        => $forum_id,
                                'start'           => $start,
                                'last_visit'      => $last_visit,
                                'last_visit_unix' => $last_visit_unix));

	if(!allowedtomoderatecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod',$post['forum_id'], 'forum', _PNFORUM_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }


    // Submit isn't set'
    if(empty($submit)) {
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('forum_id', $forum_id);
        $pnr->assign('mode',$mode);
        $pnr->assign('topic_ids', $topic_ids);
        $pnr->assign('last_visit', $last_visit);
        $pnr->assign('last_visit_unix', $last_visit_unix);
        $pnr->assign('forum',$forum);
        // For Movetopic
        $pnr->assign('forums', pnModAPIFunc('pnForum', 'user', 'readuserforums'));
        return $pnr->fetch('pnforum_user_moderateforum.html');

    } else {
        // submit is set
    	if (!pnSecConfirmAuthKey()) {
          	return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }
        if(count($topic_ids)<>0) {
    	    switch($mode) {
                case 'del':
                case 'delete':
                	foreach($topic_ids as $topic_id) {
                    	$forum_id = pnModAPIFunc('pnForum', 'user', 'deletetopic', array('topic_id'=>$topic_id));
                	}
                    break;
                case 'move':
                	if(empty($moveto)) {
                		return showforumerror(_PNFORUM_NOMOVETO, __FILE__, __LINE__);
                	}
                	foreach ($topic_ids as $topic_id) {
                    	pnModAPIFunc('pnForum', 'user', 'movetopic', array('topic_id' => $topic_id,
                        	                                               'forum_id' => $moveto,
                            	                                           'shadow'   => $shadow ));
                	}
                    break;
                case 'lock':
                case 'unlock':
                	foreach($topic_ids as $topic_id) {
                    	pnModAPIFunc('pnForum', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                	}
                    break;
                case 'sticky':
                case 'unsticky':
                	foreach($topic_ids as $topic_id) {
                    	pnModAPIFunc('pnForum', 'user', 'stickyunstickytopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                	}
                    break;
                case 'join':
                    if(empty($jointo)) {
                        return showforumerror(_PNFORUM_NOJOINTO, __FILE__, __LINE__);
                    }
                    if(in_array($jointo, $topic_ids)) {
                        // jointo, the target topic, is part of the topics to join
                        // we remove this to avoid a loop
                        $fliparray = array_flip($topic_ids);
                        unset($fliparray[$jointo]);
                        $topic_ids = array_flip($fliparray);
                    }
                	foreach($topic_ids as $to_topic_id) {
                        pnModAPIFunc('pnForum', 'user', 'jointopics', array('from_topic_id' => $topic_id,
                                                                            'to_topic_id'   => $jointo));
                    }
                    break;
                default:
            }
            // Refresh Forum Info
            $forum = pnModAPIFunc('pnForum', 'user', 'readforum',
                              array('forum_id'        => $forum_id,
                                    'start'           => $start,
                                    'last_visit'      => $last_visit,
                                    'last_visit_unix' => $last_visit_unix));
        }
    }
    return pnRedirect(pnModURL('pnForum', 'user', 'moderateforum', array('forum' => $forum_id)));
}

/**
 * report
 * notify a moderator about a posting
 *
 *@params $post int post_id
 *@params $comment string comment of reporter
 *
 */
function pnForum_user_report($args)
{
    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($post_id,
             $comment,
             $submit) = pnVarCleanFromInput('post',
                                            'comment',
                                            'submit');
    }

    $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                         array('post_id' => $post_id));

    // some spam checks:
    // - remove html and compare with original comment
    // - use censor and compare with original omment
    // if only one of this comparisons fails -> trash it, its spam.
    if(!pnUserLoggedIn()) {
        if((strip_tags($comment) <> $comment) ||
           (pnVarCensor($comment) <> $comment)) {
            // possibly spam, stop now
            // get the users ip address and store it in pnTemp/pnForum_spammers.txt
            pnf_blacklist();
            // set 403 header and stop
            header('HTTP/1.0 403 Forbidden');
            exit;
        }
    }
    
    if(!$submit) {
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('post', $post);
        return $pnr->fetch('pnforum_user_notifymod.html');
    } else {   // submit is set
    	if (!pnSecConfirmAuthKey()) {
          	return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }
        pnModAPIFunc('pnForum', 'user', 'notify_moderator',
                     array('post'    => $post,
                           'comment' => $comment));
        $start = pnModAPIFunc('pnForum', 'user', 'get_page_from_topic_replies',
                              array('topic_replies' => $post['topic_replies']));
        return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
                                   array('topic' => $post['topic_id'],
                                         'start' => $start)));
    }

}

/**
 * topicsubscriptions
 * manage the users topic subscription
 *
 *@params
 *
 */
function pnForum_user_topicsubscriptions($args)
{
    if(!pnUserLoggedIn()) {
        return pnModFunc('pnForum', 'user', 'login', array('redirect' => pnModURL('pnForum', 'user', 'prefs')));
    }

    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($topic_id,
             $submit) = pnVarCleanFromInput('topic_id',
                                            'submit');
    }

    if(!$submit) {
        $subscriptions = pnModAPIFunc('pnForum', 'user', 'get_topic_subscriptions');
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('subscriptions', $subscriptions);
        return $pnr->fetch('pnforum_user_topicsubscriptions.html');
    } else {  // submit is set
        if(!pnSecConfirmAuthKey()) {
            return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }
        if(is_array($topic_id) && (count($topic_id) > 0)) {
            for($i=0; $i<count($topic_id); $i++) {
                pnModAPIFunc('pnForum', 'user', 'unsubscribe_topic', array('topic_id' => $topic_id[$i]));
            }
        }
        return pnRedirect(pnModURL('pnForum', 'user', 'topicsubscriptions'));
    }
}

/**
 * login
 *
 */
function pnForum_user_login($args)
{
    if(pnUserLoggedIn()) {
        return pnRedirect(pnModURL('pnForum', 'user', 'main'));
    }

    // get the input
    if(count($args)>0) {
        extract($args);
        unset($args);
    } else {
        list($submit,
             $uname,
             $pass,
             $rememberme,
             $redirect) = pnVarCleanFromInput('submit',
                                              'uname',
                                              'pass',
                                              'rememberme',
                                              'redirect');
    }

    if(empty($redirect)) {
        $redirect = pnModURL('pnForum', 'user', 'main');
    }

    if(!$submit) {
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data('PNConfig');
        $pnr->assign('redirect', $redirect);
        return $pnr->fetch('pnforum_user_login.html');
    } else { // submit is set
        // login
        if(pnUserLogin($uname, $pass, $rememberme) == false) {
            return showforumerror(_PNFORUM_ERRORLOGGINGIN, __FILE__, __LINE__);
        }
        return pnRedirect($redirect);
    }

}

?>