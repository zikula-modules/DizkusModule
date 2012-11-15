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
        if ($this->getVar('forum_enabled') == 'no') {
            return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        $topicId = FormUtil::getPassedValue('topic', '', 'POST');
        $action     = FormUtil::getPassedValue('action', '', 'POST');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (empty($topicId)) {
            return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! No topic ID in \'Dizkus_ajax_lockunlocktopic()\'.'));
        }
        $actions = array('lock', 'unlock', 'sticky', 'unsticky');

        if (empty($action) || !in_array($action, $actions) ) {
            return new Zikula_Response_Ajax_BadData(array(), $this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_lockunlocktopic()\'.', DataUtil::formatForDisplay($action)));
        }

        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        $topic = new Dizkus_ContentType_Topic($topicId);
        if ($action == 'lock') {
            $topic->lock();
        } else if ($action == 'unlock') {
            $topic->unlock();
        } else if ($action == 'sticky') {
            $topic->sticky();
        } else if ($action == 'unsticky') {
            $topic->unsticky();
        }

        return new Zikula_Response_Ajax_Plain('successful');
    }

    /**
     * subscribeunsubscribetopic
     *
     */
    public function subscribeunsubscribetopic()
    {
        if ($this->getVar('forum_enabled') == 'no') {
        	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        $topic_id = FormUtil::getPassedValue('topic', '', 'POST');
        $mode     = FormUtil::getPassedValue('mode', '', 'POST');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (empty($topic_id)) {
        	return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! No topic ID in Dizkus_ajax_subscribeunsubscribetopic().'));
        }

        $topic = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                array('topic_id' => $topic_id));

        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $topic)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        switch ($mode)
        {
            case 'subscribe':
                ModUtil::apiFunc('Dizkus', 'user', 'subscribe_topic',
                             array('topic_id' => $topic_id,
                                   'silent'   => true));
                $newmode = 'subscribed';
                break;

            case 'unsubscribe':
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_topic',
                             array('topic_id' => $topic_id,
                                   'silent'   => true));
                $newmode = 'unsubscribed';
                break;

            default:
                return new Zikula_Response_Ajax_BadData(array(), $this->__f('Error! No mode or illegal mode parameter (%s) in Dizkus_ajax_subscribeunsubscribetopic().', DataUtil::formatForDisplay($mode)));
        }

        return new Zikula_Response_Ajax(array('data' => $newmode));
    }

    /**
     * subscribeunsubscribeforum
     *
     */
    public function toggleforumsubscription()
    {
        if ($this->getVar('forum_enabled') == 'no') {
        	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        $forum_id = FormUtil::getPassedValue('forum', '', 'POST');

        SessionUtil::setVar('zk_ajax_call', 'ajax');
   
         /*if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            return AjaxUtil::error(null, array(), true, true);
        }*/
    
        if (empty($forum_id)) {
        	return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! No forum ID in \'toggleforumsubscription()\'.'));
        }

        $forum = array('forum_id' => $forum_id);
        $forum['cat_id'] = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category', $forum);

        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $forum_id)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        $subscribed = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_subscription_status', 
                                       array('user_id' => UserUtil::getVar('uid'), 
                                             'forum_id' => $forum_id));
        
        if ($subscribed == true){
            ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_forum',
                         array('forum_id' => $forum_id,
                               'silent'   => true));
            $newmode = 'unsubscribed';
        } else {
            ModUtil::apiFunc('Dizkus', 'user', 'subscribe_forum',
                         array('forum_id' => $forum_id,
                               'silent'   => true));
            $newmode = 'subscribed';
        }

        return new Zikula_Response_Ajax(array('data' => $newmode));
    }

    /**
     * toggle new topics auto subscription
     *
     */
    public function toggleautosubscription()
    {
        if ($this->getVar('forum_enabled') == 'no') {
        	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }
    	
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        /*if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            return AjaxUtil::error(null, array(), true, true);
        }*/

        $newmode = ((int)ModUtil::apiFunc('Dizkus', 'user', 'togglenewtopicsubscription') == 1) ? 'autosubscription' : 'noautosubscription';

        return new Zikula_Response_Ajax(array('data' => $newmode));
    }

    public function test() {
        $input = FormUtil::getPassedValue('input', '', 'POST');
        return new Zikula_Response_Ajax_Plain($input.'gg');
    }


    /**
     * addremovefavorite
     *
     */
    public function toggleForumFavouriteState()
    {

        if ($this->getVar('forum_enabled') == 'no') {
        	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        if (ModUtil::getVar('Dizkus', 'favorites_enabled') == 'no') {
        	return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! Favourites have been disabled.'));
        }

        $forum_id = FormUtil::getPassedValue('forum', '', 'POST');
        $action = FormUtil::getPassedValue('action', '', 'POST');
        if (empty($forum_id)) {
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

        ModUtil::apiFunc($this->name, 'Favorites', $action, array('forum_id' => $forum_id));


        return new Zikula_Response_Ajax_Plain('successful');
    }

    /**
     * edittopicsubject
     *
     */
    public function edittopicsubject()
    {
        if ($this->getVar('forum_enabled') == 'no') {
        	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }
    	
        $topic_id = FormUtil::getPassedValue('topic', '', 'POST');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($topic_id)) {
            $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic',
                                 array('topic_id' => $topic_id,
                                       'count'    => false,
                                       'complete' => false));

            if ($topic['access_topicsubjectedit'] == true) {
                $this->view->add_core_data();
                $this->view->setCaching(false);
                $this->view->assign('topic', $topic);

                SessionUtil::delVar('zk_ajax_call');

        		return new Zikula_Response_Ajax(array('data' => $this->view->fetch('ajax/edittopicsubject.tpl'))); 
            } else {
            	LogUtil::registerPermissionError(null, true);
            	throw new Zikula_Exception_Forbidden();
            }
        }

        return new Zikula_Response_Ajax_Fatal(array(), $this->__('Error! No topic ID in \'Dizkus_ajax_readtopic()\'.'), array(), true, true);
    }

    /**
     * updatetopicsubject
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     *
     * return boolean
     */
    public function updatetopicsubject()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        $topic_id = FormUtil::getPassedValue('topic', '', 'POST');
        $subject  = FormUtil::getPassedValue('subject', '', 'POST');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($topic_id)) {
            /*if (!SecurityUtil::confirmAuthKey()) {
            	LogUtil::registerAuthidError();
            	throw new Zikula_Exception_Fatal();
            }*/

            $topic = ModUtil::apiFunc($this->name, 'Topic', 'read0', $topic_id);
            $topicposter = $topic['topic_poster'];

            $topic= ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $topic_id));
            if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $topic) && UserUtil::getVar('uid') <> $topicposter) {
                LogUtil::registerPermissionError(null, true);
                throw new Zikula_Exception_Forbidden();
            }

            $subject = trim($subject);
            if (empty($subject)) {
                return new Zikula_Response_Ajax_Fatal(array(), $this->__('Error! The post has no subject line.'));
            }

            $topic['topic_id']    = $topic_id;
            $topic['topic_title'] = $subject;
            $res = DBUtil::updateObject($topic, 'dizkus_topics', '', 'topic_id');

            // Let any hooks know that we have updated an item.
            // ModUtil::callHooks('item', 'update', $topic_id, array('module'   => 'Dizkus', topic_id' => $topic_id));

            SessionUtil::delVar('zk_ajax_call');

            return new Zikula_Response_Ajax(array('topic_title' => DataUtil::formatForDisplay($subject)));
        }

        return new Zikula_Response_Ajax_Fatal(array(), $this->__('Error! No topic ID in \'Dizkus_ajax_updatetopicsubject()\''));
    }

    /**
     * togglesortorder
     *
     */
    public function togglesortorder()
    {
        if ($this->getVar('forum_enabled') == 'no') {
          	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!UserUtil::isLoggedIn()) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        /*if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }*/

        ModUtil::apiFunc('Dizkus', 'user', 'change_user_post_order');
        $new_post_order =  strtolower(ModUtil::apiFunc('Dizkus','user','get_user_post_order'));

		return new Zikula_Response_Ajax(array('data' => $new_post_order));
    }

    /**
     * change forum display
     *
     */
    public function toggleforumdisplay()
    {
        if ($this->getVar('forum_enabled') == 'no') {
          	return new Zikula_Response_Ajax_Unavailable(array(), strip_tags($this->getVar('forum_disabled_info')));
        }
    	
        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!UserUtil::isLoggedIn()) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        /*if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }*/

        ModUtil::apiFunc('Dizkus', 'user', 'change_favorite_status');
        $new_favorite_status =  ModUtil::apiFunc('Dizkus','user','get_favorite_status');

		return new Zikula_Response_Ajax(array('data' => $new_favorite_status));
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
     * editcategory
     */
    public function createcategory()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }
    
        // we add a new category
        $category = array('cat_title'    => $this->__('-- Create new category --'),
                          'cat_id'       => time(),
                          'forums'       => array(),
                          'forum_count'  => 0);
    
        $this->view->assign('category', $category );
        $this->view->assign('newcategory', true);
    
        return new Zikula_Response_Ajax(array('tpl'     => $this->view->fetch('ajax/singlecategory.tpl'),
                                              'cat_id'  => $category['cat_id']));
    }

    /**
     * storecategory
     *
     * AJAX function
     */
    public function storecategory()
    {
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }
    
        /*if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }*/
    
        $cat_id    = FormUtil::getPassedValue('cat_id', null, 'POST');
        $cat_title = FormUtil::getPassedValue('cat_title', null, 'POST');
        $add       = FormUtil::getPassedValue('add', null, 'POST');
        $delete    = FormUtil::getPassedValue('delete', null, 'POST');
    
        if (!empty($delete)) {
            $forums = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                   array('cat_id'    => $cat_id,
                                         'permcheck' => 'nocheck'));
            if (count($forums) > 0) {
                $category = ModUtil::apiFunc('Dizkus', 'admin', 'readcategory', $cat_id);
            	return new Zikula_Response_Ajax_BadData(array('cat_id' => $cat_id, 'old_id' => $cat_id), 
            	                                        $this->__f('Error! This category contains %s forum)', DataUtil::formatForDisplay(count($forums))));
            }
            $res = ModUtil::apiFunc('Dizkus', 'admin', 'deletecategory',
                                array('cat_id' => $cat_id));
            if ($res == true) {
                return new Zikula_Response_Ajax(array('cat_id' => $cat_id,
                                                      'old_id' => $cat_id,
                                                      'action' => 'delete'));
            } else {
            	return new Zikula_Response_Ajax_BadData(array(), 
            											$this->__f('Error! Could not delete category %s)', DataUtil::formatForDisplay($cat_id)));
            }
    
        } elseif (!empty($add)) {
            $original_catid = $cat_id;
            $cat_id = ModUtil::apiFunc('Dizkus', 'admin', 'addcategory',
                                       array('cat_title' => $cat_title));
            if (!is_bool($cat_id)) {
                $category = ModUtil::apiFunc('Dizkus', 'admin', 'readcategory', $cat_id);
/*
                $category_forums = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                                    array('cat_id'    => $category['cat_id'],
                                                          'permcheck' => ACCESS_ADMIN)); 
*/
                $category['forum_count'] = 0;
                $category['forums'] = array();
                $this->view->assign('category', $category );
                $this->view->assign('newcategory', false);
                return new Zikula_Response_Ajax(array('cat_id'      => $cat_id,
				                                      'old_id'      => $original_catid,
				                                      'cat_title'   => $cat_title,
				                                      'action'      => 'add',
				                                      'edithtml'    => $this->view->fetch('ajax/singlecategory.tpl'),
				                                      'cat_linkurl' => ModUtil::url('Dizkus', 'user', 'main', array('viewcat' => $cat_id))));
            } else {
            	return new Zikula_Response_Ajax_BadData(array(), 
            											$this->__f('Error! Could not create category %s)', DataUtil::formatForDisplay($cat_title)));
            }
    
        } else {
            if (ModUtil::apiFunc('Dizkus', 'admin', 'updatecategory',
                             array('cat_title' => $cat_title,
                                   'cat_id'    => $cat_id)) == true) {
                return new Zikula_Response_Ajax(array('cat_id'      => $cat_id,
                                       			      'old_id'      => $cat_id,
				                                      'cat_title'   => $cat_title,
				                                      'action'      => 'update',
				                                      'cat_linkurl' => ModUtil::url('Dizkus', 'user', 'main', array('viewcat' => $cat_id))));
            } else {
            	return new Zikula_Response_Ajax_BadData(array(), 
            											$this->__f('Error! Could not update category %s)', DataUtil::formatForDisplay($cat_id)));
            }
        }
    }
    
    /**
     * editforum
     *
     * AJAX function
     */
    public function editforum($args=array())
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }
    
        $forum_id   = isset($args['forum_id']) ? $args['forum_id'] : FormUtil::getPassedValue('forum_id', null, 'POST');
        $returnhtml = isset($args['returnhtml']) ? $args['returnhtml'] : FormUtil::getPassedValue('returnhtml', null, 'POST');
    
        if (!isset($forum_id)) {
        	return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! Missing forum_id.'));
        }
    
        if ($forum_id == -1) {
            // create a new forum
            $new = true;
            $template = 'ajax/singleforum.tpl';
            $cat_id = FormUtil::getPassedValue('cat');
            $forum = array('forum_name'       => $this->__('-- Create new forum --'),
                           'forum_id'         => time(), /* for new forums only! */
                           'forum_desc'       => $this->__('-- A new forum without a description --'),
                           'forum_order'      => -1,
                           'cat_title'        => '',
                           'cat_id'           => $cat_id,
                           'pop3_active'      => 0,
                           'pop3_server'      => '',
                           'pop3_port'        => 110,
                           'pop3_login'       => '',
                           'pop3_password'    => '',
                           'pop3_interval'    => 0,
                           'pop3_pnuser'      => '',
                           'pop3_pnpassword'  => '',
                           'pop3_matchstring' => '',
                           'forum_moduleref'  => '',
                           'forum_pntopic'    => 0,
                           'externalsource'   => 0);
            $moderators = array();
        } else {
            // we are editing
            $new = false;
            $template = 'ajax/editforum.tpl';
            $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                  array('forum_id'  => $forum_id,
                                        'permcheck' => ACCESS_ADMIN));
            $moderators = ModUtil::apiFunc('Dizkus', 'admin', 'readmoderators',
                                        array('forum_id' => $forum['forum_id']));
    
    
        }
    
        $externalsourceoptions = array( 0 => array('checked'  => '',
                                                   'name'     => $this->__('No external source'),
                                                   'ok'       => '',
                                                   'extended' => false),   // none
                                        1 => array('checked'  => '',
                                                   'name'     => $this->__('Mail2Forum'),
                                                   'ok'       => '',
                                                   'extended' => true),  // mail
                                        2 => array('checked'  => '',
                                                   'name'     => $this->__('RSS2Forum'),
                                                   'ok'       => (ModUtil::available('Feeds') == true) ? '' : $this->__("<span style=\"color: red;\">'Feeds' module is not available.</span>"),
                                                   'extended' => true)); // rss
    
        $externalsourceoptions[$forum['pop3_active']]['checked'] = ' checked="checked"';
    
        $hooked_modules_raw = ModUtil::apiFunc('modules', 'admin', 'gethookedmodules',
                                           array('hookmodname' => 'Dizkus'));
    
        $hooked_modules = array(array('name' => $this->__('No hooked module found.'),
                                      'id'   => 0));
    
        $foundsel = false;
        /*foreach ($hooked_modules_raw as $hookmod => $dummy) {
            $hookmodid = ModUtil::getIDFromName($hookmod);
            $sel = false;
            if ($forum['forum_moduleref'] == $hookmodid) {
                $sel = true;
                $foundsel = true;
            }
            $hooked_modules[] = array('name' => $hookmod,
                                      'id'   => $hookmodid,
                                      'sel'  => $sel);
        }*/
    
        if ($foundsel == false) {
            $hooked_modules[0]['sel'] = true;
        }
    
        // read all RSS feeds
        $rssfeeds = array();
        if (ModUtil::available('Feeds')) {
            $rssfeeds = ModUtil::apiFunc('Feeds', 'user', 'getall');
        }
    
        $this->view->assign('hooked_modules', $hooked_modules);
        $this->view->assign('rssfeeds', $rssfeeds);
        $this->view->assign('externalsourceoptions', $externalsourceoptions);
    
        $cats        = CategoryUtil::getSubCategories (1, true, true, true, true, true);
        $catselector = CategoryUtil::getSelector_Categories($cats, 'id', $forum['forum_pntopic'], 'pncategory');
        $this->view->assign('categoryselector', $catselector);
    
        $this->view->assign('moderators', $moderators);
        $hideusers = ModUtil::getVar('Dizkus', 'hideusers');
        if ($hideusers == 'no') {
            $users = ModUtil::apiFunc('Dizkus', 'admin', 'readusers',
                                  array('moderators' => $moderators));
        } else {
            $users = array();
        }
        $this->view->assign('users', $users);
        $this->view->assign('groups', ModUtil::apiFunc('Dizkus', 'admin', 'readgroups',
                                            array('moderators' => $moderators)));
        $this->view->assign('forum', $forum);
        $this->view->assign('newforum', $new);
    
        $html = $this->view->fetch($template);
    
        if (!isset($returnhtml)) {
            return new Zikula_Response_Ajax(array('forum_id' => $forum['forum_id'],
                                                  'cat_id'   => $forum['cat_id'],
                                   				  'new'      => $new,
                                   				  'data'     => $html));
        }
    
        return($html);
    }

    /**
     * storeforum
     *
     * AJAX function
     */
    public function storeforum()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }
    
        /*if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }*/
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        $forum_name           = FormUtil::getPassedValue('forum_name', null, 'POST');
        $forum_id             = FormUtil::getPassedValue('forum_id', null, 'POST');
        $cat_id               = FormUtil::getPassedValue('cat_id', null, 'POST');
        $desc                 = FormUtil::getPassedValue('desc', null, 'POST');
        $mods                 = FormUtil::getPassedValue('mods', null, 'POST');
        $rem_mods             = FormUtil::getPassedValue('rem_mods', null, 'POST');
        $extsource            = FormUtil::getPassedValue('extsource', null, 'POST');
        $rssfeed              = FormUtil::getPassedValue('rssfeed', null, 'POST');
        $pop3_server          = FormUtil::getPassedValue('pop3_server', null, 'POST');
        $pop3_port            = FormUtil::getPassedValue('pop3_port', null, 'POST');
        $pop3_login           = FormUtil::getPassedValue('pop3_login', null, 'POST');
        $pop3_password        = FormUtil::getPassedValue('pop3_password', null, 'POST');
        $pop3_passwordconfirm = FormUtil::getPassedValue('pop3_passwordconfirm', null, 'POST');
        $pop3_interval        = FormUtil::getPassedValue('pop3_interval', null, 'POST');
        $pop3_matchstring     = FormUtil::getPassedValue('pop3_matchstring', null, 'POST');
        $pnuser               = FormUtil::getPassedValue('pnuser', null, 'POST');
        $pnpassword           = FormUtil::getPassedValue('pnpassword', null, 'POST');
        $pnpasswordconfirm    = FormUtil::getPassedValue('pnpasswordconfirm', null, 'POST');
        $moduleref            = FormUtil::getPassedValue('moduleref', null, 'POST');
        $pop3_test            = FormUtil::getPassedValue('pop3_test', null, 'POST');
        $add                  = FormUtil::getPassedValue('add', null, 'POST');
        $delete               = FormUtil::getPassedValue('delete', null, 'POST');
    
        $pntopic              = (int)FormUtil::getpassedValue('pncategory', 0, 'POST');
    
        $pop3testresulthtml = '';
        if (!empty($delete)) {
            $action = 'delete';
            $newforum = array();
            $forumtitle = '';
            $editforumhtml = '';
            $old_id = $forum_id;
            $cat_id = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category',
                                   array('forum_id' => $forum_id)); 
            // no security check!!!
            ModUtil::apiFunc('Dizkus', 'admin', 'deleteforum',
                         array('forum_id'   => $forum_id));
        } else {
            // add or update - the next steps are the same for both
            if ($extsource == 2) {
                // store the rss feed in the pop3_server field
                $pop3_server = $rssfeed;
            }
    
            if ($pop3_password <> $pop3_passwordconfirm) {
				return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! The two passwords you entered for POP3 access do not match. Please correct your entries and try again.'));
            }
            if ($pnpassword <> $pnpasswordconfirm) {
				return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! The two passwords you entered as user passwords do not match. Please correct your entries and try again.'));
            }
    
            if (!empty($add)) {
                $action = 'add';
                $old_id = $forum_id;
                $pop3_password = base64_encode($pop3_password);
                $pnpassword = base64_encode($pnpassword);
                $forum_id = ModUtil::apiFunc('Dizkus', 'admin', 'addforum',
                                         array('forum_name'             => $forum_name,
                                               'cat_id'                 => $cat_id,
                                               'forum_desc'             => $desc,
                                               'mods'                   => $mods,
                                               'forum_pop3_active'      => $extsource,
                                               'forum_pop3_server'      => $pop3_server,
                                               'forum_pop3_port'        => $pop3_port,
                                               'forum_pop3_login'       => $pop3_login,
                                               'forum_pop3_password'    => $pop3_password,
                                               'forum_pop3_interval'    => $pop3_interval,
                                               'forum_pop3_pnuser'      => $pnuser,
                                               'forum_pop3_pnpassword'  => $pnpassword,
                                               'forum_pop3_matchstring' => $pop3_matchstring,
                                               'forum_moduleref'        => $moduleref,
                                               'forum_pntopic'          => $pntopic));
            } else {
                $action = 'update';
                $old_id = $forum_id;
                $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                      array('forum_id' => $forum_id));
    
                // check if user has changed the password
                if ($forum['pop3_password'] == $pop3_password) {
                    // no change necessary
                    $pop3_password = "";
                } else {
                    $pop3_password = base64_encode($pop3_password);
                }
    
                // check if user has changed the password
                if ($forum['pop3_pnpassword'] == $pnpassword) {
                    // no change necessary
                    $pnpassword = '';
                } else {
                    $pnpassword = base64_encode($pnpassword);
                }
    
                ModUtil::apiFunc('Dizkus', 'admin', 'editforum',
                             array('forum_name'             => $forum_name,
                                   'forum_id'               => $forum_id,
                                   'cat_id'                 => $cat_id,
                                   'forum_desc'             => $desc,
                                   'mods'                   => $mods,
                                   'rem_mods'               => $rem_mods,
                                   'forum_pop3_active'      => $extsource,
                                   'forum_pop3_server'      => $pop3_server,
                                   'forum_pop3_port'        => $pop3_port,
                                   'forum_pop3_login'       => $pop3_login,
                                   'forum_pop3_password'    => $pop3_password,
                                   'forum_pop3_interval'    => $pop3_interval,
                                   'forum_pop3_pnuser'      => $pnuser,
                                   'forum_pop3_pnpassword'  => $pnpassword,
                                   'forum_pop3_matchstring' => $pop3_matchstring,
                                   'forum_moduleref'        => $moduleref,
                                   'forum_pntopic'          => $pntopic));
            }
            $editforumhtml = $this->editforum(array('forum_id'   => $forum_id,
                                                    'returnhtml' => true));
    
            $forumtitle = '<a href="' . ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id)) .'">' . $forum_name . '</a> (' . $forum_id . ')';
    
            // re-read forum data 
            $newforum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                  array('forum_id'  => $forum_id,
                                        'permcheck' => 'nocheck'));
    
            if ($pop3_test == 1) {
                $pop3testresult = ModUtil::apiFunc('Dizkus', 'user', 'testpop3connection',
                                               array('forum_id' => $forum_id));
    
                $this->view->assign('messages', $pop3testresult);
                $this->view->assign('forum_id', $forum_id);
    
                $pop3testresulthtml = $this->view->fetch('dizkus_admin_pop3test.tpl');
            }
        } 

        return new Zikula_Response_Ajax(array('action'         => $action,
				                              'forum'          => $newforum,
				                              'cat_id'         => $cat_id,
				                              'old_id'         => $old_id,
				                              'forum_id'       => $forum_id,  /* duplicate, but now the returned information are equal to the cateogry ones */
				                              'pop3resulthtml' => $pop3testresulthtml,
				                              'editforumhtml'  => $editforumhtml,
				                              'forumtitle'     => $forumtitle));
    }

    /**
     * savetree
     *
     * AJAX result function
     */
    public function savetree()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
        
        $categoryarray = FormUtil::getPassedValue('category', null, 'POST');
        // the last entry in the $category is the placeholder for a new
        // category, we need to remove this
        // not used any longer: array_pop($categoryarray);
        if (is_array($categoryarray) && count($categoryarray) > 0) {
            // store category order
            foreach ($categoryarray as $catorder => $cat_id) {
                // array key = catorder starts with 0, but we need 1, so we increase the order
                // value
                $catorder++;
                if (ModUtil::apiFunc('Dizkus', 'admin', 'updatecategory',
                                 array('cat_id'    => $cat_id,
                                       'cat_order' => $catorder)) == false) {
                    return new Zikula_Response_Ajax_BadData(array(), $this->__f('Error! cannot reorder category %s.', $cat_id));
                }
            }
        } else {
            // store forum order
            $cat_id = FormUtil::getPassedValue('cat_id', null, 'POST');
            if (!is_null($cat_id)) {
                $forumsarray = FormUtil::getPassedValue('cid_'.DataUtil::formatForDisplay($cat_id), null, 'POST');
                if (is_array($forumsarray) && count($forumsarray) > 0) {
                    foreach ($forumsarray as $forumorder => $forum_id) {
                        // array key start with 0, but we need 1, so we increase the order
                        // value
                        $forumorder++;
                        $newforum = array('forum_id'    => $forum_id,
                                          'cat_id'      => $cat_id,
                                          'forum_order' => $forumorder);
                        DBUtil::updateObject($newforum, 'dizkus_forums', null, 'forum_id');
                    }
                }
            }
        }
        return new Zikula_Response_Ajax(array());    
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
