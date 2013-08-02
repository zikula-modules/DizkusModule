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
class Dizkus_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * Checks if the forum is disabled.
     *
     * @throws Zikula_Exception_Forbidden
     * @return void
     */
    private function errorIfForumDisabled()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            throw new Zikula_Exception_Forbidden(strip_tags($this->getVar('forum_disabled_info')));
        }
    }

    /**
     * Checks if a message is shorter than 65535 - 8 characters.
     *
     * @param string $message The message to check.
     *
     * @throws Zikula_Exception_Fatal
     * @return void
     */
    private function checkMessageLength($message)
    {
        if (!ModUtil::apiFunc($this->name, 'post', 'checkMessageLength', array('message' => $message))) {
            throw new Zikula_Exception_Fatal($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
        }
    }

    /**
     * Create and configure the view for the controller.
     *
     * @return void
     *
     * @note This is necessary because the Zikula_Controller_AbstractAjax overrides this method located in Zikula_AbstractController.
     */
    protected function configureView()
    {
        $this->setView();
        $this->view->setController($this);
        $this->view->assign('controller', $this);
    }

    /**
     * Reply to a topic (or just preview).
     *
     * POST: $topic_id The topic id to reply to.
     *       $message The post message.
     *       $title @todo What is title for??
     *       $attach_signature Attach signature?
     *       $subscribe_topic Subscribe to topic.
     *       $preview Is this a preview only?
     *
     * RETURN: $data The rendered post.
     *         $post_id The post id.
     *
     * @throws Zikula_Exception_Fatal
     *
     * @return Zikula_Response_Ajax
     */
    public function reply()
    {
        $this->errorIfForumDisabled();
        $this->checkAjaxToken();

        $topic_id = $this->request->request->get('topic', null);
        $message = $this->request->request->get('message', '');
        $title = $this->request->request->get('title', '');

        $attach_signature = ($this->request->request->get('attach_signature', 0) == '1') ? true : false;
        $subscribe_topic = ($this->request->request->get('subscribe_topic', 0) == '1') ? true : false;
        $preview = ($this->request->request->get('preview', 0) == '1') ? true : false;

        $message = ModUtil::apiFunc('Dizkus', 'user', 'dzkstriptags', $message);
        $title = ModUtil::apiFunc('Dizkus', 'user', 'dzkstriptags', $title);

        // ContactList integration: Is the user ignored and allowed to write an answer to this topic?
        $managedTopic = new Dizkus_Manager_Topic($topic_id);

        $start = 0;
        $ignorelist_setting = ModUtil::apiFunc('Dizkus', 'user', 'get_settings_ignorelist', array('uid' => $managedTopic->get()->getPoster()->getUser_id()));
        if (ModUtil::available('ContactList') && ($ignorelist_setting == 'strict') && (ModUtil::apiFunc('ContactList', 'user', 'isIgnored', array('uid' => (int)$managedTopic->get()->getPoster()->getUser_id(), 'iuid' => UserUtil::getVar('uid'))))) {
            throw new Zikula_Exception_Fatal($this->__('Error! The user who started this topic is ignoring you, and does not want you to be able to write posts under this topic. Please contact the topic originator for more information.'));
        }

        $this->checkMessageLength($message);

        if ($preview == false) {
            $data = array(
                'topic_id' => $topic_id,
                'post_text' => $message,
                'attachSignature' => $attach_signature,
            );

            $managedPost = new Dizkus_Manager_Post();
            $managedPost->create($data);

            if ($subscribe_topic) {
                ModUtil::apiFunc($this->name, 'topic', 'subscribe', array('topic' => $topic_id));
            } else {
                ModUtil::apiFunc($this->name, 'topic', 'unsubscribe', array('topic' => $topic_id));
            }

            $start = ModUtil::apiFunc('Dizkus', 'user', 'getTopicPage', array('replyCount' => $managedPost->get()->getTopic()->getReplyCount()));
            $params = array(
                'topic' => $topic_id,
                'start' => $start
            );
            $url = new Zikula_ModUrl('Dizkus', 'user', 'viewtopic', ZLanguage::getLanguageCode(), $params, 'pid' . $managedPost->getId());
            $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new Zikula_ProcessHook('dizkus.ui_hooks.post.process_edit', $managedPost->getId(), $url));

            // notify topic & forum subscribers
            ModUtil::apiFunc('Dizkus', 'notify', 'emailSubscribers', array('post' => $managedPost->get()));
            $post = $managedPost->get()->toArray();

            $permissions = ModUtil::apiFunc($this->name, 'permission', 'get', array('forum_id' => $managedPost->get()->getTopic()->getForum()->getForum_id()));
        } else {
            // preview == true, create fake post
            $managedPoster = new Dizkus_Manager_ForumUser();
            $post['post_id'] = 0;
            $post['topic_id'] = $topic_id;
            $post['poster'] = $managedPoster->toArray();
            // create unix timestamp
            $post['post_time'] = time();

            $post['title'] = $title;
            $post['post_textdisplay'] = $this->phpbb_br2nl($message);
            if ($attach_signature == 1) {
                $post['post_textdisplay'] .= '[addsig]';
                $post['post_textdisplay'] = $this->dzk_replacesignature(array('text' => $post['post_textdisplay'], 'signature' => $post['poster_data']['signature']));
            }
            $post['post_textdisplay'] = ModUtil::apiFunc('Dizkus', 'user', 'dzkVarPrepHTMLDisplay', $post['post_textdisplay']);

            $post['post_text'] = $post['post_textdisplay'];

            // Do not show edit link
            $permissions = array();
        }

        $this->view->add_core_data();
        $this->view->setCaching(false);
        $this->view->assign('topic', $managedTopic->get());
        $this->view->assign('post', $post);
        $this->view->assign('start', $start);
        $this->view->assign('preview', $preview);
        $this->view->assign('permissions', $permissions);

        list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => Dizkus_Entity_Rank::TYPE_POSTCOUNT));
        $this->view->assign('ranks', $ranks);

        return new Zikula_Response_Ajax(array('data' => $this->view->fetch('user/post/single.tpl'), 'post_id' => $post['post_id']));
    }

    /**
     * Edit a post.
     *
     * POST: $post The post id to edit.
     *
     * RETURN: The edit post form.
     *
     * @throws Zikula_Exception_Fatal
     * @throws Zikula_Exception_Forbidden
     *
     * @return Zikula_Response_Ajax
     */
    public function editpost()
    {
        $this->errorIfForumDisabled();
        $this->checkAjaxToken();

        $post_id = $this->request->request->get('post', null, 'POST');
        $currentUserId = UserUtil::getVar('uid');

        if (!empty($post_id)) {
            $managedPost = new Dizkus_Manager_Post($post_id);
            $forum = $managedPost->get()->getTopic()->getForum();
            $managedForum = new Dizkus_Manager_Forum(null, $forum);
            if (($managedPost->get()->getPoster()->getUser_id() == $currentUserId) || ($managedForum->isModerator())) {
                $this->view->setCaching(false);

                $this->view->assign('post', $managedPost->get());
                // simplify our live
                $this->view->assign('postingtextareaid', 'postingtext_' . $managedPost->getId() . '_edit');
                $this->view->assign('isFirstPost', $managedPost->get()->isFirst());

                return new Zikula_Response_Ajax($this->view->fetch('ajax/editpost.tpl'));
            } else {
                LogUtil::registerPermissionError(null, true);
                throw new Zikula_Exception_Forbidden();
            }
        }

        throw new Zikula_Exception_Fatal($this->__('Error! No post ID in \'Dizkus_ajax_readrawtext()\'.'));
    }

    /**
     * Update a post.
     *
     * POST: $postId The post id to update.
     *       $message The new post message.
     *       $delete_post Delete this post?
     *       $attach_signature Attach signature?
     *
     * RETURN: $action The executed action.
     *         $newText The new post text (can be empty).
     *         $redirect The page to redirect to (can be empty).
     *
     *
     * @throws Zikula_Exception_Fatal
     * @throws Zikula_Exception_Forbidden If the user tries to delete the only post of a topic.
     *
     * @return Zikula_Response_Ajax
     */
    public function updatepost()
    {
        $this->errorIfForumDisabled();
        $this->checkAjaxToken();

        $post_id = $this->request->request->get('postId', '');
        $title = $this->request->request->get('title', '');
        $message = $this->request->request->get('message', '');
        $delete = ($this->request->request->get('delete_post', 0) == '1') ? true : false;
        $attach_signature = ($this->request->request->get('attach_signature', 0) == '1') ? true : false;

        if (!empty($post_id)) {
            $message = ModUtil::apiFunc('Dizkus', 'user', 'dzkstriptags', $message);
            $this->checkMessageLength($message);

            $managedOriginalPost = new Dizkus_Manager_Post($post_id);

            if ($delete) {
                if ($managedOriginalPost->get()->isFirst()) {
                    throw new Zikula_Exception_Forbidden($this->__('Error! Cannot delete the first post in a topic. Delete the topic instead.'));
                } else {
                    $response = array('action' => 'deleted');
                }

                $managedOriginalPost->delete();
                $this->dispatchHooks('dizkus.ui_hooks.post.process_delete', new Zikula_ProcessHook('dizkus.ui_hooks.post.process_delete', $managedOriginalPost->getId()));
            } else {
                $data = array('title' => $title,
                        'post_text' => $message,
                        'attachSignature' => $attach_signature);
                $managedOriginalPost->update($data);
                $url = new Zikula_ModUrl('Dizkus', 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $managedOriginalPost->getTopicId()), 'pid' . $managedOriginalPost->getId());
                $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new Zikula_ProcessHook('dizkus.ui_hooks.post.process_edit', $managedOriginalPost->getId(), $url));
                $response = array('action' => 'updated', 'newText' => $message);
            }

            return new Zikula_Response_Ajax($response);
        }

        throw new Zikula_Exception_Fatal($this->__('Error! No post_id in \'Dizkus_ajax_updatepost()\'.'));
    }






    /*
     *
     *
     *
     *
     * The code below must be checked!
     * -- cmfcmf
     *
     *
     *
     */







    /**
     * readpost
     */
    public function readpost()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $post_id = FormUtil::getPassedValue('post', null, 'POST');
        $currentUserId = UserUtil::getVar('uid');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($post_id)) {
            $managedPost = new Dizkus_Manager_Post($post_id);
            $forum = $managedPost->get()->getTopic()->getForum();
            $managedForum = new Dizkus_Manager_Forum(null, $forum);
            if (($managedPost->get()->getPoster()->getUser_id() == $currentUserId)
                || ($managedForum->isModerator())) {
                AjaxUtil::output($managedPost->get(), true, false, false);
            } else {
                LogUtil::registerPermissionError();
                return AjaxUtil::error(null, array(), true, true, '400 Bad Data');
            }
        }

        return AjaxUtil::error($this->__('Error! No post ID in \'Dizkus_ajax_readpost()\'.'), array(), true, true, '400 Bad Data');
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
        $params['action'] = FormUtil::getPassedValue('action', '', 'POST');

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
            'action' => FormUtil::getPassedValue('action', 'POST')
        );
        if (empty($params['forum_id'])) {
            return new Zikula_Response_Ajax_BadData(array(), $this->__('Error! No forum ID in \'Dizkus_ajax_addremovefavorite()\'.'));
        }


        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            LogUtil::registerPermissionError();
            throw new Zikula_Exception_Forbidden();
        }

        /* if (!SecurityUtil::confirmAuthKey()) {
          LogUtil::registerAuthidError();
          return AjaxUtil::error(null, array(), true, true);
          } */

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

        $forum_id = FormUtil::getPassedValue('forum', null, 'POST');
        $message = FormUtil::getPassedValue('message', '', 'POST');
        $subject = FormUtil::getPassedValue('subject', '', 'POST');
        $attach_signature = FormUtil::getPassedValue('attach_signature', null, 'POST');
        $subscribe_topic = FormUtil::getPassedValue('subscribe_topic', null, 'POST');
        $preview = (int)FormUtil::getPassedValue('preview', 0, 'POST');

        $cat_id = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category', array('forum_id' => $forum_id));

        $topic = array(
            'cat_id' => $cat_id,
            'forum_id' => $forum_id
        );
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $topic)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        $preview = ($preview == 1) ? true : false;
        //$attach_signature = ($attach_signature=='1') ? true : false;
        //$subscribe_topic  = ($subscribe_topic=='1') ? true : false;

        $message = ModUtil::apiFunc('Dizkus', 'user', 'dzkstriptags', $message);
        // check for maximum message size
        if ((strlen($message) + 8/* strlen('[addsig]') */) > 65535) {
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
            $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic', array('forum_id' => $forum_id,
                        'subject' => $subject,
                        'message' => $message,
                        'attach_signature' => $attach_signature,
                        'subscribe_topic' => $subscribe_topic));

            // `readtopic()` removed - when this method is refactored, the persisted topic is automatically available

            if (ModUtil::getVar('Dizkus', 'newtopicconfirmation') == 'yes') {
                $this->view->assign('topic', $topic);
                $confirmation = $this->view->fetch('ajax/newtopicconfirmation.tpl');
            } else {
                $confirmation = false;
            }

            // --- MediaAttach check ---
            if (ModUtil::available('MediaAttach') && ModUtil::isHooked('MediaAttach', 'Dizkus')) {
                return new Zikula_Response_Ajax(array('topic' => $topic,
                            'confirmation' => $confirmation,
                            'redirect' => ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id), null, null, true),
                            'uploadredirect' => urlencode(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id))),
                            'uploadobjectid' => $topic_id,
                            'uploadauthid' => SecurityUtil::generateCsrfToken('MediaAttach')));
            } else {
                return new Zikula_Response_Ajax(array('topic' => $topic,
                            'confirmation' => $confirmation,
                            'redirect' => ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id), null, null, true)));
            }
        }

        // preview == true, create fake topic
        $managedPoster = new Dizkus_Manager_ForumUser();
        $newtopic['cat_id'] = $cat_id;
        $newtopic['forum_id'] = $forum_id;
        $newtopic['topic_unixtime'] = time();
        $newtopic['poster_data'] = $managedPoster->toArray();
        $newtopic['subject'] = $subject;
        $newtopic['message'] = $message;
        $newtopic['message_display'] = $message; // $this->phpbb_br2nl($message);

        if (($attach_signature == 1) && (!empty($newtopic['poster_data']['signature']))) {
            $newtopic['message_display'] .= '[addsig]';
            $newtopic['message_display'] = $this->dzk_replacesignature(array('text' => $newtopic['message_display'], 'signature' => $newtopic['poster_data']['signature']));
        }

//      list($newtopic['message_display']) = ModUtil::callHooks('item', 'transform', '', array($newtopic['message_display']));
        $newtopic['message_display'] = ModUtil::apiFunc('Dizkus', 'user', 'dzkVarPrepHTMLDisplay', $newtopic['message_display']);

        if (UserUtil::isLoggedIn()) {
            // If it's the topic start
            if (empty($subject) && empty($message)) {
                $newtopic['attach_signature'] = 1;
                $newtopic['subscribe_topic'] = (ModUtil::getVar('Dizkus', 'autosubscribe') == 'yes') ? 1 : 0;
            } else {
                $newtopic['attach_signature'] = $attach_signature;
                $newtopic['subscribe_topic'] = $subscribe_topic;
            }
        } else {
            $newtopic['attach_signature'] = 0;
            $newtopic['subscribe_topic'] = 0;
        }

        $this->view->assign('newtopic', $newtopic);

        return new Zikula_Response_Ajax(array('data' => $this->view->fetch('user/topic/newpreview.tpl'),
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
            $fragment = $this->request->query->get('fragment', $this->request->request->get('fragment'));
            $users = ModUtil::apiFunc('Dizkus', 'user', 'getUsersByFragments', array('fragments' => array($fragment)));
            $view->assign('results', $users);
        }

        $output = $view->fetch('ajax/getusers.tpl');

        return new Zikula_Response_Ajax_Plain($output);
    }

    /**
     * removes instances of <br /> since sometimes they are stored in DB :(
     */
    public function phpbb_br2nl($str)
    {
        return preg_replace("=<br(>|([\s/][^>]*)>)\r?\n?=i", "\n", $str);
    }

    /**
     * dzk_replacesignature
     *
     */
    public function dzk_replacesignature($args)
    {
        $text = $args['text'];
        $signature = isset($args['signature']) ? $args['signature'] : '';
        $removesignature = ModUtil::getVar('Dizkus', 'removesignature');
        if ($removesignature == 'yes') {
            $signature = '';
        }

        if (!empty($signature)) {
            $sigstart = stripslashes(ModUtil::getVar('Dizkus', 'signature_start'));
            $sigend   = stripslashes(ModUtil::getVar('Dizkus', 'signature_end'));
            $text = preg_replace("/\[addsig]$/", "\n\n" . $sigstart . $signature . $sigend, $text);
        } else {
            $text = preg_replace("/\[addsig]$/", '', $text);
        }

        return $text;
    }

}
