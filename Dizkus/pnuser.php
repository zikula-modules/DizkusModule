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
 * main
 * show all categories and forums a user may see
 *
 *@params 'viewcat' int only expand the category, all others shall be hidden / collapsed
 */
function Dizkus_user_main($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }
    
    $viewcat   =  (int)FormUtil::getPassedValue('viewcat', (isset($args['viewcat'])) ? $args['viewcat'] : -1, 'GETPOST');
    $favorites = (bool)FormUtil::getPassedValue('favorites', (isset($args['favorites'])) ? $args['favorites'] : false, 'GETPOST');

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');
    $loggedIn = pnUserLoggedIn();
    if(pnModGetVar('Dizkus', 'favorites_enabled')=='yes') {
        if($loggedIn && !$favorites) {
            $favorites = pnModAPIFunc('Dizkus', 'user', 'get_favorite_status');
        }
    }
    if ($loggedIn && $favorites) {
        $tree = pnModAPIFunc('Dizkus', 'user', 'getFavorites', array('user_id' => (int)pnUserGetVar('uid'),
                                                                      'last_visit' => $last_visit ));
    } else {
        $tree = pnModAPIFunc('Dizkus', 'user', 'readcategorytree', array('last_visit' => $last_visit ));

        if(pnModGetVar('Dizkus', 'slimforum') == 'yes') {
            // this needs to be in here because we want to display the favorites
            // not go to it if there is only one
            // check if we have one category and one forum only
            if(count($tree)==1) {
                foreach($tree as $catname => $forumarray) {
                    if(count($forumarray['forums'])==1) {
                        return pnRedirect(pnModURL('Dizkus', 'user', 'viewforum', array('forum'=>$forumarray['forums'][0]['forum_id'])));
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

    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign( 'favorites', $favorites);
    $pnr->assign( 'tree', $tree);
    $pnr->assign( 'view_category', $viewcat);
    $pnr->assign( 'view_category_data', $view_category_data);
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    $pnr->assign( 'numposts', pnModAPIFunc('Dizkus', 'user', 'boardstats',
                                            array('id'   => '0',
                                                  'type' => 'all' )));
    return $pnr->fetch('dizkus_user_main.html');
}

/**
 * viewforum
 * opens a forum and shows the last postings
 *
 *@params 'forum' int the forum id
 *@params 'start' int the posting to start with if on page 1+
 */
function Dizkus_user_viewforum($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $forum_id = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
    $start    = (int)FormUtil::getPassedValue('start', (isset($args['start'])) ? $args['start'] : 0, 'GETPOST');

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    $forum = pnModAPIFunc('Dizkus', 'user', 'readforum',
                          array('forum_id'        => $forum_id,
                                'start'           => $start,
                                'last_visit'      => $last_visit,
                                'last_visit_unix' => $last_visit_unix));

    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign( 'forum', $forum);
    $pnr->assign( 'hot_threshold', pnModGetVar('Dizkus', 'hot_threshold'));
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    return $pnr->fetch('dizkus_user_viewforum.html');
}

/**
 * viewtopic
 *
 */
function Dizkus_user_viewtopic($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
    // begin patch #3494 part 1, credits to teb
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    // end patch #3494 part 1
    $start    = (int)FormUtil::getPassedValue('start', (isset($args['start'])) ? $args['start'] : 0, 'GETPOST');
    $view     = strtolower(FormUtil::getPassedValue('view', (isset($args['view'])) ? $args['view'] : '', 'GETPOST'));

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    if(!empty($view) && ($view=='next' || $view=='previous')) {
        $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_previous_or_next_topic_id',
                                 array('topic_id' => $topic_id,
                                       'view'     => $view));
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
                            array('topic' => $topic_id)));
    }

    // begin patch #3494 part 2, credits to teb
    if(!empty($post_id) && is_numeric($post_id) && empty($topic_id)) {
        $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_postid', array('post_id' => $post_id));
        if($topic_id <>false) {
            // redirect instad of continue, better for SEO
            return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', 
                                       array('topic' => $topic_id)));
        }
    }
    // end patch #3494 part 2
    
    $topic = pnModAPIFunc('Dizkus', 'user', 'readtopic',
                          array('topic_id'   => $topic_id,
                                'start'      => $start,
                                'last_visit' => $last_visit,
                                'count'      => true));

    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign( 'topic', $topic);
    $pnr->assign( 'post_count', count($topic['posts']));
    $pnr->assign( 'last_visit', $last_visit);
    $pnr->assign( 'last_visit_unix', $last_visit_unix);
    return $pnr->fetch('dizkus_user_viewtopic.html');

}

/**
 * reply
 *
 */
function Dizkus_user_reply($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $message  = FormUtil::getPassedValue('message', (isset($args['message'])) ? $args['message'] : '', 'GETPOST');
    $attach_signature = (int)FormUtil::getPassedValue('attach_signature', (isset($args['attach_signature'])) ? $args['attach_signature'] : 0, 'GETPOST');
    $subscribe_topic = (int)FormUtil::getPassedValue('subscribe_topic', (isset($args['subscribe_topic'])) ? $args['subscribe_topic'] : 0, 'GETPOST');
    $preview = FormUtil::getPassedValue('preview', (isset($args['preview'])) ? $args['preview'] : '', 'GETPOST');
    $submit = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $cancel = FormUtil::getPassedValue('cancel', (isset($args['cancel'])) ? $args['cancel'] : '', 'GETPOST');

    /**
     * if cancel is submitted move to forum-view
     */
    if(!empty($cancel)) {
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic'=> $topic_id)));
    }

    $preview = (empty($preview)) ? false : true;
    $submit = (empty($submit)) ? false : true;

    $message = dzkstriptags($message);
    // check for maximum message size
    if( (strlen($message) +  strlen('[addsig]')) > 65535  ) {
        LogUtil::registerStatus(_DZK_ILLEGALMESSAGESIZE);
        // switch to preview mode
        $preview = true;
    }

    if ($submit==true && $preview==false) {
        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }

        // ContactList integration: Is the user ignored and allowed to write an answer to this topic?
        $topic = DBUtil::selectObjectByID('dizkus_topics',$topic_id,'topic_id');
        $ignorelist_setting = pnModAPIFunc('Dizkus','user','get_settings_ignorelist',array('uid' => $topic['topic_poster']));
        if (pnModAvailable('ContactList') && ($ignorelist_setting == 'strict') && (pnModAPIFunc('ContactList','user','isIgnored',array('uid' => (int)$topic['topic_poster'], 'iuid' => pnUserGetVar('uid'))))) {
            LogUtil::registerError(_DZK_IGNORELISTNOREPLY);
            return pnRedirect(pnModURL('Dizkus','user','viewtopic',array('topic' => $topic_id)));
        }

        list($start,
             $post_id ) = pnModAPIFunc('Dizkus', 'user', 'storereply',
                                       array('topic_id'         => $topic_id,
                                             'message'          => $message,
                                             'attach_signature' => $attach_signature,
                                             'subscribe_topic'  => $subscribe_topic));
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
                            array('topic' => $topic_id,
                                  'start' => $start)) . '#pid' . $post_id);
    } else {
        list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');
        $reply = pnModAPIFunc('Dizkus', 'user', 'preparereply',
                              array('topic_id'   => $topic_id,
                                    'post_id'    => $post_id,
                                    'last_visit' => $last_visit,
                                    'reply_start'=> empty($message),
                                    'attach_signature' => $attach_signature,
                                    'subscribe_topic'  => $subscribe_topic));
        if($preview==true) {
            $reply['message'] = dzkVarPrepHTMLDisplay($message);
            list($reply['message_display']) = pnModCallHooks('item', 'transform', '', array($message));
            $reply['message_display'] = nl2br($reply['message_display']);
        }

        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign( 'reply', $reply);
        $pnr->assign( 'preview', $preview);
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('dizkus_user_reply.html');
    }
}

/**
 * newtopic
 *
 */
function Dizkus_user_newtopic($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $forum_id = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
    $subject  = FormUtil::getPassedValue('subject', (isset($args['subject'])) ? $args['subject'] : '', 'GETPOST');
    $message  = FormUtil::getPassedValue('message', (isset($args['message'])) ? $args['message'] : '', 'GETPOST');
    $attach_signature = (int)FormUtil::getPassedValue('attach_signature', (isset($args['attach_signature'])) ? $args['attach_signature'] : 0, 'GETPOST');
    $subscribe_topic = (int)FormUtil::getPassedValue('subscribe_topic', (isset($args['subscribe_topic'])) ? $args['subscribe_topic'] : 0, 'GETPOST');
    $preview = FormUtil::getPassedValue('preview', (isset($args['preview'])) ? $args['preview'] : '', 'GETPOST');
    $submit = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $cancel = FormUtil::getPassedValue('cancel', (isset($args['cancel'])) ? $args['cancel'] : '', 'GETPOST');

    $preview = (empty($preview)) ? false : true;
    $cancel  = (empty($cancel))  ? false : true;
    $submit  = (empty($submit))  ? false : true;

    //  if cancel is submitted move to forum-view
    if($cancel==true) {
        return pnRedirect(pnModURL('Dizkus','user', 'viewforum', array('forum'=>$forum_id)));
    }

    $message = dzkstriptags($message);
    // check for maximum message size
    if( (strlen($message) +  strlen('[addsig]')) > 65535  ) {
        LogUtil::registerStatus(_DZK_ILLEGALMESSAGESIZE);
        // switch to preview mode
        $preview = true;
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    $newtopic = pnModAPIFunc('Dizkus', 'user', 'preparenewtopic',
                             array('forum_id'   => $forum_id,
                                   'subject'    => $subject,
                                   'message'    => $message,
                                   'topic_start'=> (empty($subject) && empty($message)),
                                   'attach_signature' => $attach_signature,
                                   'subscribe_topic'  => $subscribe_topic));

    if($submit==true && $preview==false) {
        // it's a submitted page
        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }

        //store the new topic
        $topic_id = pnModAPIFunc('Dizkus', 'user', 'storenewtopic',
                                 array('forum_id'         => $forum_id,
                                       'subject'          => $subject,
                                       'message'          => $message,
                                       'attach_signature' => $attach_signature,
                                       'subscribe_topic'  => $subscribe_topic));
        if(pnModGetVar('Dizkus', 'newtopicconfirmation') == 'yes') {
            $pnr = pnRender::getInstance('Dizkus', false, null, true);
            $pnr->assign('topic', pnModAPIFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $topic_id, 'count' => false)));
            return $pnr->fetch('dizkus_user_newtopicconfirmation.html');

        } else {
            return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
                                array('topic' => $topic_id)));
        }
    } else {
        // new topic
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign( 'preview', $preview);
        $pnr->assign( 'newtopic', $newtopic);
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('dizkus_user_newtopic.html');
    }
}

/**
 * editpost
 *
 */
function Dizkus_user_editpost($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $subject  = FormUtil::getPassedValue('subject', (isset($args['subject'])) ? $args['subject'] : '', 'GETPOST');
    $message  = FormUtil::getPassedValue('message', (isset($args['message'])) ? $args['message'] : '', 'GETPOST');
    $attach_signature = (int)FormUtil::getPassedValue('attach_signature', (isset($args['attach_signature'])) ? $args['attach_signature'] : 0, 'GETPOST');
    $delete = FormUtil::getPassedValue('delete', (isset($args['delete'])) ? $args['delete'] : '', 'GETPOST');
    $preview = FormUtil::getPassedValue('preview', (isset($args['preview'])) ? $args['preview'] : '', 'GETPOST');
    $submit = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $cancel = FormUtil::getPassedValue('cancel', (isset($args['cancel'])) ? $args['cancel'] : '', 'GETPOST');

    if(empty($post_id) || !is_numeric($post_id)) {
        return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
    }
    $post = pnModAPIFunc('Dizkus', 'user', 'readpost',
                         array('post_id'    => $post_id));
    if(!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])
       && ($post['poster_data']['pn_uid'] <> pnUserGetVar('uid')) ) {
        return showforumerror(_DZK_NOAUTH, __FILE__, __LINE__);
    }

    $preview = (empty($preview)) ? false : true;

    //  if cancel is submitted move to forum-view
    if(!empty($cancel)) {
        return pnRedirect(pnModURL('Dizkus','user', 'viewtopic', array('topic'=>$topic_id)));
    }

    $message = dzkstriptags($message);
    // check for maximum message size
    if( (strlen($message) +  strlen('[addsig]')) > 65535  ) {
        LogUtil::registerStatus(_DZK_ILLEGALMESSAGESIZE);
        // switch to preview mode
        $preview = true;
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    if($submit && !$preview) {
        /**
         * Confirm authorisation code
         */
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        //store the new topic
        $redirect = pnModAPIFunc('Dizkus', 'user', 'updatepost',
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
        $firstpost = pnModAPIFunc('Dizkus', 'user', 'get_firstlast_post_in_topic',
                                  array('topic_id' => $post['topic_id'],
                                        'first'    => true));
        if($post['poster_data']['pn_uid'] == $firstpost['poster_data']['pn_uid']) {
            $post['edit_subject'] = true;
        }

        if(!empty($message)) {
            $post['post_rawtext'] = $message;
            list($post['post_textdisplay']) = pnModCallHooks('item', 'transform', '', array(nl2br($message)));
        }
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign( 'preview', $preview);
        $pnr->assign( 'post', $post);
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('dizkus_user_editpost.html');
    }
}

/**
 * topicadmin
 *
 */
function Dizkus_user_topicadmin($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $forum_id = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
    $mode   = FormUtil::getPassedValue('mode', (isset($args['mode'])) ? $args['mode'] : '', 'GETPOST');
    $submit = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $shadow = FormUtil::getPassedValue('createshadowtopic', (isset($args['createshadowtopic'])) ? $args['createshadowtopic'] : '', 'GETPOST');
    $shadow = (empty($shadow)) ? false : true;

    if(empty($topic_id) && !empty($post_id)) {
        $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_postid',
                                 array('post_id' => $post_id));
    }
    $topic = pnModAPIFunc('Dizkus', 'user', 'readtopic',
                          array('topic_id' => $topic_id, 'count' => false));
    if($topic['access_moderate']<>true) {
        return showforumerror(_DZK_NOAUTH_TOMODERATE, __FILE__, __LINE__);
    }

    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign('mode', $mode);
    $pnr->assign('topic_id', $topic_id);
    $pnr->assign('last_visit', $last_visit);
    $pnr->assign('last_visit_unix', $last_visit_unix);

    if(empty($submit)) {
        switch($mode) {
            case 'del':
            case 'delete':
                $templatename = 'dizkus_user_deletetopic.html';
                break;
            case 'move':
            case 'join':
                $pnr->assign('forums', pnModAPIFunc('Dizkus', 'user', 'readuserforums'));
                $templatename = 'dizkus_user_movetopic.html';
                break;
            case 'lock':
            case 'unlock':
                $templatename = 'dizkus_user_locktopic.html';
                break;
            case 'sticky':
            case 'unsticky':
                $templatename = 'dizkus_user_stickytopic.html';
                break;
            case 'viewip':
                $pnr->assign('viewip', pnModAPIFunc('Dizkus', 'user', 'get_viewip_data', array('post_id' => $post_id)));
                $templatename = 'dizkus_user_viewip.html';
                break;
            default:
                return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic'=>$topic_id)));
        }
        return $pnr->fetch($templatename);

    } else { // submit is set
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        switch($mode) {
            case 'del':
            case 'delete':
                $forum_id = pnModAPIFunc('Dizkus', 'user', 'deletetopic', array('topic_id'=>$topic_id));
                return pnRedirect(pnModURL('Dizkus', 'user', 'viewforum', array('forum'=>$forum_id)));
                break;
            case 'move':
                list($f_id, $c_id) = Dizkus_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id' => $topic_id));
                if($forum_id == $f_id) {
                    return showforumerror(_DZK_SOURCEEQUALSTARGETFORUM, __FILE__, __LINE__);
                }
                if(!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod',$f_id, 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
                }
                pnModAPIFunc('Dizkus', 'user', 'movetopic', array('topic_id' => $topic_id,
                                                                   'forum_id' => $forum_id,
                                                                   'shadow'   => $shadow ));
                break;
            case 'lock':
            case 'unlock':
                list($f_id, $c_id) = pnModAPIFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                  array('topic_id' => $topic_id));
                if(!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod',$f_id, 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
                }
                pnModAPIFunc('Dizkus', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                break;
            case 'sticky':
            case 'unsticky':
                list($f_id, $c_id) = pnModAPIFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                  array('topic_id' => $topic_id));
                if(!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod',$f_id, 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
                }
                pnModAPIFunc('Dizkus', 'user', 'stickyunstickytopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                break;
            case 'join':
                $to_topic_id = (int)FormUtil::getPassedValue('to_topic_id', (isset($args['to_topic_id'])) ? $args['to_topic_id'] : null, 'GETPOST');
                if(!empty($to_topic_id) && ($to_topic_id == $topic_id)) {
                    // user wants to copy topic to itself
                    return showforumerror(_DZK_SOURCEEQUALSTARGETTOPIC, __FILE__, __LINE__);
                }
                list($f_id, $c_id) = Dizkus_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id' => $to_topic_id));
                if(!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod',$f_id, 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
                }
                pnModAPIFunc('Dizkus', 'user', 'jointopics', array('from_topic_id' => $topic_id,
                                                                    'to_topic_id'   => $to_topic_id));
                return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $to_topic_id)));
                break;
            default:
        }
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic'=>$topic_id)));
    }
}

/**
 * prefs
 *
 */
function Dizkus_user_prefs($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    if(!pnUserLoggedIn()) {
        return pnModFunc('Dizkus', 'user', 'login', array('redirect' => pnModURL('Dizkus', 'user', 'prefs')));
    }

    // get the input
    $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
    $act = FormUtil::getPassedValue('act', (isset($args['act'])) ? $args['act'] : '', 'GETPOST');
    $return_to = FormUtil::getPassedValue('return_to', (isset($args['return_to'])) ? $args['return_to'] : '', 'GETPOST');
    $forum_id = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
    $user_id = (int)FormUtil::getPassedValue('user', (isset($args['user'])) ? $args['user'] : null, 'GETPOST');

    // user_id will only be used if we have admin permissions otherwise the
    // user can edit his prefs only but not others users prefs

    switch($act) {
        case 'subscribe_topic':
            $return_to = (!empty($return_to))? $return_to : 'viewtopic';
            pnModAPIFunc('Dizkus', 'user', 'subscribe_topic',
                         array('topic_id' => $topic_id ));
            $params = array('topic' => $topic_id);
            break;
        case 'unsubscribe_topic':
            $return_to = (!empty($return_to))? $return_to : 'viewtopic';
            pnModAPIFunc('Dizkus', 'user', 'unsubscribe_topic',
                         array('topic_id' => $topic_id ));
            $params = array('topic' => $topic_id);
            break;
        case 'subscribe_forum':
            $return_to = (!empty($return_to))? $return_to : 'viewforum';
            pnModAPIFunc('Dizkus', 'user', 'subscribe_forum',
                         array('forum_id' => $forum_id ));
            $params = array('forum' => $forum_id);
            break;
        case 'unsubscribe_forum':
            $return_to = (!empty($return_to))? $return_to : 'viewforum';
            pnModAPIFunc('Dizkus', 'user', 'unsubscribe_forum',
                         array('forum_id' => $forum_id ));
            $params = array('forum' => $forum_id);
            break;
        case 'add_favorite_forum':
            if(pnModGetVar('Dizkus', 'favorites_enabled')=='yes') {
                $return_to = (!empty($return_to))? $return_to : 'viewforum';
                pnModAPIFunc('Dizkus', 'user', 'add_favorite_forum',
                             array('forum_id' => $forum_id ));
                $params = array('forum' => $forum_id);
            }
            break;
        case 'remove_favorite_forum':
            if(pnModGetVar('Dizkus', 'favorites_enabled')=='yes') {
                $return_to = (!empty($return_to))? $return_to : 'viewforum';
                pnModAPIFunc('Dizkus', 'user', 'remove_favorite_forum',
                             array('forum_id' => $forum_id ));
                $params = array('forum' => $forum_id);
            }
            break;
        case 'change_post_order':
            $return_to = (!empty($return_to))? $return_to : 'viewtopic';
            pnModAPIFunc('Dizkus', 'user', 'change_user_post_order');
            $params = array('topic' => $topic_id);
            break;
        case 'showallforums':
        case 'showfavorites':
            if(pnModGetVar('Dizkus', 'favorites_enabled')=='yes') {
                $return_to = (!empty($return_to))? $return_to : 'main';
                $favorites = pnModAPIFunc('Dizkus', 'user', 'change_favorite_status');
                $params = array();
            }
            break;
        default:
            list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');
            $pnr = pnRender::getInstance('Dizkus', false, null, true);
            $pnr->assign('last_visit', $last_visit);
            $pnr->assign('favorites_enabled', pnModGetVar('Dizkus', 'favorites_enabled'));
            $pnr->assign('last_visit_unix', $last_visit_unix);
            $pnr->assign('signaturemanagement', pnModGetVar('Dizkus','signaturemanagement'));
            $pnr->assign('ignorelist_handling', pnModGetVar('Dizkus','ignorelist_handling'));
            $pnr->assign('contactlist_available', pnModAvailable('ContactList'));
            $pnr->assign('post_order', strtolower(pnModAPIFunc('Dizkus','user','get_user_post_order')));
            $pnr->assign('tree', pnModAPIFunc('Dizkus', 'user', 'readcategorytree', array('last_visit' => $last_visit )));
            return $pnr->fetch('dizkus_user_prefs.html');
    }
    return pnRedirect(pnModURL('Dizkus', 'user', $return_to, $params));
}

/**
 * signature management
 * 
 */
function Dizkus_user_signaturemanagement()
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    if(!pnUserLoggedIn()) {
        return pnModFunc('Dizkus', 'user', 'login', array('redirect' => pnModURL('Dizkus', 'user', 'prefs')));
    }
    // Security check
    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT) || (!(pnModGetVar('Dizkus','signaturemanagement') == 'yes'))) {
        return LogUtil::registerPermissionError();
    }
    
    // Include handler class
    Loader::requireOnce('modules/Dizkus/pnincludes/dizkus_user_signaturemanagementhandler.class.php');
    
    // Create output and assign data
    $render = FormUtil::newpnForm('Dizkus');
    // Return the output
    return $render->pnFormExecute('dizkus_user_signaturemanagement.html', new dizkus_user_signaturemanagementHandler());
}

/**
 * ignorelist management
 * 
 */
function Dizkus_user_ignorelistmanagement()
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    if(!pnUserLoggedIn()) {
        return pnModFunc('Dizkus', 'user', 'login', array('redirect' => pnModURL('Dizkus', 'user', 'prefs')));
    }
    // Security check
    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT)) {
        return LogUtil::registerPermissionError();
    }

	// check for Contactlist module and admin settings
	$ignorelist_handling = pnModGetVar('Dizkus','ignorelist_handling');
	if (!pnModAvailable('ContactList') || ($ignorelist_handling == 'none')) {
	  	LogUtil::registerError(_DZK_PREFS_NOCONFIGPOSSIBLE);
	  	return pnRedirect(pnModURL('Dizkus', 'user', 'prefs'));
	}
    // Include handler class
    Loader::requireOnce('modules/Dizkus/pnincludes/dizkus_user_ignorelistmanagementhandler.class.php');
    
    // Create output and assign data
    $render = FormUtil::newpnForm('Dizkus');
    // Return the output
    return $render->pnFormExecute('dizkus_user_ignorelistmanagement.html', new dizkus_user_ignorelistmanagementHandler());
}

/**
 * emailtopic
 *
 */
function Dizkus_user_emailtopic($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $topic_id      = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
    $emailsubject  = FormUtil::getPassedValue('emailsubject', (isset($args['emailsubject'])) ? $args['emailsubject'] : '', 'GETPOST');
    $message       = FormUtil::getPassedValue('message', (isset($args['message'])) ? $args['message'] : '', 'GETPOST');
    $sendto_email  = FormUtil::getPassedValue('sendto_email', (isset($args['sendto_email'])) ? $args['sendto_email'] : '', 'GETPOST');
    $submit        = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');

    if(!pnUserLoggedIn()) {
        return showforumerror(_DZK_NOTLOGGEDIN, __FILE__, __LINE__);
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    if(!empty($submit)) {
        if (!pnVarValidate($sendto_email, 'email')) {
            // Empty e-mail is checked here too
            $error_msg = DataUtil::formatForDisplay(_DZK_MAILTO_WRONGEMAIL);
            $sendto_email = '';
            unset($submit);
        } else if ($message == '') {
            $error_msg = DataUtil::formatForDisplay(_DZK_MAILTO_NOBODY);
            unset($submit);
        } else if ($emailsubject == '') {
            $error_msg = DataUtil::formatForDisplay(_DZK_MAILTO_NOSUBJECT);
            unset($submit);
        }
    }

//    $topic = pnModAPIFunc('Dizkus', 'user', 'prepareemailtopic',
//                          array('topic_id'   => $topic_id));

    if(!empty($submit)) {
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }

        pnModAPIFunc('Dizkus', 'user', 'emailtopic',
                     array('sendto_email' => $sendto_email,
                           'message'      => $message,
                           'subject'      => $emailsubject));
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
    } else {
        $topic = pnModAPIFunc('Dizkus', 'user', 'prepareemailtopic',
                              array('topic_id'   => $topic_id));
        $emailsubject = (!empty($emailsubject)) ? $emailsubject : $topic['topic_subject'];
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('topic', $topic);
        $pnr->assign('error_msg', $error_msg);
        $pnr->assign('sendto_email', $sendto_email);
        $pnr->assign('emailsubject', $emailsubject);
        $pnr->assign('message', DataUtil::formatForDisplay(_DZK_EMAILTOPICMSG) ."\n\n" . pnGetBaseURL() . pnModURL('Dizkus', 'user', 'viewtopic', array('topic'=>$topic_id)));
        $pnr->assign( 'last_visit', $last_visit);
        $pnr->assign( 'last_visit_unix', $last_visit_unix);
        return $pnr->fetch('dizkus_user_emailtopic.html');
    }
}

/**
 * latest
 *
 */
function Dizkus_user_viewlatest($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    if(useragent_is_bot() == true) {
        return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
    }

    // get the input
    $selorder   = (int)FormUtil::getPassedValue('selorder', (isset($args['selorder'])) ? $args['selorder'] : 1, 'GETPOST');
    $nohours    = (int)FormUtil::getPassedValue('nohours', (isset($args['nohours'])) ? $args['nohours'] : null, 'GETPOST');
    $unanswered = (int)FormUtil::getPassedValue('unanswered', (isset($args['unanswered'])) ? $args['unanswered'] : 0, 'GETPOST');

    if(!empty($nohours) && !is_numeric($nohours)) {
        unset($nohours);
    }
    // maximum two weeks back = 2 * 24 * 7 hours
    if(isset($nohours) && $nohours>336) {
        $nohours = 336;
    }
    
    if(!empty($nohours)) {
        $selorder = 5;
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    list($posts, $m2fposts, $rssposts, $text) = pnModAPIFunc('Dizkus', 'user', 'get_latest_posts',
                                                             array('selorder'   => $selorder,
                                                                   'nohours'    => $nohours,
                                                                   'unanswered' => $unanswered,
                                                                   'last_visit' => $last_visit,
                                                                   'last_visit_unix' => $last_visit_unix));

    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign('posts', $posts);
    $pnr->assign('m2fposts', $m2fposts);
    $pnr->assign('rssposts', $rssposts);
    $pnr->assign('text', $text);
    $pnr->assign('nohours', $nohours);
    $pnr->assign('last_visit', $last_visit);
    $pnr->assign('last_visit_unix', $last_visit_unix);
    $pnr->assign('numposts', pnModAPIFunc('Dizkus', 'user', 'boardstats',
                                            array('id'   => '0',
                                                  'type' => 'all' )));
    return $pnr->fetch('dizkus_user_latestposts.html');

}

/**
 * splittopic
 *
 */
function Dizkus_user_splittopic($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id    = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $newsubject = FormUtil::getPassedValue('newsubject', (isset($args['newsubject'])) ? $args['newsubject'] : '', 'GETPOST');
    $submit     = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');

    $post = pnModAPIFunc('Dizkus', 'user', 'readpost',
                         array('post_id' => $post_id));

    if(!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod',$post['forum_id'], 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    if(!empty($submit)) {
        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        // submit is set, we split the topic now
        $post['topic_subject'] = $newsubject;
        $newtopic_id = pnModAPIFunc('Dizkus', 'user', 'splittopic',
                                   array('post' => $post));
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
                                   array('topic' => $newtopic_id)));

    } else {
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('post', $post);
        return $pnr->fetch('dizkus_user_splittopic.html');
    }
}

/**
 * print
 * prepare print view of the selected posting or topic
 *
 */
function Dizkus_user_print($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');

    if(useragent_is_bot() == true) {
        if($post_id <> 0 ) {
            $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_postid',
                                    array('post_id' => $post_id));
        }
        if(($topic_id <> 0) && ($topic_id<>false)) {
            return Dizkus_user_viewtopic(array('topic' => $topic_id,
                                                'start'   => 0));
        } else {
            return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
        }
    } else {
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        if($post_id<>0) {
            $post = pnModAPIFunc('Dizkus', 'user', 'readpost',
                                 array('post_id' => $post_id));
            $pnr->assign('post', $post);
            $output = $pnr->fetch('dizkus_user_printpost.html');
        } elseif($topic_id<>0) {
            $topic = pnModAPIFunc('Dizkus', 'user', 'readtopic',
                                 array('topic_id'  => $topic_id,
                                       'complete' => true,
                                       'count' => false ));
            $pnr->assign('topic', $topic);
            $output = $pnr->fetch('dizkus_user_printtopic.html');
        } else {
            return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
        }
        $lang = pnConfigGetVar('backend_language');
        echo "<?xml version=\"1.0\" encoding=\"iso-8859-15\"?>\n";
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
        echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"$lang\" xml:lang=\"$lang\">\n";
        echo "<head>\n";
        echo "<title>" . DataUtil::formatForDisplay($topic['topic_title']) . "</title>\n";
        echo "<link rel=\"StyleSheet\" href=\"themes/" . pnUserGetTheme() . "/style/style.css\" type=\"text/css\" />\n";
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=". pnModGetVar('Dizkus', 'default_lang') ."\" />\n";

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
        pnShutDown();
    }
}

/**
 * search
 * internal search function
 *
 */
function Dizkus_user_search($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    $submit = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    if(!$submit) {
        return pnModAPIFunc('Dizkus', 'search', 'internalsearchoptions');
    } else {
        return pnModAPIFunc('Dizkus', 'search', 'search');
    }
}

/**
 * movepost
 * Move a single post to another thread
 * added by by el_cuervo -- dev-postnuke.com
 *
 */
function Dizkus_user_movepost($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $to_topic = (int)FormUtil::getPassedValue('to_topic', (isset($args['to_topic'])) ? $args['to_topic'] : null, 'GETPOST');

    $post = pnModAPIFunc('Dizkus', 'user', 'readpost', array('post_id' => $post_id));

    if(!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod', $post['forum_id'], 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    if(!empty($submit)) {
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        // submit is set, we move the posting now
        // Existe el Topic ? --- Exists new Topic ?
        $topic = pnModAPIFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $to_topic,
                                                                    'complete' => false,
                                                                    'count' => false));
        $post['new_topic'] = $to_topic;
        $post['old_topic'] = $topic['topic_id'];
        $start = pnModAPIFunc('Dizkus', 'user', 'movepost', array('post'     => $post,
                                                                   'to_topic' => $to_topic));
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
                                   array('topic' => $to_topic,
                                         'start' => $start)) . '#pid' . $post['post_id']);
    } else {
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('post', $post);
        return $pnr->fetch('dizkus_user_movepost.html');
    }
}

/**
 * jointopics
 * Join a topic with another toipic                                                                                                  ?>
 * by el_cuervo -- dev-postnuke.com
 *
 */
function Dizkus_user_jointopics($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id       = (int)FormUtil::getPassedValue('post_id', (isset($args['post_id'])) ? $args['post_id'] : null, 'GETPOST');
    $submit        = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $to_topic_id   = (int)FormUtil::getPassedValue('to_topic_id', (isset($args['to_topic_id'])) ? $args['to_topic_id'] : null, 'GETPOST');
    $from_topic_id = (int)FormUtil::getPassedValue('from_topic_id', (isset($args['from_topic_id'])) ? $args['from_topic_id'] : null, 'GETPOST');

    $post = pnModAPIFunc('Dizkus', 'user', 'readpost', array('post_id' => $post_id));

    if(!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod',$post['forum_id'], 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }

    if(!$submit) {
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('post', $post);
        return $pnr->fetch('dizkus_user_jointopics.html');
    } else {
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }

        // check if from_topic exists. this function will return an error if not
        $from_topic = pnModAPIFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $from_topic_id, 'complete' => false, 'count' => false));
        // check if to_topic exists. this function will return an error if not
        $to_topic = pnModAPIFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $to_topic_id, 'complete' => false, 'count' => false));
        // submit is set, we split the topic now
        //$post['new_topic'] = $totopic;
        //$post['old_topic'] = $old_topic;
        $res = pnModAPIFunc('Dizkus', 'user', 'jointopics', array('from_topic' => $from_topic,
                                                                   'to_topic'   => $to_topic));
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $res)));
    }
}

/**
 * moderateforum
 * simple moderation of multiple topics
 *
 *@params to be documented :-)
 *
 */
function Dizkus_user_moderateforum($args=array())
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $forum_id = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
    $start    = (int)FormUtil::getPassedValue('start', (isset($args['start'])) ? $args['start'] : 0, 'GETPOST');
    $mode   = FormUtil::getPassedValue('mode', (isset($args['mode'])) ? $args['mode'] : '', 'GETPOST');
    $submit        = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $topic_ids = FormUtil::getPassedValue('topic_id', (isset($args['topic_id'])) ? $args['topic_id'] : array(), 'GETPOST');
    $shadow = FormUtil::getPassedValue('createshadowtopic', (isset($args['createshadowtopic'])) ? $args['createshadowtopic'] : '', 'GETPOST');
    $moveto = (int)FormUtil::getPassedValue('moveto', (isset($args['moveto'])) ? $args['moveto'] : null, 'GETPOST');
    $jointo = (int)FormUtil::getPassedValue('jointo', (isset($args['jointo'])) ? $args['jointo'] : null, 'GETPOST');

    $shadow = (empty($shadow)) ? false : true;

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    // Get the Forum for Display and Permission-Check
    $forum = pnModAPIFunc('Dizkus', 'user', 'readforum',
                          array('forum_id'        => $forum_id,
                                'start'           => $start,
                                'last_visit'      => $last_visit,
                                'last_visit_unix' => $last_visit_unix));

    if(!allowedtomoderatecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod',$post['forum_id'], 'forum', _DZK_NOAUTH_TOMODERATE), __FILE__, __LINE__);
    }


    // Submit isn't set'
    if(empty($submit)) {
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('forum_id', $forum_id);
        $pnr->assign('mode',$mode);
        $pnr->assign('topic_ids', $topic_ids);
        $pnr->assign('last_visit', $last_visit);
        $pnr->assign('last_visit_unix', $last_visit_unix);
        $pnr->assign('forum',$forum);
        // For Movetopic
        $pnr->assign('forums', pnModAPIFunc('Dizkus', 'user', 'readuserforums'));
        return $pnr->fetch('dizkus_user_moderateforum.html');

    } else {
        // submit is set
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        if(count($topic_ids)<>0) {
            switch($mode) {
                case 'del':
                case 'delete':
                    foreach($topic_ids as $topic_id) {
                        $forum_id = pnModAPIFunc('Dizkus', 'user', 'deletetopic', array('topic_id'=>$topic_id));
                    }
                    break;
                case 'move':
                    if(empty($moveto)) {
                        return showforumerror(_DZK_NOMOVETO, __FILE__, __LINE__);
                    }
                    foreach ($topic_ids as $topic_id) {
                        pnModAPIFunc('Dizkus', 'user', 'movetopic', array('topic_id' => $topic_id,
                                                                           'forum_id' => $moveto,
                                                                           'shadow'   => $shadow ));
                    }
                    break;
                case 'lock':
                case 'unlock':
                    foreach($topic_ids as $topic_id) {
                        pnModAPIFunc('Dizkus', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                    }
                    break;
                case 'sticky':
                case 'unsticky':
                    foreach($topic_ids as $topic_id) {
                        pnModAPIFunc('Dizkus', 'user', 'stickyunstickytopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                    }
                    break;
                case 'join':
                    if(empty($jointo)) {
                        return showforumerror(_DZK_NOJOINTO, __FILE__, __LINE__);
                    }
                    if(in_array($jointo, $topic_ids)) {
                        // jointo, the target topic, is part of the topics to join
                        // we remove this to avoid a loop
                        $fliparray = array_flip($topic_ids);
                        unset($fliparray[$jointo]);
                        $topic_ids = array_flip($fliparray);
                    }
                    foreach($topic_ids as $to_topic_id) {
                        pnModAPIFunc('Dizkus', 'user', 'jointopics', array('from_topic_id' => $topic_id,
                                                                            'to_topic_id'   => $jointo));
                    }
                    break;
                default:
            }
            // Refresh Forum Info
            $forum = pnModAPIFunc('Dizkus', 'user', 'readforum',
                              array('forum_id'        => $forum_id,
                                    'start'           => $start,
                                    'last_visit'      => $last_visit,
                                    'last_visit_unix' => $last_visit_unix));
        }
    }
    return pnRedirect(pnModURL('Dizkus', 'user', 'moderateforum', array('forum' => $forum_id)));
}

/**
 * report
 * notify a moderator about a posting
 *
 *@params $post int post_id
 *@params $comment string comment of reporter
 *
 */
function Dizkus_user_report($args)
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $submit        = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $comment        = FormUtil::getPassedValue('comment', (isset($args['comment'])) ? $args['comment'] : '', 'GETPOST');

    $post = pnModAPIFunc('Dizkus', 'user', 'readpost',
                         array('post_id' => $post_id));

    // some spam checks:
    // - remove html and compare with original comment
    // - use censor and compare with original omment
    // if only one of this comparisons fails -> trash it, its spam.
    if(!pnUserLoggedIn() && SecurityUtil::confirmAuthKey()) {
        if((strip_tags($comment) <> $comment) ||
           (pnVarCensor($comment) <> $comment)) {
            // possibly spam, stop now
            // get the users ip address and store it in pnTemp/Dizkus_spammers.txt
            dzk_blacklist();
            // set 403 header and stop
            header('HTTP/1.0 403 Forbidden');
            pnShutDown();
        }
    }
    
    if(!$submit) {
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('post', $post);
        return $pnr->fetch('dizkus_user_notifymod.html');
    } else {   // submit is set
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        pnModAPIFunc('Dizkus', 'user', 'notify_moderator',
                     array('post'    => $post,
                           'comment' => $comment));
        $start = pnModAPIFunc('Dizkus', 'user', 'get_page_from_topic_replies',
                              array('topic_replies' => $post['topic_replies']));
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
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
function Dizkus_user_topicsubscriptions($args)
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    if(!pnUserLoggedIn()) {
        return pnModFunc('Dizkus', 'user', 'login', array('redirect' => pnModURL('Dizkus', 'user', 'prefs')));
    }

    // get the input
    $topic_id = FormUtil::getPassedValue('topic_id', (isset($args['topic_id'])) ? $args['topic_id'] : null, 'GETPOST');
    $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');

    if(!$submit) {
        $subscriptions = pnModAPIFunc('Dizkus', 'user', 'get_topic_subscriptions');
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('subscriptions', $subscriptions);
        return $pnr->fetch('dizkus_user_topicsubscriptions.html');
    } else {  // submit is set
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        if(is_array($topic_id) && (count($topic_id) > 0)) {
            for($i=0; $i<count($topic_id); $i++) {
                pnModAPIFunc('Dizkus', 'user', 'unsubscribe_topic', array('topic_id' => $topic_id[$i]));
            }
        }
        return pnRedirect(pnModURL('Dizkus', 'user', 'topicsubscriptions'));
    }
}

/**
 * login
 *
 */
function Dizkus_user_login($args)
{
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        return $disabled;
    }

    if(pnUserLoggedIn()) {
        return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
    }

    // get the input
    $submit        = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $uname        = FormUtil::getPassedValue('uname', (isset($args['uname'])) ? $args['uname'] : '', 'GETPOST');
    $pass        = FormUtil::getPassedValue('pass', (isset($args['pass'])) ? $args['pass'] : '', 'GETPOST');
    $rememberme        = FormUtil::getPassedValue('rememberme', (isset($args['rememberme'])) ? $args['rememberme'] : '', 'GETPOST');
    $redirect        = FormUtil::getPassedValue('redirect', (isset($args['redirect'])) ? $args['redirect'] : pnModURL('Dizkus', 'user', 'main'), 'GETPOST');

    if(!$submit) {
        $pnr = pnRender::getInstance('Dizkus', false);
        $pnr->add_core_data('PNConfig');
        $pnr->assign('redirect', $redirect);
        return $pnr->fetch('dizkus_user_login.html');
    } else { // submit is set
        // login
        if(pnUserLogin($uname, $pass, $rememberme) == false) {
            return showforumerror(_DZK_ERRORLOGGINGIN, __FILE__, __LINE__);
        }
        return pnRedirect($redirect);
    }

}
