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

include_once("modules/pnForum/common.php");

/**
 * main
 * show all categories and forums a user may see
 *
 *@params 'viewcat' int only expand the category, all others shall be hidden / collapsed
 */
function pnForum_user_main()
{
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 

    $viewcat = (int)pnVarCleanFromInput('viewcat');
    $viewcat = (!empty($viewcat)) ? $viewcat : -1;

    $loggedIn = pnUserLoggedIn();

    $favorites = (bool)pnVarCleanFromInput('favorites');
    if($loggedIn && empty($favorites)) {
        $favorites = pnModAPIFunc('pnForum', 'user', 'get_favorite_status');
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');
    if ($loggedIn && $favorites) {
        $tree = pnModAPIFunc('pnForum', 'user', 'getFavorites', array('user_id' => (int)pnUserGetVar('uid'),
                                                                      'last_visit' => $last_visit ));
    } else {
        $tree = pnModAPIFunc('pnForum', 'user', 'readcategorytree', array('last_visit' => $last_visit ));

        // this needs to be in here because we want to display the favorites
        // not go to it if there is only one
        // check if we have one category and one forum only
        if(count($tree)==1) {
            foreach($tree as $catname=>$forumarray) {
                if(count($forumarray['forums'])==1) {  
                    pnRedirect(pnModURL('pnForum', 'user', 'viewforum', array('forum'=>$forumarray['forums'][0]['forum_id'])));  
                    return true;
                }
            }
        }   
    }
    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->assign( 'favorites', $favorites);
    $pnr->assign( 'tree', $tree);
    $pnr->assign( 'view_category', $viewcat);
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    $pnr->assign( 'loggedin', $loggedIn);
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
function pnForum_user_viewforum()
{
    $forum_id = (int)pnVarCleanFromInput('forum');
    $start    = (int)pnVarCleanFromInput('start');

    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    $forum = pnModAPIFunc('pnForum', 'user', 'readforum',
                          array('forum_id'   => $forum_id,
                                'start'      => $start,
                                'last_visit' => $last_visit));

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->assign( 'forum', $forum);
    $pnr->assign( 'hot_threshold', pnModGetVar('pnForum', 'hot_threshold'));
    $pnr->assign( 'loggedin', pnUserLoggedIn());
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    return $pnr->fetch('pnforum_user_viewforum.html');    
}

/**
 * viewtopic
 *
 */
function pnForum_user_viewtopic()
{
    $topic_id = (int)pnVarCleanFromInput('topic');
    $start    = (int)pnVarCleanFromInput('start');
    $view     = strtolower(pnVarCleanFromInput('view'));
    $highlight= urldecode(pnVarCleanFromInput('highlight'));
    if(!empty($highlight)) {
        pnSessionSetVar('highlight', pnVarPrepForStore($highlight));
    }
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 
    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');
    
    if(!empty($view) && ($view=="next" || $view=="previous")) {
        $topic_id = pnModAPIFunc('pnForum', 'user', 'get_previous_or_next_topic_id',
                                 array('topic_id' => $topic_id,
                                       'view'     => $view));
        pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', 
                            array('topic' => $topic_id)));
        return true;
    }
    $topic = pnModAPIFunc('pnForum', 'user', 'readtopic',
                          array('topic_id'   => $topic_id,
                                'start'      => $start,
                                'last_visit' => $last_visit));

    pnSessionDelVar('highlight');
    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->assign( 'topic', $topic);
    $pnr->assign( 'hot_threshold', pnModGetVar('pnForum', 'hot_threshold'));
    $pnr->assign( 'loggedin', pnUserLoggedIn());
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    return $pnr->fetch('pnforum_user_viewtopic.html');    
    
}

/**
 * reply
 *
 */
function pnForum_user_reply()
{
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 
   
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

    $post_id = (int)$post_id;
    $topic_id = (int)$topic_id;
    $attach_signature = (int)$attach_signature;
    $subscribe_topic = (int)$subscribe_topic;
        
    /**
     * if cancel is submitted move to forum-view
     */
    if(!empty($cancel)) {
    	pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=> $topic_id)));
    }
    
    $preview = (empty($preview)) ? false : true;
    
    if (empty($submit)) {
        $submit = false;
    	$subject="";
    	$message="";    
    } else {
        $submit = true;
    }

    if ($submit==true && $preview==false) {
        // Confirm authorisation code
        if (!pnSecConfirmAuthKey()) {
            return showforumerror(_BADAUTHKEY, __FILE__, __LINE__);
        }

        // sync the users, so that new pn users get into the pnForum
        // database
        pnModAPIFunc('pnForum', 'user', 'usersync');

        $start = pnModAPIFunc('pnForum', 'user', 'storereply',
                              array('topic_id'         => $topic_id,
                                    'message'          => $message,
                                    'attach_signature' => $attach_signature,
                                    'subscribe_topic'  => $subscribe_topic));
        pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
                            array('topic' => $topic_id,
                                  'start' => $start)));
    } else {
        list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');
        
        $reply = pnModAPIFunc('pnForum', 'user', 'preparereply',
                              array('topic_id'   => $topic_id,
                                    'post_id'    => $post_id,
                                    'last_visit' => $last_visit));
        if($preview==true) {
            $reply['message'] = $message;
        }
        
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->assign( 'reply', $reply);
        $pnr->assign( 'preview', $preview);
        $pnr->assign( 'loggedin', pnUserLoggedIn());
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('pnforum_user_reply.html'); 
    }   
}

/**
 * newtopic
 *
 */
function pnForum_user_newtopic()
{
    list($forum_id, 
    	 $message, 
    	 $subject, 
    	 $cancel,
    	 $submit,
    	 $preview) = pnVarCleanFromInput('forum', 
    	  								 'message', 
    									 'subject', 
    									 'cancel',
    									 'submit',
    									 'preview');
    
    //	if cancel is submitted move to forum-view
    if(!empty($cancel)) {
        pnRedirect(pnModURL('pnForum','user', 'viewforum', array('forum'=>$forum_id)));
        return true;
    }

    if (empty($preview)) {
    	unset($preview);
    }
    
    if (empty($submit)) {
    	$subject="";
    	$message="";    
    }
    
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 

    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');
    $newtopic = pnModAPIFunc('pnForum', 'user', 'preparenewtopic',
                             array('forum_id'   => $forum_id,
                                   'subject'    => $subject,
                                   'message'    => $message ));

    if(isset($submit) && !isset($preview)) {
        // sync the users, so that new pn users get into the pnForum
        // database
        pnModAPIFunc('pnForum', 'user', 'usersync');

        //store the new topic
        $topic_id = pnModAPIFunc('pnForum', 'user', 'storenewtopic',
                                 array('forum_id' => $forum_id,
                                       'subject'  => $subject,
                                       'message'  => $message));
    	pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
    	                    array('topic' => pnVarPrepForStore($topic_id))));
    	return true;

    } else {
        // new topic
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->assign( 'preview', (isset($preview)) ? true : false); 
        $pnr->assign( 'newtopic', $newtopic);
        $pnr->assign( 'loggedin', pnUserLoggedIn());
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('pnforum_user_newtopic.html');    
    }
}

/**
 * editpost
 *
 */
function pnForum_user_editpost()
{
    list($post_id, 
    	 $message, 
    	 $subject,
    	 $submit,
    	 $delete,
    	 $preview) =  pnVarCleanFromInput('post', 
                                          'message', 
                                          'subject',
                                          'submit',
                                          'delete',
                                          'preview');
        
    //	if cancel is submitted move to forum-view
    if(!empty($cancel)) {
        pnRedirect(pnModURL('pnForum','user', 'viewtopic', array('topic'=>$topic_id)));
        return true;
    }

    if (empty($preview)) {
    	unset($preview);
    }
    
    if (empty($submit)) {
    	$subject="";
    	$message="";    
    }
    
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 
    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    if(isset($submit) && !isset($preview)) {
        //store the new topic

        $redirect = pnModAPIFunc('pnForum', 'user', 'updatepost',
                                 array('post_id'  => $post_id,
                                       'delete'   => $delete,
                                       'subject'  => $subject,
                                       'message'  => $message));
    	pnRedirect($redirect);
    	return true;

    } else {
        $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                             array('post_id'    => $post_id));
        if(!empty($subject)) {
            $post['topic_subject'] = $subject;
        }
        if(!empty($message)) {
            $post['post_text'] = $message;
            list($post['post_textdisplay']) = pnModCallHooks('item', 'transform', '', array($message));
        }
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->assign( 'preview', (isset($preview)) ? true : false); 
        $pnr->assign( 'post', $post);
        $pnr->assign( 'loggedin', pnUserLoggedIn());
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('pnforum_user_editpost.html');    
    }
}

/**
 * topicadmin
 *
 */
function pnForum_user_topicadmin()
{
    $topic_id = (int)pnVarCleanFromInput('topic');
    $post_id  = (int)pnVarCleanFromInput('post');
    $forum_id = (int)pnVarCleanFromInput('forum');  // for move
    $mode     = pnVarCleanFromInput('mode');
    $submit   = pnVarCleanFromInput('submit');
    $shadow   = pnVarCleanFromInput('createshadowtopic');
    $shadow = (empty($shadow)) ? false : true;

    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->assign('mode', $mode);
    $pnr->assign('topic_id', $topic_id);
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);

    if(empty($submit)) {
        switch($mode) {
            case "del":
                $templatename = "pnforum_user_deletetopic.html";
                break;
            case "move":
                $pnr->assign('forums', pnModAPIFunc('pnForum', 'user', 'readuserforums'));
                $templatename = "pnforum_user_movetopic.html";
                break;
            case "lock":
            case "unlock":
                $templatename = "pnforum_user_locktopic.html";
                break;
            case "sticky":
            case "unsticky":
                $templatename = "pnforum_user_stickytopic.html";
                break;
            case "viewip":
                $pnr->assign('viewip', pnModAPIFunc('pnForum', 'user', 'get_viewip_data', array('post_id' => $post_id)));
                $templatename = "pnforum_user_viewip.html";
                break;
            default:
                pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id)));
                return true;
        }
        return $pnr->fetch($templatename);
            
    } else { // submit is set
        switch($mode) {
            case "del":
                $forum_id = pnModAPIFunc('pnForum', 'user', 'deletetopic', array('topic_id'=>$topic_id));
                pnRedirect(pnModURL('pnForum', 'user', 'viewforum', array('forum'=>$forum_id)));
                return true;
                break;
            case "move":
                pnModAPIFunc('pnForum', 'user', 'movetopic', array('topic_id' => $topic_id,
                                                                   'forum_id' => $forum_id,
                                                                   'shadow'   => $shadow ));
                break;
            case "lock":
            case "unlock":
                pnModAPIFunc('pnForum', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                break;
            case "sticky":
            case "unsticky":
                pnModAPIFunc('pnForum', 'user', 'stickyunstickytopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                break;
            default:
        }
        pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id)));
        return true;
    }
}

/**
 * prefs
 *
 */
function pnForum_user_prefs()
{
    $loggedin = pnUserLoggedIn();
    if(!$loggedin) {
        pnRedirect(pnModURL('pnForum', 'user', 'main'));
        return true;
    }
    
    $act = pnVarCleanFromInput('act');
    $return_to = pnVarCleanFromInput('return_to');
    $topic_id = (int)pnVarCleanFromInput('topic');
    $forum_id = (int)pnVarCleanFromInput('forum');

    $return_to = (!empty($return_to))? $return_to : "viewforum";
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 

    switch($act) {
        case 'subscribe_topic':
            pnModAPIFunc('pnForum', 'user', 'subscribe_topic',
                         array('topic_id' => $topic_id ));
            pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id)));
            break;
        case 'unsubscribe_topic':
            pnModAPIFunc('pnForum', 'user', 'unsubscribe_topic',
                         array('topic_id' => $topic_id ));
            pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id)));
            break;
        case 'subscribe_forum':
            pnModAPIFunc('pnForum', 'user', 'subscribe_forum',
                         array('forum_id' => $forum_id ));
            pnRedirect(pnModURL('pnForum', 'user', $return_to, array('forum'=>$forum_id)));
            break;
        case 'unsubscribe_forum':
            pnModAPIFunc('pnForum', 'user', 'unsubscribe_forum',
                         array('forum_id' => $forum_id ));
            pnRedirect(pnModURL('pnForum', 'user', $return_to, array('forum'=>$forum_id)));
            break;
        case 'add_favorite_forum':
            pnModAPIFunc('pnForum', 'user', 'add_favorite_forum',
                         array('forum_id' => $forum_id ));
            pnRedirect(pnModURL('pnForum', 'user', $return_to, array('forum'=>$forum_id)));
            break;
        case 'remove_favorite_forum':
            pnModAPIFunc('pnForum', 'user', 'remove_favorite_forum',
                         array('forum_id' => $forum_id ));
            pnRedirect(pnModURL('pnForum', 'user', $return_to, array('forum'=>$forum_id)));
            break;
        case 'change_post_order':
            pnModAPIFunc('pnForum', 'user', 'change_user_post_order');
            pnRedirect(pnModURL('pnForum', 'user', $return_to, array('topic'=>$topic_id)));
            break;
        default:
            list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');
            $pnr =& new pnRender('pnForum');
            $pnr->caching = false;
            $pnr->assign( 'last_visit', $last_visit);
            $pnr->assign( 'loggedin', $loggedin);
            $pnr->assign( 'last_visit_unix', $last_visit_unix);
            $pnr->assign('tree', pnModAPIFunc('pnForum', 'user', 'readcategorytree', array('last_visit' => $last_visit )));
            return $pnr->fetch('pnforum_user_prefs.html');
    }
    return true;
}

/**
 * emailtopic
 *
 */
function pnForum_user_emailtopic()
{
    $topic_id = (int)pnVarCleanFromInput('topic');
    $message = pnVarCleanFromInput('message');
    $sendto_email = pnVarCleanFromInput('sendto_email');
    $submit = pnVarCleanFromInput('submit');

    if(!pnUserLoggedIn()) {
        return showforumerror(_PNFORUM_NOTLOGGEDIN, __FILE__, __LINE__);
    }
    
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 
    
    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    if(!empty($submit)) {
	    if (!pnVarValidate($sendto_email, 'email')) {
	    	// Empty e-mail is checked here too
        	$error_msg = true;
        	$sendto_email = "";
        	unset($submit);
	    } else if ($message == "") {
        	$error_msg = true;
        	unset($submit);
	    }
    }

    $topic = pnModAPIFunc('pnForum', 'user', 'prepareemailtopic',
                          array('topic_id'   => $topic_id));
	
    if(!empty($submit)) {
        pnModAPIFunc('pnForum', 'user', 'emailtopic',
                     array('sendto_email' => $sendto_email,
                           'message'      => $message,
                           'topic_subject'=> $topic['topic_subject']));
        pnRedirect(pnModURL('pnForum', 'user', 'viewtopic', array('topic' => $topic_id)));
        return true;
    } else {
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->assign('topic', $topic);
        $pnr->assign('error_msg', $error_msg);
        $pnr->assign('sendto_email', $sendto_email);
        $pnr->assign('message', "".pnVarPrepForDisplay(_PNFORUM_EMAILTOPICMSG) ."\n\n" . pnModURL('pnForum', 'user', 'viewtopic', array('topic'=>$topic_id)));
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('pnforum_user_emailtopic.html');
    }
}

/**
 * latest
 *
 */
function pnForum_user_viewlatest()
{
    list($selorder, $nohours, $unanswered) = pnVarCleanFromInput('selorder', 'nohours', 'unanswered');
    
    if (!isset($selorder) || !is_numeric($selorder)) {
    	$selorder = 1;
    }
    if (isset($nohours) && !is_numeric($nohours)) {
    	unset($nohours);
    }
    if (!isset($unanswered) || !is_numeric($unanswered)) {
    	$unanswered = 0;
    }
    if (isset($nohours)) {
    	$selorder = 5;
    }

    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 
    
    list($last_visit, $last_visit_unix) = pnModAPIFunc('pnForum', 'user', 'setcookies');

    list($posts, $text) = pnModAPIFunc('pnForum', 'user', 'get_latest_posts',
                                       array('selorder'   => $selorder,
                                             'nohours'    => $nohours,
                                             'unanswered' => $unanswered,
                                             'last_visit' => $last_visit));

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->assign('posts', $posts);
    $pnr->assign('text', $text);
    $pnr->assign('nohours', $nohours);
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'loggedin', pnUserLoggedIn());
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    $pnr->assign( 'numposts', pnModAPIFunc('pnForum', 'user', 'boardstats',
                                            array('id'   => '0',
                                                  'type' => 'all' )));
    return $pnr->fetch('pnforum_user_latestposts.html');

}

/**
 * splittopic
 *
 */
function pnForum_user_splittopic()
{
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 

    list($post_id,
         $submit, 
         $newsubject) = pnVarCleanFromInput('post',
                                            'submit', 
                                            'newsubject');
    $post = pnModAPIFunc('pnForum', 'user', 'readpost',
                         array('post_id' => $post_id));

    if(!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror( _PNFORUM_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }

    if(!empty($submit)) {
        // submit is set, we split the topic now
        $post['topic_subject'] = $newsubject;
        $newtopic_id = pnModAPIFunc('pnForum', 'user', 'splittopic',
                                   array('post' => $post));
        pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
                            array('topic' => $newtopic_id)));
        
    } else {
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->assign('post', $post);
        return $pnr->fetch('pnforum_user_splittopic.html');
    } 
}

/**
 * print
 * prepare print view of the selectd posting or topic
 *
 */
function pnForum_user_print()
{
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 
    
    $post_id  = (int)pnVarCleanFromInput('post');
    $topic_id = (int)pnVarCleanFromInput('topic');
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
        pnRedirect(pnModURL('pnForum', 'user', 'main'));
        return true;
    }        
    echo "<html>\n";
    echo "<head>\n";
    echo "<link rel=\"StyleSheet\" href=\"themes/" . pnUserGetTheme() . "/style/style.css\" type=\"text/css\" />\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=". pnModGetVar('pnForum', 'default_lang') ."\">\n";
    
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

/**
 * search
 * internal search function
 *
 */
function pnForum_user_search()
{
    $submit = pnVarCleanFromInput('submit');
    
    if(!$submit) {
        if(!pnModAPILoad('pnForum', 'admin')) {
            return showforumerror("loading adminapi failed", __FILE__, __LINE__);
        } 
        $forums = pnModAPIFunc('pnForum', 'admin', 'readforums');
    
        $pnr =& new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->assign('forums', $forums);
        return $pnr->fetch('pnforum_user_search.html');
    } else {   // submit is set
        if(!pnModAPILoad('pnForum', 'user')) {
            return showforumerror("loading userapi failed", __FILE__, __LINE__);
        } 

        list($vars['searchfor'],
             $vars['searchbool'],
             $vars['searchauthor'],
             $vars['searchforums'],
             $vars['searchorder'],
             $vars['searchlimit'],
             $vars['searchstart'] ) = pnVarCleanFromInput('searchfor',
                                                          'searchbool',
                                                          'searchauthor',
                                                          'searchforums',
                                                          'searchorder',
                                                          'searchlimit',
                                                          'searchstart');

        if( !is_array($vars['searchforums']) || !is_array($vars['searchorder']) || ($vars['searchbool']<>"AND" && $vars['searchbool']<>"OR") ) {
            return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
        }
        
        list($searchresults,
             $total_hits ) = pnModAPIFunc('pnForum', 'user', 'forumsearch',
                                          array('searchfor' => $vars['searchfor'],
                                                'bool'      => $vars['searchbool'],
                                                'forums'    => $vars['searchforums'],
                                                'author'    => $vars['searchauthor'],
                                                'order'     => $vars['searchorder'],
                                                'limit'     => $vars['searchlimit'],
                                                'startnum'  => $vars['searchstart']));
    
        $pnr =& new pnRender('pnForum');
        $urltemplate = pnModURL('pnForum', 'user', 'search', 
                                array('searchagain'  => 1,
                                      'searchstart'  => '%%'));
        $pnr->caching = false;
        $pnr->assign('total_hits', $total_hits);
        $pnr->assign('urltemplate', $urltemplate);
        $pnr->assign('loggedin', pnUserLoggedIn());
        $pnr->assign('searchresults', $searchresults);
        $pnr->assign('searchfor',    $vars['searchfor']);
        $pnr->assign('searchbool',   $vars['searchbool']);
        $pnr->assign('searchauthor', $vars['searchauthor']);
        $pnr->assign('searchforums', $vars['searchforums']);
        $pnr->assign('searchorder',  $vars['searchorder']);
        $pnr->assign('searchlimit',  $vars['searchlimit']);
        $pnr->assign('searchstart',  $vars['searchstart']);
        return $pnr->fetch('pnforum_user_searchresults.html');
    }
}

/**
 * showfavorites
 * switches the main view to favorites only
 *
 */
function pnForum_user_showfavorites()
{
    if(pnUserLoggedIn()) {
        if(!pnModAPILoad('pnForum', 'user')) {
            return showforumerror("loading userapi failed", __FILE__, __LINE__);
        } 
        $favorites = pnModAPIFunc('pnForum', 'user', 'change_favorite_status');
    }
    pnRedirect(pnModURL('pnForum', 'user', 'main'));
    return true;       
}

/**
 * showallforums
 * switches the main view to show all forums
 * we simply call showfavorites here, because its the same action :-)
 * this function is only for cosmetic issues
 *
 */
function pnForum_user_showallforums()
{
    pnForum_user_showfavorites();
}

?>
