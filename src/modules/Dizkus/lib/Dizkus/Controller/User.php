<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Controller_User extends Zikula_AbstractController
{
    public function postInitialize()
    {
        $this->view->setCaching(false)->add_core_data();
    }
    /**
     * main
     * show all categories and forums a user may see
     *
     * @params 'viewcat' int only expand the category, all others shall be hidden / collapsed
     */
    public function main($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
        
        $viewcat   =  (int)FormUtil::getPassedValue('viewcat', (isset($args['viewcat'])) ? $args['viewcat'] : -1, 'GETPOST');
        $favorites = (bool)FormUtil::getPassedValue('favorites', (isset($args['favorites'])) ? $args['favorites'] : false, 'GETPOST');
    
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
        $loggedIn = UserUtil::isLoggedIn();
    
        if (ModUtil::getVar('Dizkus', 'favorites_enabled') == 'yes') {
            if ($loggedIn && !$favorites) {
                $favorites = ModUtil::apiFunc('Dizkus', 'user', 'get_favorite_status');
            }
        }
        if ($loggedIn && $favorites) {
            $tree = ModUtil::apiFunc('Dizkus', 'user', 'getfavorites',
                                 array('user_id'    => (int)UserUtil::getVar('uid'),
                                       'last_visit' => $last_visit ));
        } else {
            $tree = ModUtil::apiFunc('Dizkus', 'user', 'readcategorytree',
                                 array('last_visit' => $last_visit ));
    
            if (ModUtil::getVar('Dizkus', 'slimforum') == 'yes') {
                // this needs to be in here because we want to display the favorites
                // not go to it if there is only one
                // check if we have one category and one forum only
                if (count($tree)==1) {
                    foreach ($tree as $catname => $forumarray) {
                        if (count($forumarray['forums']) == 1) {
                            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewforum', array('forum'=>$forumarray['forums'][0]['forum_id'])));
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
    
        $this->view->assign('favorites', $favorites);
        $this->view->assign('tree', $tree);
        $this->view->assign('view_category', $viewcat);
        $this->view->assign('view_category_data', $view_category_data);
        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);
        $this->view->assign('numposts', ModUtil::apiFunc('Dizkus', 'user', 'boardstats',
                                                array('id'   => '0',
                                                      'type' => 'all' )));
    
        return $this->view->fetch('user/main.tpl');
    }
    
    /**
     * viewforum
     * opens a forum and shows the last postings
     *
     * @params 'forum' int the forum id
     * @params 'start' int the posting to start with if on page 1+
     */
    public function viewforum($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        

        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $forum_id = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
        $start    = (int)FormUtil::getPassedValue('start', (isset($args['start'])) ? $args['start'] : 0, 'GETPOST');
    
        
        
         $subforums = DBUtil::selectObjectArray('dizkus_forums', $where = 'WHERE is_subforum ='.$forum_id );
         foreach ($subforums as $key => $subforum) {
             $subforums[$key]['new_posts'] = false;
             $subforums[$key]['last_post'] = '';
         }
         $this->view->assign('subforums', $subforums);
        
        
        
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
        $forum = ModUtil::apiFunc('Dizkus', 'user', 'readforum',
                              array('forum_id'        => $forum_id,
                                    'start'           => $start,
                                    'last_visit'      => $last_visit,
                                    'last_visit_unix' => $last_visit_unix));
    
        
        $this->view->assign('forum', $forum);
        $this->view->assign('hot_threshold', ModUtil::getVar('Dizkus', 'hot_threshold'));
        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);
        $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
        return $this->view->fetch('user/viewforum.tpl');
    }
    
    /**
     * viewtopic
     *
     */
    public function viewtopic($args=array())
    {        
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        
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
    
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc($this->name, 'user', 'setcookies');
    
        if (!empty($view) && ($view=='next' || $view=='previous')) {
            $topic_id = ModUtil::apiFunc($this->name, 'user', 'get_previous_or_next_topic_id',
                                     array('topic_id' => $topic_id,
                                           'view'     => $view));
            return System::redirect(ModUtil::url($this->name, 'user', 'viewtopic',
                                array('topic' => $topic_id)));
        }
    
        // begin patch #3494 part 2, credits to teb
        if (!empty($post_id) && is_numeric($post_id) && empty($topic_id)) {
            $topic_id = ModUtil::apiFunc($this->name, 'user', 'get_topicid_by_postid', array('post_id' => $post_id));
            if ($topic_id <> false) {
                // redirect instad of continue, better for SEO
                return System::redirect(ModUtil::url($this->name, 'user', 'viewtopic', 
                                           array('topic' => $topic_id)));
            }
        }
        // end patch #3494 part 2
    
        $topic = ModUtil::apiFunc($this->name, 'user', 'readtopic',
                              array('topic_id'   => $topic_id,
                                    'start'      => $start,
                                    'count'      => true));
    
        $this->view->assign('topic', $topic);
        $this->view->assign('post_count', count($topic['posts']));
        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);
        $this->view->assign('favorites', ModUtil::apifunc($this->name, 'user', 'get_favorite_status'));
    
        return $this->view->fetch('user/viewtopic.tpl');
    }
    
    /**
     * reply
     *
     */
    public function reply($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        
        
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
         * if cancel is submitted move to topic-view
         */
        if (!empty($cancel)) {
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
        }
    
        $preview = (empty($preview)) ? false : true;
        $submit  = (empty($submit))  ? false : true;
    
        $message = dzkstriptags($message);
        // check for maximum message size
        if ((strlen($message) +  strlen('[addsig]')) > 65535) {
            LogUtil::registerStatus($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
            // switch to preview mode
            $preview = true;
        }
    
        if ($submit == true && $preview == false) {
            // Confirm authorisation code
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
    
            // ContactList integration: Is the user ignored and allowed to write an answer to this topic?
            $topic = DBUtil::selectObjectByID('dizkus_topics',$topic_id,'topic_id');
            $ignorelist_setting = ModUtil::apiFunc('Dizkus','user','get_settings_ignorelist',array('uid' => $topic['topic_poster']));
            if (ModUtil::available('ContactList') && ($ignorelist_setting == 'strict') && (ModUtil::apiFunc('ContactList','user','isIgnored',array('uid' => (int)$topic['topic_poster'], 'iuid' => UserUtil::getVar('uid'))))) {
                return LogUtil::registerError($this->__('Error! The user who started this topic is ignoring you, and does not want you to be able to write posts under this topic. Please contact the topic originator for more information.'), null, ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
            }
    
            list($start,
                 $post_id ) = ModUtil::apiFunc('Dizkus', 'user', 'storereply',
                                           array('topic_id'         => $topic_id,
                                                 'message'          => $message,
                                                 'attach_signature' => $attach_signature,
                                                 'subscribe_topic'  => $subscribe_topic));
    
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                array('topic' => $topic_id,
                                      'start' => $start)) . '#pid' . $post_id);
        } else {
            list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $reply = ModUtil::apiFunc('Dizkus', 'user', 'preparereply',
                                  array('topic_id'   => $topic_id,
                                        'post_id'    => $post_id,
                                        'last_visit' => $last_visit,
                                        'reply_start'=> empty($message),
                                        'attach_signature' => $attach_signature,
                                        'subscribe_topic'  => $subscribe_topic));
            if ($preview == true) {
                $reply['message'] = dzkVarPrepHTMLDisplay($message);
//                list($reply['message_display']) = ModUtil::callHooks('item', 'transform', '', array($message));
                $reply['message_display'] = nl2br($reply['message_display']);
            }

            $this->view->assign('reply', $reply);
            $this->view->assign('preview', $preview);
            $this->view->assign('last_visit', $last_visit);
            $this->view->assign('last_visit_unix', $last_visit_unix);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
            return $this->view->fetch('user/reply.tpl');
        }
    }
    
    /**
     * newtopic
     *
     */
    public function newtopic($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $forum_id = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
        if ($forum_id == null) {
            return LogUtil::registerError($this->_('Error! Missing forum id.'), null, ModUtil::url('Dizkus','user', 'main'));
        }
        
        $subject  = FormUtil::getPassedValue('subject', (isset($args['subject'])) ? $args['subject'] : '', 'GETPOST');
        $message  = FormUtil::getPassedValue('message', (isset($args['message'])) ? $args['message'] : '', 'GETPOST');
        $attach_signature = (int)FormUtil::getPassedValue('attach_signature', (isset($args['attach_signature'])) ? $args['attach_signature'] : 0, 'GETPOST');
        $subscribe_topic = (int)FormUtil::getPassedValue('subscribe_topic', (isset($args['subscribe_topic'])) ? $args['subscribe_topic'] : 0, 'GETPOST');
        $preview  = FormUtil::getPassedValue('preview', (isset($args['preview'])) ? $args['preview'] : '', 'GETPOST');
        $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
        $cancel   = FormUtil::getPassedValue('cancel', (isset($args['cancel'])) ? $args['cancel'] : '', 'GETPOST');
    
        $preview = (empty($preview)) ? false : true;
        $cancel  = (empty($cancel))  ? false : true;
        $submit  = (empty($submit))  ? false : true;
    
        //  if cancel is submitted move to forum-view
        if ($cancel == true) {
            return System::redirect(ModUtil::url('Dizkus','user', 'viewforum', array('forum' => $forum_id)));
        }
    
        $message = dzkstriptags($message);
        // check for maximum message size
        if ((strlen($message) +  strlen('[addsig]')) > 65535) {
            LogUtil::registerStatus($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
            // switch to preview mode
            $preview = true;
        }
    
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
        $newtopic = ModUtil::apiFunc('Dizkus', 'user', 'preparenewtopic',
                                 array('forum_id'   => $forum_id,
                                       'subject'    => $subject,
                                       'message'    => $message,
                                       'topic_start'=> (empty($subject) && empty($message)),
                                       'attach_signature' => $attach_signature,
                                       'subscribe_topic'  => $subscribe_topic));
    
        if ($submit == true && $preview == false) {
            // it's a submitted page
            // Confirm authorisation code
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
    
            //store the new topic
            $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic',
                                     array('forum_id'         => $forum_id,
                                           'subject'          => $subject,
                                           'message'          => $message,
                                           'attach_signature' => $attach_signature,
                                           'subscribe_topic'  => $subscribe_topic));
    
            if (ModUtil::getVar('Dizkus', 'newtopicconfirmation') == 'yes') {
                $this->view->assign('topic', ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $topic_id, 'count' => false)));
    
                return $this->view->fetch('user/newtopicconfirmation.tpl');
    
            } else {
                return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                           array('topic' => $topic_id),
                                           null, null, true));
            }
        } else {
            // new topic
            $this->view->assign('preview', $preview);
            $this->view->assign('newtopic', $newtopic);
            $this->view->assign('last_visit', $last_visit);
            $this->view->assign('last_visit_unix', $last_visit_unix);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
            return $this->view->fetch('user/newtopic.tpl');
        }
    }
    
    /**
     * editpost
     *
     */
    public function editpost($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        
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
            return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                             array('post_id' => $post_id));
    
        if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])
           && ($post['poster_data']['uid'] <> UserUtil::getVar('uid')) ) {
            return LogUtil::registerPermissionError();
        }
    
        $preview = (empty($preview)) ? false : true;
    
        //  if cancel is submitted move to forum-view
        if (!empty($cancel)) {
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
        }
    
        $message = dzkstriptags($message);
        // check for maximum message size
        if ((strlen($message) + 8/*strlen('[addsig]')*/) > 65535) {
            LogUtil::registerStatus($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
            // switch to preview mode
            $preview = true;
        }
    
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
        if ($submit && !$preview) {
    
            // Confirm authorisation code
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
    
            // store the new topic
            $redirect = ModUtil::apiFunc('Dizkus', 'user', 'updatepost',
                                     array('post_id'          => $post_id,
                                           'topic_id'         => $topid_id,
                                           'delete'           => $delete,
                                           'subject'          => $subject,
                                           'message'          => $message,
                                           'attach_signature' => ($attach_signature==1)));
    
            return System::redirect($redirect);
    
        } else {
            if (!empty($subject)) {
                $post['topic_subject'] = strip_tags($subject);
            }
    
            // if the current user is the original poster we allow to
            // edit the subject
            $firstpost = ModUtil::apiFunc('Dizkus', 'user', 'get_firstlast_post_in_topic',
                                      array('topic_id' => $post['topic_id'],
                                            'first'    => true));
    
            if ($post['poster_data']['uid'] == $firstpost['poster_data']['uid']) {
                $post['edit_subject'] = true;
            }
    
            if (!empty($message)) {
                $post['post_rawtext'] = $message;
//                list($post['post_textdisplay']) = ModUtil::callHooks('item', 'transform', '', array(nl2br($message)));
            }
    
            $this->view->assign('preview', $preview);
            $this->view->assign('post', $post);
            $this->view->assign('last_visit', $last_visit);
            $this->view->assign('last_visit_unix', $last_visit_unix);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
            return $this->view->fetch('user/editpost.tpl');
        }
    }
    
    /**
     * topicadmin
     *
     */
    public function topicadmin($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
        $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
        $forum_id = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
        $mode     = FormUtil::getPassedValue('mode', (isset($args['mode'])) ? $args['mode'] : '', 'GETPOST');
        $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
        $shadow   = FormUtil::getPassedValue('createshadowtopic', (isset($args['createshadowtopic'])) ? $args['createshadowtopic'] : '', 'GETPOST');
        $shadow   = (empty($shadow)) ? false : true;
    
        if (empty($topic_id) && !empty($post_id)) {
            $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_postid',
                                     array('post_id' => $post_id));
        }
    
        $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic',
                              array('topic_id' => $topic_id,
                                    'count'    => false));
    
        if ($topic['access_moderate'] <> true) {
            return LogUtil::registerPermissionError();
        }
    
        $this->view->add_core_data();
        $this->view->setCaching(false);
        $this->view->assign('mode', $mode);
        $this->view->assign('topic_id', $topic_id);
        $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
        if (empty($submit)) {
            switch ($mode)
            {
                case 'del':
                case 'delete':
                    $templatename = 'user/deletetopic.tpl';
                    break;
    
                case 'move':
                case 'join':
                    $tree = ModUtil::apiFunc('Dizkus', 'user', 'readcategorytree');
                    $list = array();
                    foreach($tree as $categoryname => $category) {
                        foreach($category['forums'] as $forum) {
                            $list[$forum['forum_id']] = $categoryname . '::' . $forum['forum_name'];
                        }
                    }
                    $this->view->assign('forums', $list);

                    $templatename = 'user/movetopic.tpl';
                    break;
    
                case 'lock':
                case 'unlock':
                    $templatename = 'user/locktopic.tpl';
                    break;
    
                case 'sticky':
                case 'unsticky':
                    $templatename = 'user/stickytopic.tpl';
                    break;
    
                case 'viewip':
                    $this->view->assign('viewip', ModUtil::apiFunc('Dizkus', 'user', 'get_viewip_data', array('post_id' => $post_id)));
                    $templatename = 'user/viewip.tpl';
                    break;
    
                default:
                    return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
            }
            return $this->view->fetch($templatename);
    
        } else { // submit is set
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
    
            switch ($mode)
            {
                case 'del':
                case 'delete':
                    $forum_id = ModUtil::apiFunc('Dizkus', 'user', 'deletetopic', array('topic_id' => $topic_id));
                    return System::redirect(ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id)));
                    break;
    
                case 'move':
                    list($f_id, $c_id) = Dizkus_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id' => $topic_id));
                    if ($forum_id == $f_id) {
                        return LogUtil::registerError($this->__('Error! The original forum cannot be the same as the target forum.'));
                    }
                    if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                        return LogUtil::registerPermissionError();
                    }
                    ModUtil::apiFunc('Dizkus', 'user', 'movetopic',
                                 array('topic_id' => $topic_id,
                                       'forum_id' => $forum_id,
                                       'shadow'   => $shadow ));
                    break;
    
                case 'lock':
                case 'unlock':
                    list($f_id, $c_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                      array('topic_id' => $topic_id));
                    if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                        return LogUtil::registerPermissionError();
                    }
                    ModUtil::apiFunc('Dizkus', 'user', 'lockunlocktopic',
                                 array('topic_id' => $topic_id,
                                       'mode'     => $mode));
                    break;
    
                case 'sticky':
                case 'unsticky':
                    list($f_id, $c_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                      array('topic_id' => $topic_id));
                    if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                        return LogUtil::registerPermissionError();
                    }
                    ModUtil::apiFunc('Dizkus', 'user', 'stickyunstickytopic',
                                 array('topic_id' => $topic_id,
                                       'mode'     => $mode));
                    break;
    
                case 'join':
                    $to_topic_id = (int)FormUtil::getPassedValue('to_topic_id', (isset($args['to_topic_id'])) ? $args['to_topic_id'] : null, 'GETPOST');
                    list($f_id, $c_id) = Dizkus_userapi_get_forumid_and_categoryid_from_topicid(array('topic_id' => $to_topic_id));
                    if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                        return LogUtil::registerPermissionError();
                    }

                    if (!empty($to_topic_id) && ($to_topic_id == $topic_id)) {
                        // user wants to copy topic to itself
                        return LogUtil::registerError($this->__('Error! The original topic cannot be the same as the target topic.'), null, ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $f_id)));
                    }
                    ModUtil::apiFunc('Dizkus', 'user', 'jointopics',
                                 array('from_topic_id' => $topic_id,
                                       'to_topic_id'   => $to_topic_id));
    
                    return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $to_topic_id)));
                    break;
    
                default:
            }
    
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
        }
    }
    
    /**
     * prefs
     *
     */
    public function prefs($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        if (!UserUtil::isLoggedIn()) {
            return ModUtil::func('Users', 'user', 'loginscreen', array('redirecttype' => 1));
        }
    
        // get the input
        $topic_id  = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
        $act       = FormUtil::getPassedValue('act', (isset($args['act'])) ? $args['act'] : '', 'GETPOST');
        $return_to = FormUtil::getPassedValue('return_to', (isset($args['return_to'])) ? $args['return_to'] : '', 'GETPOST');
        $forum_id  = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
        $user_id   = (int)FormUtil::getPassedValue('user', (isset($args['user'])) ? $args['user'] : null, 'GETPOST');
    
        // user_id will only be used if we have admin permissions otherwise the
        // user can edit his prefs only but not others users prefs
    
        switch ($act)
        {
            case 'subscribe_topic':
                $return_to = (!empty($return_to))? $return_to : 'viewtopic';
                ModUtil::apiFunc('Dizkus', 'user', 'subscribe_topic',
                             array('topic_id' => $topic_id ));
                $params = array('topic' => $topic_id);
                break;
    
            case 'unsubscribe_topic':
                $return_to = (!empty($return_to))? $return_to : 'viewtopic';
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_topic',
                             array('topic_id' => $topic_id ));
                $params = array('topic' => $topic_id);
                break;
    
            case 'subscribe_forum':
                $return_to = (!empty($return_to))? $return_to : 'viewforum';
                ModUtil::apiFunc('Dizkus', 'user', 'subscribe_forum',
                             array('forum_id' => $forum_id ));
                $params = array('forum' => $forum_id);
                break;
    
            case 'unsubscribe_forum':
                $return_to = (!empty($return_to))? $return_to : 'viewforum';
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_forum',
                             array('forum_id' => $forum_id ));
                $params = array('forum' => $forum_id);
                break;
    
            case 'add_favorite_forum':
                if (ModUtil::getVar('Dizkus', 'favorites_enabled')=='yes') {
                    $return_to = (!empty($return_to))? $return_to : 'viewforum';
                    ModUtil::apiFunc('Dizkus', 'user', 'add_favorite_forum',
                                 array('forum_id' => $forum_id ));
                    $params = array('forum' => $forum_id);
                }
                break;
    
            case 'remove_favorite_forum':
                if (ModUtil::getVar('Dizkus', 'favorites_enabled')=='yes') {
                    $return_to = (!empty($return_to))? $return_to : 'viewforum';
                    ModUtil::apiFunc('Dizkus', 'user', 'remove_favorite_forum',
                                 array('forum_id' => $forum_id ));
                    $params = array('forum' => $forum_id);
                }
                break;
    
            case 'change_post_order':
                $return_to = (!empty($return_to))? $return_to : 'viewtopic';
                ModUtil::apiFunc('Dizkus', 'user', 'change_user_post_order');
                $params = array('topic' => $topic_id);
                break;
    
            case 'showallforums':
            case 'showfavorites':
                if (ModUtil::getVar('Dizkus', 'favorites_enabled')=='yes') {
                    $return_to = (!empty($return_to))? $return_to : 'prefs';
                    $favorites = ModUtil::apiFunc('Dizkus', 'user', 'change_favorite_status');
                    $params = array();
                }
                break;

            case 'noautosubscribe':
            case 'autosubscribe':
                $return_to = (!empty($return_to))? $return_to : 'prefs';
                $nm = ModUtil::apiFunc('Dizkus', 'user', 'togglenewtopicsubscription');
                $params = array();
                break;
    
            default:
                list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
                $this->view->assign('last_visit', $last_visit);
                $this->view->assign('autosubscribe', (bool)UserUtil::getVar('dizkus_autosubscription', -1, 0));
                $this->view->assign('favorites_enabled', ModUtil::getVar('Dizkus', 'favorites_enabled'));
                $this->view->assign('last_visit_unix', $last_visit_unix);
                $this->view->assign('signaturemanagement', ModUtil::getVar('Dizkus','signaturemanagement'));
                $this->view->assign('ignorelist_handling', ModUtil::getVar('Dizkus','ignorelist_handling'));
                $this->view->assign('contactlist_available', ModUtil::available('ContactList'));
                $this->view->assign('post_order', strtolower(ModUtil::apiFunc('Dizkus','user','get_user_post_order')));
                $this->view->assign('favorites', ModUtil::apiFunc('Dizkus','user','get_favorite_status'));
                $this->view->assign('tree', ModUtil::apiFunc('Dizkus', 'user', 'readcategorytree', array('last_visit' => $last_visit )));
    
                return $this->view->fetch('user/prefs.tpl');
        }
    
        return System::redirect(ModUtil::url('Dizkus', 'user', $return_to, $params));
    }
    
    /**
     * signature management
     * 
     */
    public function signaturemanagement()
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        if (!UserUtil::isLoggedIn()) {
            return ModUtil::func('Users', 'user', 'loginscreen', array('redirecttype' => 1));
        }
        // Security check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT) || (!(ModUtil::getVar('Dizkus','signaturemanagement') == 'yes'))) {
            return LogUtil::registerPermissionError();
        }
    
        // Create output and assign data
        $form = FormUtil::newForm($this->name, $this);
    
        // Return the output
        return $form->execute('user/signaturemanagement.tpl', new Dizkus_Form_Handler_User_SignatureManagement());
    }
    
    /**
     * ignorelist management
     * 
     */
    public function ignorelistmanagement()
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        if (!UserUtil::isLoggedIn()) {
            return ModUtil::func('Users', 'user', 'loginscreen', array('redirecttype' => 1));
        }
        // Security check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT)) {
            return LogUtil::registerPermissionError();
        }
    
    	// check for Contactlist module and admin settings
    	$ignorelist_handling = ModUtil::getVar('Dizkus','ignorelist_handling');
    	if (!ModUtil::available('ContactList') || ($ignorelist_handling == 'none')) {
    	  	return LogUtil::registerError($this->__("No 'ignore list' configuration is currently possible."), null, ModUtil::url('Dizkus', 'user', 'prefs'));
    	}
    
        // Create output and assign data
        $render = FormUtil::newForm($this->name, $this);
    
        // Return the output
        return $render->execute('user/ignorelistmanagement.tpl', new Dizkus_Form_Handler_User_IgnoreListManagement());
    }
    
    /**
     * emailtopic
     */
    public function emailtopic($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
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
    
        if (!UserUtil::isLoggedIn()) {
            return LogUtil::registerError($this->__('Error! You need to be logged-in to perform this action.'), null, ModUtil::url('Users', 'user', 'login'));
        }
    
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
        if (!empty($submit)) {
            if (!System::varValidate($sendto_email, 'email')) {
                // Empty e-mail is checked here too
                $error_msg = DataUtil::formatForDisplay($this->__('Error! Either you did not enter an e-mail address for the recipient, or the e-mail address you entered was invalid.'));
                $sendto_email = '';
                unset($submit);
            } else if ($message == '') {
                $error_msg = DataUtil::formatForDisplay($this->__('Error! You must enter a message.'));
                unset($submit);
            } else if ($emailsubject == '') {
                $error_msg = DataUtil::formatForDisplay($this->__('Error! You must enter a subject line for the e-mail message.'));
                unset($submit);
            }
        } else {
            $error_msg = null;
        }
    
        if (!empty($submit)) {
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
    
            ModUtil::apiFunc('Dizkus', 'user', 'emailtopic',
                         array('sendto_email' => $sendto_email,
                               'message'      => $message,
                               'subject'      => $emailsubject));
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
        } else {
            $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic',
                                  array('topic_id'   => $topic_id));
    
            $emailsubject = (!empty($emailsubject)) ? $emailsubject : $topic['topic_title'];
    
            $this->view->assign('topic', $topic);
            $this->view->assign('error_msg', $error_msg);
            $this->view->assign('sendto_email', $sendto_email);
            $this->view->assign('emailsubject', $emailsubject);
            $this->view->assign('message', DataUtil::formatForDisplay($this->__('Hello! Please visit this link. I think it will be of interest to you.')) ."\n\n" . ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic'=>$topic_id), null, null, true));
            $this->view->assign('last_visit', $last_visit);
            $this->view->assign('last_visit_unix', $last_visit_unix);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
            return $this->view->fetch('user/emailtopic.tpl');
        }
    }
    
    /**
     * latest
     */
    public function viewlatest($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        if (useragent_is_bot() == true) {
            return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
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
    
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
        list($posts, $m2fposts, $rssposts, $text) = ModUtil::apiFunc('Dizkus', 'user', 'get_latest_posts',
                                                                 array('selorder'   => $selorder,
                                                                       'nohours'    => $nohours,
                                                                       'amount'     => $amount,
                                                                       'unanswered' => $unanswered,
                                                                       'last_visit' => $last_visit,
                                                                       'last_visit_unix' => $last_visit_unix));
    
        $this->view->assign('posts', $posts);
        $this->view->assign('m2fposts', $m2fposts);
        $this->view->assign('rssposts', $rssposts);
        $this->view->assign('text', $text);
        $this->view->assign('nohours', $nohours);
        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);
        $this->view->assign('numposts', ModUtil::apiFunc('Dizkus', 'user', 'boardstats',
                                                array('id'   => '0',
                                                      'type' => 'all' )));
        $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
        return $this->view->fetch('user/latestposts.tpl');
    }
    
    /**
     * splittopic
     *
     */
    public function splittopic($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $post_id    = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
        $newsubject = FormUtil::getPassedValue('newsubject', (isset($args['newsubject'])) ? $args['newsubject'] : '', 'GETPOST');
        $submit     = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    
        $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                             array('post_id' => $post_id));
    
        if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
        if (!empty($submit)) {
            // Confirm authorisation code
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
            // submit is set, we split the topic now
            $post['topic_subject'] = $newsubject;
    
            $newtopic_id = ModUtil::apiFunc('Dizkus', 'user', 'splittopic',
                                       array('post' => $post));
    
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                       array('topic' => $newtopic_id)));
    
        } else {
            $this->view->assign('post', $post);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
            return $this->view->fetch('user/splittopic.tpl');
        }
    }
    
    /**
     * print
     * prepare print view of the selected posting or topic
     *
     */
    public function printtopic($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
        $topic_id = (int)FormUtil::getPassedValue('topic', (isset($args['topic'])) ? $args['topic'] : null, 'GETPOST');
    
        if (useragent_is_bot() == true) {
            if ($post_id <> 0 ) {
                $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_postid',
                                        array('post_id' => $post_id));
            }
            if (($topic_id <> 0) && ($topic_id<>false)) {
                return $this->viewtopic(array('topic' => $topic_id,
                                                    'start'   => 0));
            } else {
                return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
            }
        } else {
            $this->view->add_core_data();
            $this->view->setCaching(false);
            if ($post_id <> 0) {
                $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                     array('post_id' => $post_id));
    
                $this->view->assign('post', $post);
    
                $output = $this->view->fetch('user/printpost.tpl');
            } elseif ($topic_id <> 0) {
                $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic',
                                     array('topic_id'  => $topic_id,
                                           'complete' => true,
                                           'count' => false ));
    
                $this->view->assign('topic', $topic);
    
                $output = $this->view->fetch('user/printtopic.tpl');
            } else {
                return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
            }
    
            // FIXME backend_language is deprecated?
            $lang = System::getVar('backend_language');
            echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
            echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"$lang\" xml:lang=\"$lang\">\n";
            echo "<head>\n";
            echo "<title>" . DataUtil::formatForDisplay($topic['topic_title']) . "</title>\n";
            echo "<link rel=\"stylesheet\" href=\"" . System::getBaseUrl() . "modules/Dizkus/style/style.css\" type=\"text/css\" />\n";
            echo "<link rel=\"stylesheet\" href=\"" . System::getBaseUrl() . "themes/" . UserUtil::getTheme() . "/style/style.css\" type=\"text/css\" />\n";        
            echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
    
            global $additional_header;
            if (is_array($additional_header)) {
                foreach ($additional_header as $header) {
                    echo "$header\n";
                }
            }
            echo "</head>\n";
            echo "<body class=\"printbody\">\n";
            echo $output;
            echo "</body>\n";
            echo "</html>\n";
            System::shutDown();
        }
    }
    
    /**
     * movepost
     * Move a single post to another thread
     * added by by el_cuervo -- dev-postnuke.com
     */
    public function movepost($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
        $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
        $to_topic = (int)FormUtil::getPassedValue('to_topic', (isset($args['to_topic'])) ? $args['to_topic'] : null, 'GETPOST');
    
        $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost', array('post_id' => $post_id));
    
        if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
        if (!empty($submit)) {
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
            // submit is set, we move the posting now
            // Existe el Topic ? --- Exists new Topic ?
            $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $to_topic,
                                                                        'complete' => false,
                                                                        'count' => false));
            $post['new_topic'] = $to_topic;
            $post['old_topic'] = $topic['topic_id'];
    
            $start = ModUtil::apiFunc('Dizkus', 'user', 'movepost', array('post'     => $post,
                                                                      'to_topic' => $to_topic));
    
            $start = $start - $start%ModUtil::getVar('Dizkus', 'posts_per_page', 15);
    
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                       array('topic' => $to_topic,
                                             'start' => $start)) . '#pid' . $post['post_id']);
        } else {
            $this->view->assign('post', $post);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
            return $this->view->fetch('user/movepost.tpl');
        }
    }
    
    /**
     * jointopics
     * Join a topic with another toipic                                                                                                  ?>
     * by el_cuervo -- dev-postnuke.com
     *
     */
    public function jointopics($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $post_id       = (int)FormUtil::getPassedValue('post_id', (isset($args['post_id'])) ? $args['post_id'] : null, 'GETPOST');
        $submit        = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
        $to_topic_id   = (int)FormUtil::getPassedValue('to_topic_id', (isset($args['to_topic_id'])) ? $args['to_topic_id'] : null, 'GETPOST');
        $from_topic_id = (int)FormUtil::getPassedValue('from_topic_id', (isset($args['from_topic_id'])) ? $args['from_topic_id'] : null, 'GETPOST');
    
        $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost', array('post_id' => $post_id));
    
        if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
        if (!$submit) {
            $this->view->assign('post', $post);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
            return $this->view->fetch('user/jointopics.tpl');
    
        } else {
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
    
            // check if from_topic exists. this function will return an error if not
            $from_topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $from_topic_id, 'complete' => false, 'count' => false));
            // check if to_topic exists. this function will return an error if not
            $to_topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $to_topic_id, 'complete' => false, 'count' => false));
            // submit is set, we split the topic now
            //$post['new_topic'] = $totopic;
            //$post['old_topic'] = $old_topic;
            $res = ModUtil::apiFunc('Dizkus', 'user', 'jointopics', array('from_topic' => $from_topic,
                                                                       'to_topic'   => $to_topic));
    
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $res)));
        }
    }
    
    /**
     * moderateforum
     * simple moderation of multiple topics
     *
     * @params to be documented :-)
     *
     */
    public function moderateforum($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $forum_id  = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
        $start     = (int)FormUtil::getPassedValue('start', (isset($args['start'])) ? $args['start'] : 0, 'GETPOST');
        $mode      = FormUtil::getPassedValue('mode', (isset($args['mode'])) ? $args['mode'] : '', 'GETPOST');
        $submit    = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
        $topic_ids = FormUtil::getPassedValue('topic_id', (isset($args['topic_id'])) ? $args['topic_id'] : array(), 'GETPOST');
        $shadow    = FormUtil::getPassedValue('createshadowtopic', (isset($args['createshadowtopic'])) ? $args['createshadowtopic'] : '', 'GETPOST');
        $moveto    = (int)FormUtil::getPassedValue('moveto', (isset($args['moveto'])) ? $args['moveto'] : null, 'GETPOST');
        $jointo    = (int)FormUtil::getPassedValue('jointo', (isset($args['jointo'])) ? $args['jointo'] : null, 'GETPOST');
    
        $shadow = (empty($shadow)) ? false : true;
    
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
        // Get the Forum for Display and Permission-Check
        $forum = ModUtil::apiFunc('Dizkus', 'user', 'readforum',
                              array('forum_id'        => $forum_id,
                                    'start'           => $start,
                                    'last_visit'      => $last_visit,
                                    'last_visit_unix' => $last_visit_unix));
    
        if (!allowedtomoderatecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
    
        // Submit isn't set'
        if (empty($submit)) {
            $this->view->assign('forum_id', $forum_id);
            $this->view->assign('mode',$mode);
            $this->view->assign('topic_ids', $topic_ids);
            $this->view->assign('last_visit', $last_visit);
            $this->view->assign('last_visit_unix', $last_visit_unix);
            $this->view->assign('forum',$forum);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
            // For Movetopic
            $this->view->assign('forums', ModUtil::apiFunc('Dizkus', 'user', 'readuserforums'));
    
            return $this->view->fetch('user/moderateforum.tpl');
    
        } else {
            // submit is set
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
            if (count($topic_ids) <> 0) {
                switch ($mode)
                {
                    case 'del':
                    case 'delete':
                        foreach ($topic_ids as $topic_id) {
                            $forum_id = ModUtil::apiFunc('Dizkus', 'user', 'deletetopic', array('topic_id' => $topic_id));
                        }
                        break;
    
                    case 'move':
                        if (empty($moveto)) {
                            return LogUtil::registerError($this->__('Error! You did not select a target forum for the move.'), null, ModUtil::url('Dizkus', 'user', 'moderateforum', array('forum' => $forum_id)));
                        }
                        foreach ($topic_ids as $topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'movetopic',
                                         array('topic_id' => $topic_id,
                                               'forum_id' => $moveto,
                                               'shadow'   => $shadow ));
                        }
                        break;
    
                    case 'lock':
                    case 'unlock':
                        foreach ($topic_ids as $topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                        }
                        break;
    
                    case 'sticky':
                    case 'unsticky':
                        foreach ($topic_ids as $topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'stickyunstickytopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                        }
                        break;
    
                    case 'join':
                        if (empty($jointo)) {
                            return LogUtil::registerError($this->__('Error! You did not select a target topic to join.'), null, ModUtil::url('Dizkus', 'user', 'moderateforum', array('forum' => $forum_id)));
                        }
                        if (in_array($jointo, $topic_ids)) {
                            // jointo, the target topic, is part of the topics to join
                            // we remove this to avoid a loop
                            $fliparray = array_flip($topic_ids);
                            unset($fliparray[$jointo]);
                            $topic_ids = array_flip($fliparray);
                        }
                        foreach ($topic_ids as $from_topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'jointopics', array('from_topic_id' => $from_topic_id,
                                                                                'to_topic_id'   => $jointo));
                        }
                        break;
    
                    default:
                }
    
                // Refresh Forum Info
                $forum = ModUtil::apiFunc('Dizkus', 'user', 'readforum',
                                  array('forum_id'        => $forum_id,
                                        'start'           => $start,
                                        'last_visit'      => $last_visit,
                                        'last_visit_unix' => $last_visit_unix));
            }
        }
    
        return System::redirect(ModUtil::url('Dizkus', 'user', 'moderateforum', array('forum' => $forum_id)));
    }
    
    /**
     * report
     * notify a moderator about a posting
     *
     * @params $post int post_id
     * @params $comment string comment of reporter
     */
    public function report($args)
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $post_id  = (int)FormUtil::getPassedValue('post', (isset($args['post'])) ? $args['post'] : null, 'GETPOST');
        $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
        $comment  = FormUtil::getPassedValue('comment', (isset($args['comment'])) ? $args['comment'] : '', 'GETPOST');
    
        $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                             array('post_id' => $post_id));
    
        //if (SecurityUtil::confirmAuthKey()) {
            $authkeycheck = true;
        /*} else {
            $authkeycheck = false;
        }*/
    
        // some spam checks:
        // - remove html and compare with original comment
        // - use censor and compare with original comment
        // if only one of this comparisons fails -> trash it, it is spam.
        if (!UserUtil::isLoggedIn() && $authkeycheck == true ) {
            if (strip_tags($comment) <> $comment) {
                // possibly spam, stop now
                // get the users ip address and store it in zTemp/Dizkus_spammers.txt
                dzk_blacklist();
                // set 403 header and stop
                header('HTTP/1.0 403 Forbidden');
                System::shutDown();
            }
        }
    
        if (!$submit) {
            $this->view->assign('post', $post);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
            return $this->view->fetch('user/notifymod.tpl');
    
        } else {
            // submit is set
            if ($authkeycheck == false) {
                return LogUtil::registerAuthidError();
            }
    
            ModUtil::apiFunc('Dizkus', 'user', 'notify_moderator',
                         array('post'    => $post,
                               'comment' => $comment));
    
            $start = ModUtil::apiFunc('Dizkus', 'user', 'get_page_from_topic_replies',
                                  array('topic_replies' => $post['topic_replies']));
    
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                       array('topic' => $post['topic_id'],
                                             'start' => $start)));
        }
    }
    
    /**
     * topicsubscriptions
     * manage the users topic subscription
     */
    public function topicsubscriptions($args)
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        if (!UserUtil::isLoggedIn()) {
            return ModUtil::func('Users', 'user', 'loginscreen', array('redirecttype' => 1));
        }
    
        // get the input
        $topic_id = FormUtil::getPassedValue('topic_id', (isset($args['topic_id'])) ? $args['topic_id'] : null, 'GETPOST');
        $submit   = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
    
        if (!$submit) {
            $subscriptions = ModUtil::apiFunc('Dizkus', 'user', 'get_topic_subscriptions');
            $this->view->assign('subscriptions', $subscriptions);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
            return $this->view->fetch('user/topicsubscriptions.tpl');
    
        } else {
            // submit is set
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
    
            if (is_array($topic_id) && (count($topic_id) > 0)) {
                for ($i = 0; $i < count($topic_id); $i++) {
                    ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_topic', array('topic_id' => $topic_id[$i]));
                }
            }
    
            return System::redirect(ModUtil::url('Dizkus', 'user', 'topicsubscriptions'));
        }
    }

}