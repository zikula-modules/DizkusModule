<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * Ajax controller functions.
 */
class Dizkus_Controller_Ajax extends Zikula_AbstractController
{
    /**
     * reply
     *
     * @return string
     */
    public function reply()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }
        
        $topic_id         = $this->request->request->get('topic', null);
        $message          = $this->request->request->get('message', '');
        $title            = $this->request->request->get('title', '');;
        $attach_signature = $this->request->request->get('topic', attach_signature);
        $subscribe_topic  = $this->request->request->get('subscribe_topic', null);
        $preview          = $this->request->request->get('preview', 0);
        $preview          = ($preview == '1') ? true : false;

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        $message = dzkstriptags($message);
        $title   = dzkstriptags($title);

        // ContactList integration: Is the user ignored and allowed to write an answer to this topic?        
        $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopci0', $topic_id);

        $topic['start'] = 0;
        $ignorelist_setting = ModUtil::apiFunc('Dizkus', 'user', 'get_settings_ignorelist', array('uid' => $topic['topic_poster']));
        if (ModUtil::available('ContactList') && ($ignorelist_setting == 'strict') && (ModUtil::apiFunc('ContactList', 'user', 'isIgnored', array('uid' => (int)$topic['topic_poster'], 'iuid' => UserUtil::getVar('uid'))))) {
            return new Zikula_Response_Ajax_Fatal(
                array(),
                $this->__('Error! The user who started this topic is ignoring you, and does not want you to be able to write posts under this topic. Please contact the topic originator for more information.')
            );
        }

        // check for maximum message size (strlen('[addsig]')=8)
        if ((strlen($message) + 8) > 65535) {
            return new Zikula_Response_Ajax_BadData(
                array(),
                $this->__('Error! The message is too long. The maximum length is 65,535 characters.')
            );
        }

        if ($preview == false) {
            //if (!SecurityUtil::confirmAuthKey()) {
            //	LogUtil::registerAuthidError();
            //	throw new Zikula_Exception_Fatal();
            //}

            list($start, $post_id) = ModUtil::apiFunc('Dizkus', 'user', 'storereply',
                                        array(
                                            'topic_id'         => $topic_id,
                                            'message'          => $message,
                                            'attach_signature' => $attach_signature,
                                            'subscribe_topic'  => $subscribe_topic,
                                            'title'            => $title)
                                        );
            
            if (!isset($post_id)) {
                return new Zikula_Response_Ajax_BadData(array());
                $error = '<p class="z-errormsg">'.$this->__('Error! Your post contains unacceptable content and has been rejected.').'</p>';
                return new Zikula_Response_Ajax(array('data' => $error));
            }

            $topic['start'] = $start;
            $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost', array('post_id' => $post_id));

        } else {
            // preview == true, create fake post
            $post['post_id']         = 0;
            $post['topic_id']        = $topic_id;
            $post['poster_data']     = ModUtil::apiFunc('Dizkus', 'user', 'get_userdata_from_id', array('userid' => UserUtil::getVar('uid')));
            // create unix timestamp
            $post['post_unixtime']   = time();
            $post['posted_unixtime'] = $post['post_unixtime'];

            $post['post_title'] = $title;
            $post['post_textdisplay'] = phpbb_br2nl($message);
            if ($attach_signature == 1) {
                $post['post_textdisplay'] .= '[addsig]';
                $post['post_textdisplay'] = dzk_replacesignature($post['post_textdisplay'], $post['poster_data']['signature']);
            }
            // call hooks for $message_display ($message remains untouched for the textarea)
            // list($post['post_textdisplay']) = ModUtil::callHooks('item', 'transform', $post['post_id'], array($post['post_textdisplay']));
            $post['post_textdisplay']       = dzkVarPrepHTMLDisplay($post['post_textdisplay']);

            $post['post_text'] = $post['post_textdisplay'];
        }

        $this->view->add_core_data();
        $this->view->setCaching(false);
        $this->view->assign('topic', $topic);
        $this->view->assign('post', $post);
        $this->view->assign('preview', $preview);

        //---- begin of MediaAttach integration ----
        //if (ModUtil::available('MediaAttach') && ModUtil::isHooked('MediaAttach', 'Dizkus')) {
        //	return new Zikula_Response_Ajax(array('data'    => $this->view->fetch('user/singlepost.tpl'),
        //                           		          'post_id' => $post['post_id'],
        //                            			  'uploadauthid' => SecurityUtil::generateCsrfToken('MediaAttach')));
        //
        //} else {
        return new Zikula_Response_Ajax(
            array('data' => $this->view->fetch('user/post/single.tpl'),
            'post_id' => $post['post_id'])
        );
        //}
        //---- end of MediaAttach integration ----
    }

    /**
     * preparequote
     *
     * @return string
     */
    public function preparequote()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        $post_id = $this->request->request->get('post', null);

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($post_id)) {
            $post = ModUtil::apiFunc('Dizkus', 'user', 'preparereply',
                                 array('post_id'     => $post_id,
                                       'quote'       => true,
                                       'reply_start' => true));
            return new Zikula_Response_Ajax($post);
        }

        return new Zikula_Response_Ajax_Fatal(array(), $this->__('Error! No post ID in \'Dizkus_ajax_preparequote()\'.'));
    }

    /**
     * readpost
     */
    public function readpost()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $post_id = FormUtil::getPassedValue('post', null, 'POST');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($post_id)) {
            $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                 array('post_id'     => $post_id));
            if ($post['poster_data']['edit'] == true) {
                AjaxUtil::output($post, true, false, false);
            } else {
                LogUtil::registerPermissionError();
                return AjaxUtil::error(null, array(), true, true, '400 Bad Data');
            }
        }

        return AjaxUtil::error($this->__('Error! No post ID in \'Dizkus_ajax_readpost()\'.'), array(), true, true, '400 Bad Data');
    }

    /**
     * editpost
     */
    public function editpost()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        $post_id = $this->request->request->get('post', null, 'POST');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($post_id)) {
            $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                 array('post_id'     => $post_id));

            if ($post['poster_data']['edit'] == true) {
                //$this->view->add_core_data();
                $this->view->setCaching(false);

                $this->view->assign('post', $post);
                // simplify our live
                $this->view->assign('postingtextareaid', 'postingtext_' . $post['post_id'] . '_edit');

                SessionUtil::delVar('zk_ajax_call');

                return new Zikula_Response_Ajax(array('data' => $this->view->fetch('ajax/editpost.tpl')));
            } else {
            	LogUtil::registerPermissionError(null, true);
            	throw new Zikula_Exception_Forbidden();
            }
        }
        
        
        return new Zikula_Response_Ajax_BadData(
            array(),
            $this->__('Error! No post ID in \'Dizkus_ajax_readrawtext()\'.')
        );
    }

    /**
     * updatepost
     *
     * @return string
     */
    public function updatepost()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        $post_id = $this->request->request->get('post', '', 'POST');
        $subject = $this->request->request->get('subject', '', 'POST');
        $message = $this->request->request->get('message', '', 'POST');
        $delete  = $this->request->request->get('delete', null, 'POST');
        $attach_signature = $this->request->request->get('attach_signature', null, 'POST');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($post_id)) {
            //if (!SecurityUtil::confirmAuthKey()) {
            //	LogUtil::registerAuthidError();
            //	throw new Zikula_Exception_Fatal();
            //}

            $message = dzkstriptags($message);
            // check for maximum message size (strlen('[addsig]')==8)
            if ((strlen($message) + 8) > 65535) {
                return new Zikula_Response_Ajax_BadData(
                    array(),
                    $this->__('Error! The message is too long. The maximum length is 65,535 characters.')
                );
            }

            // read the original posting to get the forum id we might need later if the topic has been erased
            $orig_post = ModUtil::apiFunc('Dizkus', 'user', 'readpost', array('post_id'     => $post_id));

            $update = ModUtil::apiFunc('Dizkus', 'user', 'updatepost',
                         array('post_id'          => $post_id,
                               'subject'          => $subject,
                               'message'          => $message,
                               'delete'           => $delete,
                               'attach_signature' => ($attach_signature==1)));
            
            if (!$update) {
                return new Zikula_Response_Ajax_BadData(
                    array()
                );
            }

            if ($delete <> '1') {
                $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost', array('post_id'     => $post_id));
                $hook = new Zikula_FilterHook(
                    $eventname = 'dizkus.filter_hooks.message.filter', 
                    $content = $post['post_text']
                );
                $post['post_text'] = ServiceUtil::getManager()->getService('zikula.hookmanager')
                                                              ->notify($hook)->getData();
                $post['action'] = 'updated';
            } else {
                // try to read topic
                $topic = false;
                if (is_array($orig_post) && !empty($orig_post['topic_id'])) {
                    $topic = ModUtil::apiFunc($this->name, 'Topic', 'read0', $orig_post['topic_id']);
                }
                if (!is_array($topic)) {
                    // topic has been deleted
                    $post = array(
                                'action'   => 'topic_deleted',
                                'redirect' => ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $orig_post['forum_id']),
                                null,
                                null,
                                true)
                            );
                } else {
                    $post = array('action'  => 'deleted');
                }
            }

            SessionUtil::delVar('zk_ajax_call');

            return new Zikula_Response_Ajax($post);
        }

        return new Zikula_Response_Ajax_BadData(
            array(),
            $this->__('Error! No post ID in \'Dizkus_ajax_updatepost()\'.')
        );
    }

    /**
     * lockunlocktopic
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     *
     * @return string
     */
    public function changeTopicStatus()
    {
        // Check if forum is disabled
        if ($this->getVar('forum_enabled') == 'no') {
            return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        // Get common parameters
        $params = array();
        $params['topic_id'] = FormUtil::getPassedValue('topic', '', 'POST');
        $params['action']   = FormUtil::getPassedValue('action', '', 'POST');

        // Check if topic is is set
        if (empty($params['topic_id'])) {
            return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! No topic ID in \'Dizkus_ajax_changeTopicStatus()\'.'));
        }

        // Check if action is legal
        $allowedActions = array('lock', 'unlock', 'sticky', 'unsticky', 'subscribe', 'unsubscribe', 'solve', 'unsolve', 'setTitle');
        if (empty($params['action']) || !in_array($params['action'], $allowedActions)) {
            return new Zikula_Response_Ajax_BadData(array(), $this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_lockunlocktopic()\'.', DataUtil::formatForDisplay($params['action'])));
        }

        // Get title parameter if action == setTitle
        if ($params['action'] == 'setTitle') {
            $params['title'] = FormUtil::getPassedValue('title', '', 'POST');
            $params['title'] = trim($params['title']);
            if (empty($params['title'])) {
                return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! The post has no subject line.'));
            }
        }

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);

        return new Zikula_Response_Ajax_Plain('successful');
    }



    /**
     * addremovefavorite
     *
     */
    public function modifyForum()
    {

        if ($this->getVar('forum_enabled') == 'no') {
        	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        if (ModUtil::getVar('Dizkus', 'favorites_enabled') == 'no') {
        	return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! Favourites have been disabled.'));
        }

        $params = array(
            'forum_id' => FormUtil::getPassedValue('forum', 'POST'),
            'action'   => FormUtil::getPassedValue('action', 'POST')
        );
        if (empty($params['forum_id'])) {
        	return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! No forum ID in \'Dizkus_ajax_addremovefavorite()\'.'));
        }


        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            LogUtil::registerPermissionError();
            throw new Zikula_Exception_Forbidden();
        }

        /*if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            return AjaxUtil::error(null, array(), true, true);
        }*/

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        ModUtil::apiFunc($this->name, 'Forum', 'modify', $params);


        return new Zikula_Response_Ajax_Plain('successful');
    }

    /**
     * newtopic
     *
     */
    public function newtopic()
    {
        if ($this->getVar('forum_enabled') == 'no') {
        	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        $forum_id         = FormUtil::getPassedValue('forum', null, 'POST');
        $message          = FormUtil::getPassedValue('message', '', 'POST');
        $subject          = FormUtil::getPassedValue('subject', '', 'POST');
        $attach_signature = FormUtil::getPassedValue('attach_signature', null, 'POST');
        $subscribe_topic  = FormUtil::getPassedValue('subscribe_topic', null, 'POST');
        $preview          = (int)FormUtil::getPassedValue('preview', 0, 'POST');

        $cat_id = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category',
                               array('forum_id' => $forum_id));

        $topic = array(
            'cat_id' => $cat_id,
            'forum_id' => $forum_id
        );
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $topic)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        $preview          = ($preview == 1) ? true : false;
        //$attach_signature = ($attach_signature=='1') ? true : false;
        //$subscribe_topic  = ($subscribe_topic=='1') ? true : false;

        $message = dzkstriptags($message);
        // check for maximum message size
        if ((strlen($message) + 8/*strlen('[addsig]')*/) > 65535) {
            return AjaxUtil::error($this->__('Error! The message is too long. The maximum length is 65,535 characters.'), array(), true, true);
        } 
        
        if (strlen($message) == 0) {
            return AjaxUtil::error($this->__('Error! You tried to post a blank message. Please go back and try again.'), array(), true, true);
        }

        if (strlen($subject) == 0) {
            return AjaxUtil::error($this->__('Error! The post has no subject line.'), array(), true, true);
        }
        
        $this->view->add_core_data();
        $this->view->setCaching(false);

        if ($preview == false) {
            // store new topic
            $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic',
                                     array('forum_id'         => $forum_id,
                                           'subject'          => $subject,
                                           'message'          => $message,
                                           'attach_signature' => $attach_signature,
                                           'subscribe_topic'  => $subscribe_topic));

            $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic',
                                  array('topic_id' => $topic_id,
                                        'count'    => false));

            if (ModUtil::getVar('Dizkus', 'newtopicconfirmation') == 'yes') {
                $this->view->assign('topic', $topic);
                $confirmation = $this->view->fetch('ajax/newtopicconfirmation.tpl');
            } else {
                $confirmation = false;
            }

            // --- MediaAttach check ---
            if (ModUtil::available('MediaAttach') && ModUtil::isHooked('MediaAttach', 'Dizkus')) {
                return new Zikula_Response_Ajax(array('topic'        => $topic,
                                       'confirmation' => $confirmation,
                                       'redirect'     => ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                  array('topic' => $topic_id),
                                                                  null, null, true),
                                       'uploadredirect' => urlencode(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                              array('topic' => $topic_id))),
                                       'uploadobjectid' => $topic_id,
                                       'uploadauthid' => SecurityUtil::generateCsrfToken('MediaAttach')));

            } else {
                return new Zikula_Response_Ajax(array('topic'        => $topic,
                                       'confirmation' => $confirmation,
                                       'redirect'     => ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                  array('topic' => $topic_id),
                                                                  null, null, true)));
            }
        }

        // preview == true, create fake topic
        $newtopic['cat_id']     = $cat_id;
        $newtopic['forum_id']   = $forum_id;
        $newtopic['topic_unixtime'] = time();
        $newtopic['poster_data'] = ModUtil::apiFunc('Dizkus', 'user', 'get_userdata_from_id', array('userid' => UserUtil::getVar('uid')));
        $newtopic['subject'] = $subject;
        $newtopic['message'] = $message;
        $newtopic['message_display'] = $message; // phpbb_br2nl($message);

        if (($attach_signature == 1) && (!empty($newtopic['poster_data']['signature']))){
            $newtopic['message_display'] .= '[addsig]';
            $newtopic['message_display'] = dzk_replacesignature($newtopic['message_display'], $newtopic['poster_data']['signature']);
        }

//      list($newtopic['message_display']) = ModUtil::callHooks('item', 'transform', '', array($newtopic['message_display']));
        $newtopic['message_display']       = dzkVarPrepHTMLDisplay($newtopic['message_display']);

        if (UserUtil::isLoggedIn()) {
            // If it's the topic start
            if (empty($subject) && empty($message)) {
                $newtopic['attach_signature'] = 1;
                $newtopic['subscribe_topic']  = (ModUtil::getVar('Dizkus', 'autosubscribe') == 'yes') ? 1 : 0;
            } else {
                $newtopic['attach_signature'] = $attach_signature;
                $newtopic['subscribe_topic']  = $subscribe_topic;
            }
        } else {
            $newtopic['attach_signature'] = 0;
            $newtopic['subscribe_topic']  = 0;
        }

        $this->view->assign('newtopic', $newtopic);
        
        return new Zikula_Response_Ajax(array('data'     => $this->view->fetch('user/topic/newpreview.tpl'),
                                              'newtopic' => $newtopic));
    }

    /**
     * forumusers
     * update the "users online" section in the footer
     * original version by gf
     *
     */
    public function forumusers()
    {
        if ($this->getVar('forum_enabled') == 'no') {
        	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }
    	
        $this->view->add_core_data();
        $this->view->setCaching(false);
        if (System::getVar('shorturls')) {
            include_once('lib/render/plugins/outputfilter.shorturls.php');
            $this->view->register_outputfilter('smarty_outputfilter_shorturls');
        }

        $this->view->display('ajax/forumusers.tpl');
        System::shutDown();
    }

    /**
     * newposts
     * update the "new posts" block
     * original version by gf
     *
     */
    public function newposts()
    {
        if ($this->getVar('forum_enabled') == 'no') {
        	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }
    	
        $this->view->add_core_data();
        $this->view->setCaching(false);
        if (System::getVar('shorturls')) {
            include_once 'lib/render/plugins/outputfilter.shorturls.php';
            $this->view->register_outputfilter('smarty_outputfilter_shorturls');
        }

        $out = $this->view->fetch('ajax/newposts.tpl');
        echo $out;
        System::shutDown();
    }

    
    
     /**
     * Performs a user search based on the user name fragment entered so far.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string fragment A partial user name entered by the user.
     *
     * @return string Zikula_Response_Ajax_Plain with list of users matching the criteria.
     */
    public function getUsers()
    {
        //$this->checkAjaxToken();
        $view = Zikula_View::getInstance($this->name);

        if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            $fragment = $this->request->getGet()->get('fragment', $this->request->getPost()->get('fragment'));

            $tables = DBUtil::getTables();
            $usersColumn = $tables['users_column'];
            $where = $usersColumn['uname'] . ' REGEXP \'(' . DataUtil::formatForStore($fragment) . ')\'';
            $results = UserUtil::getUsers($where);
            
            $view->assign('results', $results);
        }

        $output = $view->fetch('ajax/getusers.tpl');

        return new Zikula_Response_Ajax_Plain($output);
    }
}
