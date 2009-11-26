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
 * @params 'viewcat' int only expand the category, all others shall be hidden / collapsed
 */
function Dizkus_user_main($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }
    
    $viewcat   =  (int)FormUtil::getPassedValue('viewcat', (isset($args['viewcat'])) ? $args['viewcat'] : -1, 'GETPOST');
    $favorites = (bool)FormUtil::getPassedValue('favorites', (isset($args['favorites'])) ? $args['favorites'] : false, 'GETPOST');

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');
    $loggedIn = pnUserLoggedIn();

    if (pnModGetVar('Dizkus', 'favorites_enabled') == 'yes') {
        if ($loggedIn && !$favorites) {
            $favorites = pnModAPIFunc('Dizkus', 'user', 'get_favorite_status');
        }
    }
    if ($loggedIn && $favorites) {
        $tree = pnModAPIFunc('Dizkus', 'user', 'getFavorites',
                             array('user_id'    => (int)pnUserGetVar('uid'),
                                   'last_visit' => $last_visit ));
    } else {
        $tree = pnModAPIFunc('Dizkus', 'user', 'readcategorytree',
                             array('last_visit' => $last_visit ));

        if (pnModGetVar('Dizkus', 'slimforum') == 'yes') {
            // this needs to be in here because we want to display the favorites
            // not go to it if there is only one
            // check if we have one category and one forum only
            if (count($tree)==1) {
                foreach ($tree as $catname => $forumarray) {
                    if (count($forumarray['forums']) == 1) {
                        return pnRedirect(pnModURL('Dizkus', 'user', 'viewforum', array('forum'=>$forumarray['forums'][0]['forum_id'])));
                    } else {
                        $viewcat = $tree[$catname]['cat_id'];
                    }
                }
            }
        }
    }

    $view_category_data = array();
    if ($viewcat <> -1) {
        foreach ($tree as $category) {
            if ($category['cat_id'] == $viewcat) {
                $view_category_data = $category;
                break;
            }
        }
    }

    $render = & pnRender::getInstance('Dizkus', false, null, true);
    $render->assign('favorites', $favorites);
    $render->assign('tree', $tree);
    $render->assign('view_category', $viewcat);
    $render->assign('view_category_data', $view_category_data);
    $render->assign('last_visit', $last_visit);
    $render->assign('last_visit_unix', $last_visit_unix);
    $render->assign('numposts', pnModAPIFunc('Dizkus', 'user', 'boardstats',
                                            array('id'   => '0',
                                                  'type' => 'all' )));
    return $render->fetch('dizkus_user_main.html');
}

/**
 * viewforum
 * opens a forum and shows the last postings
 *
 * @params 'forum' int the forum id
 * @params 'start' int the posting to start with if on page 1+
 */
function Dizkus_user_viewforum($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
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

    $render = & pnRender::getInstance('Dizkus', false, null, true);
    $render->assign( 'forum', $forum);
    $render->assign( 'hot_threshold', pnModGetVar('Dizkus', 'hot_threshold'));
    $render->assign( 'last_visit', $last_visit);
    $render->assign( 'last_visit_unix', $last_visit_unix);
    return $render->fetch('dizkus_user_viewforum.html');
}

/**
 * viewtopic
 *
 */
function Dizkus_user_viewtopic($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
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

    if (!empty($view) && ($view=='next' || $view=='previous')) {
        $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_previous_or_next_topic_id',
                                 array('topic_id' => $topic_id,
                                       'view'     => $view));
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
                            array('topic' => $topic_id)));
    }

    // begin patch #3494 part 2, credits to teb
    if (!empty($post_id) && is_numeric($post_id) && empty($topic_id)) {
        $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_postid', array('post_id' => $post_id));
        if ($topic_id <> false) {
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

    $render = & pnRender::getInstance('Dizkus', false, null, true);
    $render->assign('avatarpath', pnModGetVar('Users', 'avatarpath'));
    $render->assign('topic', $topic);
    $render->assign('post_count', count($topic['posts']));
    $render->assign('last_visit', $last_visit);
    $render->assign('last_visit_unix', $last_visit_unix);
    return $render->fetch('dizkus_user_viewtopic.html');

}

/**
 * reply
 *
 */
function Dizkus_user_reply($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
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
    if (!empty($cancel)) {
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic'=> $topic_id)));
    }

    $preview = (empty($preview)) ? false : true;
    $submit = (empty($submit)) ? false : true;

    $message = dzkstriptags($message);
    // check for maximum message size
    if ((strlen($message) +  strlen('[addsig]')) > 65535) {
        LogUtil::registerStatus(__('Error! Illegal message size. The maximum size of a post is 65,535 characters.', $dom));
        // switch to preview mode
        $preview = true;
    }

    if ($submit == true && $preview == false) {
        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }

        // ContactList integration: Is the user ignored and allowed to write an answer to this topic?
        $topic = DBUtil::selectObjectByID('dizkus_topics',$topic_id,'topic_id');
        $ignorelist_setting = pnModAPIFunc('Dizkus','user','get_settings_ignorelist',array('uid' => $topic['topic_poster']));
        if (pnModAvailable('ContactList') && ($ignorelist_setting == 'strict') && (pnModAPIFunc('ContactList','user','isIgnored',array('uid' => (int)$topic['topic_poster'], 'iuid' => pnUserGetVar('uid'))))) {
            LogUtil::registerError(__('Sorry! The user who started this topic is ignoring you, and does not want you to be able to write posts under this topic. Please contact the topic originator for more information.', $dom));
            return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
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
        if ($preview == true) {
            $reply['message'] = dzkVarPrepHTMLDisplay($message);
            list($reply['message_display']) = pnModCallHooks('item', 'transform', '', array($message));
            $reply['message_display'] = nl2br($reply['message_display']);
        }

        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('avatarpath', pnModGetVar('Users', 'avatarpath'));
        $render->assign('reply', $reply);
        $render->assign('preview', $preview);
        $render->assign('last_visit', $last_visit);
        $render->assign('last_visit_unix', $last_visit_unix);
        return $render->fetch('dizkus_user_reply.html');
    }
}

/**
 * newtopic
 *
 */
function Dizkus_user_newtopic($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
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
    if ($cancel == true) {
        return pnRedirect(pnModURL('Dizkus','user', 'viewforum', array('forum' => $forum_id)));
    }

    $message = dzkstriptags($message);
    // check for maximum message size
    if ((strlen($message) +  strlen('[addsig]')) > 65535) {
        LogUtil::registerStatus(__('Error! Illegal message size. The maximum size of a post is 65,535 characters.', $dom));
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

    if ($submit == true && $preview == false) {
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

        if (pnModGetVar('Dizkus', 'newtopicconfirmation') == 'yes') {
            $render = & pnRender::getInstance('Dizkus', false, null, true);
            $render->assign('topic', pnModAPIFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $topic_id, 'count' => false)));
            return $render->fetch('dizkus_user_newtopicconfirmation.html');

        } else {
            return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
                                       array('topic' => $topic_id),
                                       null, null, true));
        }
    } else {
        // new topic
        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('avatarpath', pnModGetVar('Users', 'avatarpath'));
        $render->assign('preview', $preview);
        $render->assign('newtopic', $newtopic);
        $render->assign('last_visit', $last_visit);
        $render->assign('last_visit_unix', $last_visit_unix);
        return $render->fetch('dizkus_user_newtopic.html');
    }
}

/**
 * editpost
 *
 */
function Dizkus_user_editpost($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $subject  = FormUtil::getPassedValue('subject', (isset($args['subject'])) ? $args['subject'] : '', 'GETPOST');
    $message  = FormUtil::getPassedValue('message', (isset($args['message'])) ? $args['message'] : '', 'GETPOST');
    $attach_signature = (int)FormUtil::getPassedValue('attach_signature', (isset($args['attach_signature'])) ? $args['attach_signature'] : 0, 'GETPOST');
    $delete   = FormUtil::getPassedValue('delete', (isset($args['delete'])) ? $args['delete'] : '', 'GETPOST');
    $preview  = FormUtil::getPassedValue('preview', (isset($args['preview'])) ? $args['preview'] : '', 'GETPOST');
    $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $cancel   = FormUtil::getPassedValue('cancel', (isset($args['cancel'])) ? $args['cancel'] : '', 'GETPOST');

    if (empty($post_id) || !is_numeric($post_id)) {
        return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
    }

    $post = pnModAPIFunc('Dizkus', 'user', 'readpost',
                         array('post_id' => $post_id));

    if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])
       && ($post['poster_data']['pn_uid'] <> pnUserGetVar('uid')) ) {
        return showforumerror(__('Sorry! You do not have authorisation for this action.', $dom), __FILE__, __LINE__);
    }

    $preview = (empty($preview)) ? false : true;

    //  if cancel is submitted move to forum-view
    if (!empty($cancel)) {
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
    }

    $message = dzkstriptags($message);
    // check for maximum message size
    if ((strlen($message) + 8/*strlen('[addsig]')*/) > 65535) {
        LogUtil::registerStatus(__('Error! Illegal message size. The maximum size of a post is 65,535 characters.', $dom));
        // switch to preview mode
        $preview = true;
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    if ($submit && !$preview) {

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }

        // store the new topic
        $redirect = pnModAPIFunc('Dizkus', 'user', 'updatepost',
                                 array('post_id'          => $post_id,
                                       'delete'           => $delete,
                                       'subject'          => $subject,
                                       'message'          => $message,
                                       'attach_signature' => ($attach_signature==1)));

        return pnRedirect($redirect);

    } else {
        if (!empty($subject)) {
            $post['topic_subject'] = strip_tags($subject);
        }

        // if the current user is the original poster we allow to
        // edit the subject
        $firstpost = pnModAPIFunc('Dizkus', 'user', 'get_firstlast_post_in_topic',
                                  array('topic_id' => $post['topic_id'],
                                        'first'    => true));

        if ($post['poster_data']['pn_uid'] == $firstpost['poster_data']['pn_uid']) {
            $post['edit_subject'] = true;
        }

        if (!empty($message)) {
            $post['post_rawtext'] = $message;
            list($post['post_textdisplay']) = pnModCallHooks('item', 'transform', '', array(nl2br($message)));
        }

        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('avatarpath', pnModGetVar('Users', 'avatarpath'));
        $render->assign('preview', $preview);
        $render->assign('post', $post);
        $render->assign('last_visit', $last_visit);
        $render->assign('last_visit_unix', $last_visit_unix);
        return $render->fetch('dizkus_user_editpost.html');
    }
}

/**
 * topicadmin
 *
 */
function Dizkus_user_topicadmin($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
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

    if (empty($topic_id) && !empty($post_id)) {
        $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_postid',
                                 array('post_id' => $post_id));
    }
    $topic = pnModAPIFunc('Dizkus', 'user', 'readtopic',
                          array('topic_id' => $topic_id,
                                'count'    => false));

    if ($topic['access_moderate'] <> true) {
        return showforumerror(__('Sorry! You do not have authorisation to moderate this forum or forum category.', $dom), __FILE__, __LINE__);
    }

    $render = & pnRender::getInstance('Dizkus', false, null, true);
    $render->assign('mode', $mode);
    $render->assign('topic_id', $topic_id);

    if (empty($submit)) {
        switch($mode)
        {
            case 'del':
            case 'delete':
                $templatename = 'dizkus_user_deletetopic.html';
                break;

            case 'move':
            case 'join':
                $render->assign('forums', pnModAPIFunc('Dizkus', 'user', 'readuserforums'));
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
                $render->assign('viewip', pnModAPIFunc('Dizkus', 'user', 'get_viewip_data', array('post_id' => $post_id)));
                $templatename = 'dizkus_user_viewip.html';
                break;

            default:
                return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
        }
        return $render->fetch($templatename);

    } else { // submit is set
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        switch($mode) {
            case 'del':
            case 'delete':
                $forum_id = pnModAPIFunc('Dizkus', 'user', 'deletetopic', array('topic_id' => $topic_id));
                return pnRedirect(pnModURL('Dizkus', 'user', 'viewforum', array('forum' => $forum_id)));
                break;

            case 'move':
                list($f_id, $c_id) = Dizkus_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id' => $topic_id));
                if ($forum_id == $f_id) {
                    return showforumerror(__('Error! The source forum cannot be the same as the target forum.', $dom), __FILE__, __LINE__);
                }
                if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod', $f_id, 'forum', __('Sorry! You do not have authorisation to moderate this forum or forum category.', $dom)), __FILE__, __LINE__);
                }
                pnModAPIFunc('Dizkus', 'user', 'movetopic',
                             array('topic_id' => $topic_id,
                                   'forum_id' => $forum_id,
                                   'shadow'   => $shadow ));
                break;

            case 'lock':
            case 'unlock':
                list($f_id, $c_id) = pnModAPIFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                  array('topic_id' => $topic_id));
                if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod', $f_id, 'forum', __('Sorry! You do not have authorisation to moderate this forum or forum category.', $dom)), __FILE__, __LINE__);
                }
                pnModAPIFunc('Dizkus', 'user', 'lockunlocktopic',
                             array('topic_id' => $topic_id,
                                   'mode'     => $mode));
                break;

            case 'sticky':
            case 'unsticky':
                list($f_id, $c_id) = pnModAPIFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                  array('topic_id' => $topic_id));
                if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod', $f_id, 'forum', __('Sorry! You do not have authorisation to moderate this forum or forum category.', $dom)), __FILE__, __LINE__);
                }
                pnModAPIFunc('Dizkus', 'user', 'stickyunstickytopic',
                             array('topic_id' => $topic_id,
                                   'mode'     => $mode));
                break;

            case 'join':
                $to_topic_id = (int)FormUtil::getPassedValue('to_topic_id', (isset($args['to_topic_id'])) ? $args['to_topic_id'] : null, 'GETPOST');
                if (!empty($to_topic_id) && ($to_topic_id == $topic_id)) {
                    // user wants to copy topic to itself
                    return showforumerror(__('Error! The source topic cannot be the same as the target topic.', $dom), __FILE__, __LINE__);
                }
                list($f_id, $c_id) = Dizkus_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id' => $to_topic_id));
                if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                    return showforumerror(getforumerror('auth_mod', $f_id, 'forum', __('Sorry! You do not have authorisation to moderate this forum or forum category.', $dom)), __FILE__, __LINE__);
                }
                pnModAPIFunc('Dizkus', 'user', 'jointopics',
                             array('from_topic_id' => $topic_id,
                                   'to_topic_id'   => $to_topic_id));

                return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $to_topic_id)));
                break;

            default:
        }
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
    }
}

/**
 * prefs
 *
 */
function Dizkus_user_prefs($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    if (!pnUserLoggedIn()) {
        return pnModFunc('Users', 'user', 'loginscreen', array('redirecttype' => 1));
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
            if (pnModGetVar('Dizkus', 'favorites_enabled')=='yes') {
                $return_to = (!empty($return_to))? $return_to : 'viewforum';
                pnModAPIFunc('Dizkus', 'user', 'add_favorite_forum',
                             array('forum_id' => $forum_id ));
                $params = array('forum' => $forum_id);
            }
            break;
        case 'remove_favorite_forum':
            if (pnModGetVar('Dizkus', 'favorites_enabled')=='yes') {
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
            if (pnModGetVar('Dizkus', 'favorites_enabled')=='yes') {
                $return_to = (!empty($return_to))? $return_to : 'main';
                $favorites = pnModAPIFunc('Dizkus', 'user', 'change_favorite_status');
                $params = array();
            }
            break;
        default:
            list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');
            $render = & pnRender::getInstance('Dizkus', false, null, true);
            $render->assign('last_visit', $last_visit);
            $render->assign('favorites_enabled', pnModGetVar('Dizkus', 'favorites_enabled'));
            $render->assign('last_visit_unix', $last_visit_unix);
            $render->assign('signaturemanagement', pnModGetVar('Dizkus','signaturemanagement'));
            $render->assign('ignorelist_handling', pnModGetVar('Dizkus','ignorelist_handling'));
            $render->assign('contactlist_available', pnModAvailable('ContactList'));
            $render->assign('post_order', strtolower(pnModAPIFunc('Dizkus','user','get_user_post_order')));
            $render->assign('tree', pnModAPIFunc('Dizkus', 'user', 'readcategorytree', array('last_visit' => $last_visit )));
            return $render->fetch('dizkus_user_prefs.html');
    }
    return pnRedirect(pnModURL('Dizkus', 'user', $return_to, $params));
}

/**
 * signature management
 * 
 */
function Dizkus_user_signaturemanagement()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    if (!pnUserLoggedIn()) {
        return pnModFunc('Users', 'user', 'loginscreen', array('redirecttype' => 1));
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
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    if (!pnUserLoggedIn()) {
        return pnModFunc('Users', 'user', 'loginscreen', array('redirecttype' => 1));
    }
    // Security check
    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT)) {
        return LogUtil::registerPermissionError();
    }

	// check for Contactlist module and admin settings
	$ignorelist_handling = pnModGetVar('Dizkus','ignorelist_handling');
	if (!pnModAvailable('ContactList') || ($ignorelist_handling == 'none')) {
	  	LogUtil::registerError(__('No ignorelist configuration possible', $dom));
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
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $topic_id      = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
    $emailsubject  = FormUtil::getPassedValue('emailsubject', (isset($args['emailsubject'])) ? $args['emailsubject'] : '', 'GETPOST');
    $message       = FormUtil::getPassedValue('message', (isset($args['message'])) ? $args['message'] : '', 'GETPOST');
    $sendto_email  = FormUtil::getPassedValue('sendto_email', (isset($args['sendto_email'])) ? $args['sendto_email'] : '', 'GETPOST');
    $submit        = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');

    if (!pnUserLoggedIn()) {
        return showforumerror(__('Error! You need to be logged-in to perform this action.'), __FILE__, __LINE__);
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    if (!empty($submit)) {
        if (!pnVarValidate($sendto_email, 'email')) {
            // Empty e-mail is checked here too
            $error_msg = DataUtil::formatForDisplay(__('Error! Either you did not enter an e-mail address or the e-mail address was invalid.', $dom));
            $sendto_email = '';
            unset($submit);
        } else if ($message == '') {
            $error_msg = DataUtil::formatForDisplay(__('Error! You did not enter a message to send.', $dom));
            unset($submit);
        } else if ($emailsubject == '') {
            $error_msg = DataUtil::formatForDisplay(__('Error! You did not enter a subject line for the e-mail message.', $dom));
            unset($submit);
        }
    }

    if (!empty($submit)) {
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }

        pnModAPIFunc('Dizkus', 'user', 'emailtopic',
                     array('sendto_email' => $sendto_email,
                           'message'      => $message,
                           'subject'      => $emailsubject));
        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
    } else {
        $topic = pnModAPIFunc('Dizkus', 'user', 'readtopic',
                              array('topic_id'   => $topic_id));
        $emailsubject = (!empty($emailsubject)) ? $emailsubject : $topic['topic_title'];
        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('topic', $topic);
        $render->assign('error_msg', $error_msg);
        $render->assign('sendto_email', $sendto_email);
        $render->assign('emailsubject', $emailsubject);
        $render->assign('message', DataUtil::formatForDisplay(__('Hello! I\'m sending you a link to a topic in the forums because I think it might interest you.', $dom)) ."\n\n" . pnModURL('Dizkus', 'user', 'viewtopic', array('topic'=>$topic_id), null, null, true));
        $render->assign( 'last_visit', $last_visit);
        $render->assign( 'last_visit_unix', $last_visit_unix);
        return $render->fetch('dizkus_user_emailtopic.html');
    }
}

/**
 * latest
 *
 */
function Dizkus_user_viewlatest($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    if (useragent_is_bot() == true) {
        return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
    }

    // get the input
    $selorder   = (int)FormUtil::getPassedValue('selorder', (isset($args['selorder'])) ? $args['selorder'] : 1, 'GETPOST');
    $nohours    = (int)FormUtil::getPassedValue('nohours', (isset($args['nohours'])) ? $args['nohours'] : null, 'GETPOST');
    $unanswered = (int)FormUtil::getPassedValue('unanswered', (isset($args['unanswered'])) ? $args['unanswered'] : 0, 'GETPOST');
    $amount     = (int)FormUtil::getPassedValue('amount', (isset($args['amount'])) ? $args['amount'] : null, 'GETPOST');

    if (!empty($amount) && !is_numeric($amount)) {
        unset($amount);
        }

    // maximum last 100 posts maybe shown
    if (isset($amount) && $amount>100) {
        $amount = 100;
        }

    if (!empty($amount)) {
        $selorder = 7;
        }

    if (!empty($nohours) && !is_numeric($nohours)) {
        unset($nohours);
    }

    // maximum two weeks back = 2 * 24 * 7 hours
    if (isset($nohours) && $nohours > 336) {
        $nohours = 336;
    }

    if (!empty($nohours)) {
        $selorder = 5;
    }

    list($last_visit, $last_visit_unix) = pnModAPIFunc('Dizkus', 'user', 'setcookies');

    list($posts, $m2fposts, $rssposts, $text) = pnModAPIFunc('Dizkus', 'user', 'get_latest_posts',
                                                             array('selorder'   => $selorder,
                                                                   'nohours'    => $nohours,
                                                                   'amount'     => $amount,
                                                                   'unanswered' => $unanswered,
                                                                   'last_visit' => $last_visit,
                                                                   'last_visit_unix' => $last_visit_unix));

    $render = & pnRender::getInstance('Dizkus', false, null, true);
    $render->assign('posts', $posts);
    $render->assign('m2fposts', $m2fposts);
    $render->assign('rssposts', $rssposts);
    $render->assign('text', $text);
    $render->assign('nohours', $nohours);
    $render->assign('last_visit', $last_visit);
    $render->assign('last_visit_unix', $last_visit_unix);
    $render->assign('numposts', pnModAPIFunc('Dizkus', 'user', 'boardstats',
                                            array('id'   => '0',
                                                  'type' => 'all' )));
    return $render->fetch('dizkus_user_latestposts.html');

}

/**
 * splittopic
 *
 */
function Dizkus_user_splittopic($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id    = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $newsubject = FormUtil::getPassedValue('newsubject', (isset($args['newsubject'])) ? $args['newsubject'] : '', 'GETPOST');
    $submit     = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');

    $post = pnModAPIFunc('Dizkus', 'user', 'readpost',
                         array('post_id' => $post_id));

    if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod',$post['forum_id'], 'forum', __('Sorry! You do not have authorisation to moderate this forum or forum category.', $dom)), __FILE__, __LINE__);
    }

    if (!empty($submit)) {
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
        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('post', $post);
        return $render->fetch('dizkus_user_splittopic.html');
    }
}

/**
 * print
 * prepare print view of the selected posting or topic
 *
 */
function Dizkus_user_print($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');

    if (useragent_is_bot() == true) {
        if ($post_id <> 0 ) {
            $topic_id = pnModAPIFunc('Dizkus', 'user', 'get_topicid_by_postid',
                                    array('post_id' => $post_id));
        }
        if (($topic_id <> 0) && ($topic_id<>false)) {
            return Dizkus_user_viewtopic(array('topic' => $topic_id,
                                                'start'   => 0));
        } else {
            return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
        }
    } else {
        $render = & pnRender::getInstance('Dizkus', false, null, true);
        if ($post_id<>0) {
            $post = pnModAPIFunc('Dizkus', 'user', 'readpost',
                                 array('post_id' => $post_id));
            $render->assign('post', $post);
            $render->assign('avatarpath', pnModGetVar('Users', 'avatarpath'));
            $output = $render->fetch('dizkus_user_printpost.html');
        } elseif ($topic_id<>0) {
            $topic = pnModAPIFunc('Dizkus', 'user', 'readtopic',
                                 array('topic_id'  => $topic_id,
                                       'complete' => true,
                                       'count' => false ));
            $render->assign('avatarpath', pnModGetVar('Users', 'avatarpath'));
            $render->assign('topic', $topic);
            $output = $render->fetch('dizkus_user_printtopic.html');
        } else {
            return pnRedirect(pnModURL('Dizkus', 'user', 'main'));
        }
        $lang = pnConfigGetVar('backend_language');
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
        echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"$lang\" xml:lang=\"$lang\">\n";
        echo "<head>\n";
        echo "<title>" . DataUtil::formatForDisplay($topic['topic_title']) . "</title>\n";
        echo "<link rel=\"stylesheet\" href=\"" . pnGetBaseURL() . "modules/Dizkus/pnstyle/style.css\" type=\"text/css\" />\n";
        echo "<link rel=\"stylesheet\" href=\"" . pnGetBaseURL() . "themes/" . pnUserGetTheme() . "/style/style.css\" type=\"text/css\" />\n";        
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
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    $submit = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    if (!$submit) {
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
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $to_topic = (int)FormUtil::getPassedValue('to_topic', (isset($args['to_topic'])) ? $args['to_topic'] : null, 'GETPOST');

    $post = pnModAPIFunc('Dizkus', 'user', 'readpost', array('post_id' => $post_id));

    if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod', $post['forum_id'], 'forum', __('Sorry! You do not have authorisation to moderate this forum or forum category.', $dom)), __FILE__, __LINE__);
    }

    if (!empty($submit)) {
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

        $start = $start - $start%pnModGetVar('Dizkus', 'posts_per_page', 15);

        return pnRedirect(pnModURL('Dizkus', 'user', 'viewtopic',
                                   array('topic' => $to_topic,
                                         'start' => $start)) . '#pid' . $post['post_id']);
    } else {
        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('post', $post);
        return $render->fetch('dizkus_user_movepost.html');
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
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id       = (int)FormUtil::getPassedValue('post_id', (isset($args['post_id'])) ? $args['post_id'] : null, 'GETPOST');
    $submit        = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $to_topic_id   = (int)FormUtil::getPassedValue('to_topic_id', (isset($args['to_topic_id'])) ? $args['to_topic_id'] : null, 'GETPOST');
    $from_topic_id = (int)FormUtil::getPassedValue('from_topic_id', (isset($args['from_topic_id'])) ? $args['from_topic_id'] : null, 'GETPOST');

    $post = pnModAPIFunc('Dizkus', 'user', 'readpost', array('post_id' => $post_id));

    if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod',$post['forum_id'], 'forum', __('Sorry! You do not have authorisation to moderate this forum or forum category.', $dom)), __FILE__, __LINE__);
    }

    if (!$submit) {
        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('post', $post);
        return $render->fetch('dizkus_user_jointopics.html');
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
 * @params to be documented :-)
 *
 */
function Dizkus_user_moderateforum($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
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

    if (!allowedtomoderatecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
        // user is not allowed to moderate this forum
        return showforumerror(getforumerror('auth_mod',$post['forum_id'], 'forum', __('Sorry! You do not have authorisation to moderate this forum or forum category.', $dom)), __FILE__, __LINE__);
    }


    // Submit isn't set'
    if (empty($submit)) {
        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('forum_id', $forum_id);
        $render->assign('mode',$mode);
        $render->assign('topic_ids', $topic_ids);
        $render->assign('last_visit', $last_visit);
        $render->assign('last_visit_unix', $last_visit_unix);
        $render->assign('forum',$forum);
        // For Movetopic
        $render->assign('forums', pnModAPIFunc('Dizkus', 'user', 'readuserforums'));
        return $render->fetch('dizkus_user_moderateforum.html');

    } else {
        // submit is set
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        if (count($topic_ids)<>0) {
            switch($mode) {
                case 'del':
                case 'delete':
                    foreach($topic_ids as $topic_id) {
                        $forum_id = pnModAPIFunc('Dizkus', 'user', 'deletetopic', array('topic_id'=>$topic_id));
                    }
                    break;
                case 'move':
                    if (empty($moveto)) {
                        return showforumerror(__('Error! You did not select a target forum for the move.', $dom), __FILE__, __LINE__);
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
                    if (empty($jointo)) {
                        return showforumerror(__('Error! You did not select a target topic for the join.', $dom), __FILE__, __LINE__);
                    }
                    if (in_array($jointo, $topic_ids)) {
                        // jointo, the target topic, is part of the topics to join
                        // we remove this to avoid a loop
                        $fliparray = array_flip($topic_ids);
                        unset($fliparray[$jointo]);
                        $topic_ids = array_flip($fliparray);
                    }
                    foreach($topic_ids as $from_topic_id) {
                        pnModAPIFunc('Dizkus', 'user', 'jointopics', array('from_topic_id' => $from_topic_id,
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
 * @params $post int post_id
 * @params $comment string comment of reporter
 *
 */
function Dizkus_user_report($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    // get the input
    $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
    $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    $comment  = FormUtil::getPassedValue('comment', (isset($args['comment'])) ? $args['comment'] : '', 'GETPOST');

    $post = pnModAPIFunc('Dizkus', 'user', 'readpost',
                         array('post_id' => $post_id));

    
    if (SecurityUtil::confirmAuthKey()) {
        $authkeycheck = true;
    } else {
        $authkeycheck = false;
    }

    // some spam checks:
    // - remove html and compare with original comment
    // - use censor and compare with original comment
    // if only one of this comparisons fails -> trash it, it is spam.
    if (!pnUserLoggedIn() && $authkeycheck == true ) {
        if ((strip_tags($comment) <> $comment) ||
           (pnVarCensor($comment) <> $comment)) {
            // possibly spam, stop now
            // get the users ip address and store it in pnTemp/Dizkus_spammers.txt
            dzk_blacklist();
            // set 403 header and stop
            header('HTTP/1.0 403 Forbidden');
            pnShutDown();
        }
    }
    
    if (!$submit) {
        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('post', $post);
        return $render->fetch('dizkus_user_notifymod.html');
    } else {   // submit is set
        if ($authkeycheck == false) {
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
 * @params
 *
 */
function Dizkus_user_topicsubscriptions($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        return $disabled;
    }

    if (!pnUserLoggedIn()) {
        return pnModFunc('Users', 'user', 'loginscreen', array('redirecttype' => 1));
    }

    // get the input
    $topic_id = FormUtil::getPassedValue('topic_id', (isset($args['topic_id'])) ? $args['topic_id'] : null, 'GETPOST');
    $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');

    if (!$submit) {
        $subscriptions = pnModAPIFunc('Dizkus', 'user', 'get_topic_subscriptions');
        $render = & pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('subscriptions', $subscriptions);
        return $render->fetch('dizkus_user_topicsubscriptions.html');
    } else {  // submit is set
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }
        if (is_array($topic_id) && (count($topic_id) > 0)) {
            for($i=0; $i<count($topic_id); $i++) {
                pnModAPIFunc('Dizkus', 'user', 'unsubscribe_topic', array('topic_id' => $topic_id[$i]));
            }
        }
        return pnRedirect(pnModURL('Dizkus', 'user', 'topicsubscriptions'));
    }
}
