<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Controller_Ajax extends Zikula_Controller {

    /**
     * reply
     */
    public function reply()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $topic_id         = FormUtil::getPassedValue('topic');
        $message          = FormUtil::getPassedValue('message', '');
        $title            = FormUtil::getPassedValue('title', '');
        $attach_signature = FormUtil::getPassedValue('attach_signature');
        $subscribe_topic  = FormUtil::getPassedValue('subscribe_topic');
        $preview          = FormUtil::getPassedValue('preview', 0);
        $preview          = ($preview == '1') ? true : false;

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        $message = dzkstriptags($message);
        $title   = dzkstriptags($title);

        // ContactList integration: Is the user ignored and allowed to write an answer to this topic?
        $topic = DBUtil::selectObjectByID('dizkus_topics', $topic_id, 'topic_id');
        $topic['start'] = 0;
        $ignorelist_setting = ModUtil::apiFunc('Dizkus', 'user', 'get_settings_ignorelist', array('uid' => $topic['topic_poster']));
        if (ModUtil::available('ContactList') && ($ignorelist_setting == 'strict') && (ModUtil::apiFunc('ContactList', 'user', 'isIgnored', array('uid' => (int)$topic['topic_poster'], 'iuid' => UserUtil::getVar('uid'))))) {
            return AjaxUtil::error($this->__('Error! The user who started this topic is ignoring you, and does not want you to be able to write posts under this topic. Please contact the topic originator for more information.'), array(), true, true, '403 Forbidden');
        }

        // check for maximum message size
        if ((strlen($message) + 8/*strlen('[addsig]')*/) > 65535) {
            return AjaxUtil::error($this->__('Error! The message is too long. The maximum length is 65,535 characters.'), array(), true, true, '404 Bad Data');
        }

        if ($preview == false) {
            if (!SecurityUtil::confirmAuthKey()) {
                return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
            }

            list($start,
                 $post_id) = ModUtil::apiFunc('Dizkus', 'user', 'storereply',
                                           array('topic_id'         => $topic_id,
                                                 'message'          => $message,
                                                 'attach_signature' => $attach_signature,
                                                 'subscribe_topic'  => $subscribe_topic,
                                                 'title'            => $title));

            $topic['start'] = $start;
            $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                 array('post_id' => $post_id));

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
                $post['post_textdisplay'] = Dizkus_replacesignature($post['post_textdisplay'], $post['poster_data']['signature']);
            }
            // call hooks for $message_display ($message remains untouched for the textarea)
            list($post['post_textdisplay']) = ModUtil::callHooks('item', 'transform', $post['post_id'], array($post['post_textdisplay']));
            $post['post_textdisplay']       = dzkVarPrepHTMLDisplay($post['post_textdisplay']);

            $post['post_text'] = $post['post_textdisplay'];
        }

        $this->view->add_core_data();
        $this->view->setCaching(false);
        $this->view->assign('topic', $topic);
        $this->view->assign('post', $post);
        $this->view->assign('preview', $preview);

        //---- begin of MediaAttach integration ----
        if (ModUtil::available('MediaAttach') && ModUtil::isHooked('MediaAttach', 'Dizkus')) {
            AjaxUtil::output(array('data'    => $this->view->fetch('dizkus_user_singlepost.html'),
                                    'post_id' => $post['post_id'],
                                    'uploadauthid' => SecurityUtil::generateAuthKey('MediaAttach')),
                             true, false, false);

        } else {
            AjaxUtil::output(array('data'    => $this->view->fetch('dizkus_user_singlepost.html'),
                                    'post_id' => $post['post_id']),
                              true, false, false);
        }
        //---- end of MediaAttach integration ----
    }

    /**
     * preparequote
     */
    public function preparequote()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $post_id = FormUtil::getPassedValue('post');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($post_id)) {
            $post = ModUtil::apiFunc('Dizkus', 'user', 'preparereply',
                                 array('post_id'     => $post_id,
                                       'quote'       => true,
                                       'reply_start' => true));
            AjaxUtil::output($post, true, false, false);
        }

        return AjaxUtil::error($this->__('Error! No post ID in \'Dizkus_ajax_preparequote()\'.'), array(), true, true, '403 Forbidden');
    }

    /**
     * readpost
     */
    public function readpost()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $post_id = FormUtil::getPassedValue('post');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($post_id)) {
            $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                 array('post_id'     => $post_id));
            if ($post['poster_data']['edit'] == true) {
                dzk_jsonizeoutput($post, false);
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
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $post_id = FormUtil::getPassedValue('post');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($post_id)) {
            $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                 array('post_id'     => $post_id));

            if ($post['poster_data']['edit'] == true) {
                $this->view->add_core_data();
                $this->view->setCaching(false);

                $this->view->assign('post', $post);
                // simplify our live
                $this->view->assign('postingtextareaid', 'postingtext_' . $post['post_id'] . '_edit');

                SessionUtil::delVar('zk_ajax_call');

                AjaxUtil::output(array('data' => $this->view->fetch('dizkus_ajax_editpost.html')),
                                 true, false, false);
            } else {
                LogUtil::registerPermissionError();
                return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
            }
        }
        
        return AjaxUtil::error($this->__('Error! No post ID in \'Dizkus_ajax_readrawtext()\'.'), array(), true, true, '400 Bad Data');
    }

    /**
     * updatepost
     */
    public function updatepost()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $post_id = FormUtil::getPassedValue('post', '');
        $subject = FormUtil::getPassedValue('subject', '');
        $message = FormUtil::getPassedValue('message', '');
        $delete  = FormUtil::getPassedValue('delete');
        $attach_signature = FormUtil::getPassedValue('attach_signature');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($post_id)) {
            if (!SecurityUtil::confirmAuthKey()) {
                LogUtil::registerAuthidError();
                return AjaxUtil::error(null, array(), true, true, '400 Bad Data');
            }

            $message = dzkstriptags($message);
            // check for maximum message size
            if ((strlen($message) + 8/*strlen('[addsig]')*/) > 65535) {
                return AjaxUtil::error($this->__('Error! The message is too long. The maximum length is 65,535 characters.'), array(), true, true, '400 Bad Data');
            }

            // read the original posting to get the forum id we might need later if the topic has been erased
            $orig_post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                      array('post_id'     => $post_id));

            ModUtil::apiFunc('Dizkus', 'user', 'updatepost',
                         array('post_id'          => $post_id,
                               'subject'          => $subject,
                               'message'          => $message,
                               'delete'           => $delete,
                               'attach_signature' => ($attach_signature==1)));

            if ($delete <> '1') {
                $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                     array('post_id'     => $post_id));
                $post['action'] = 'updated';
            } else {
                // try to read topic
                $topic = false;
                if (is_array($orig_post) && !empty($orig_post['topic_id'])) {
                    $topic = DBUtil::selectObject('dizkus_topics', 'topic_id='.DataUtil::formatForStore($orig_post['topic_id']));
                }
                if (!is_array($topic)) {
                    // topic has been deleted
                    $post = array('action'   => 'topic_deleted',
                                  'redirect' => ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $orig_post['forum_id']), null, null, true));
                } else {
                    $post = array('action'  => 'deleted');
                }
            }

            SessionUtil::delVar('zk_ajax_call');

            AjaxUtil::output($post, true, false, false);
        }

        return AjaxUtil::error($this->__('Error! No post ID in \'Dizkus_ajax_updatepost()\'.'), array(), true, true, '400 Bad Data');
    }

    /**
     * lockunlocktopic
     *
     */
    public function lockunlocktopic()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $topic_id = FormUtil::getPassedValue('topic', '');
        $mode     = FormUtil::getPassedValue('mode', '');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (empty($topic_id)) {
            return AjaxUtil::error($this->__('Error! No topic ID in \'Dizkus_ajax_lockunlocktopic()\'.'), array(), true, true, '400 Bad Data');
        }
        if (empty($mode) || (($mode <> 'lock') && ($mode <> 'unlock')) ) {
            return AjaxUtil::error($this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_lockunlocktopic()\'.', DataUtil::formatForDisplay($mode)), array(), true, true, '400 Bad Data');
        }

        list($forum_id, $cat_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                array('topic_id' => $topic_id));

        if (!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
            LogUtil::registerPermissionError();
            return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
        }

        ModUtil::apiFunc('Dizkus', 'user', 'lockunlocktopic',
                     array('topic_id' => $topic_id,
                           'mode'     => $mode));

        $newmode = ($mode=='lock') ? 'locked' : 'unlocked';

        AjaxUtil::output($newmode, true, false, false);
    }

    /**
     * stickyunstickytopic
     *
     */
    public function stickyunstickytopic()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $topic_id = FormUtil::getPassedValue('topic', '');
        $mode     = FormUtil::getPassedValue('mode', '');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (empty($topic_id)) {
            return AjaxUtil::error($this->__('Error! No topic ID in \'Dizkus_ajax_stickyunstickytopic()\'.'), array(), true, true, '400 Bad Data');
        }
        if (empty($mode) || (($mode <> 'sticky') && ($mode <> 'unsticky')) ) {
            return AjaxUtil::error($this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_stickyunstickytopic()\'.', DataUtil::formatForDisplay($mode)), array(), true, true, '400 Bad Data');
        }

        list($forum_id, $cat_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                array('topic_id' => $topic_id));

        if (!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
            LogUtil::registerPermissionError();
            return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
        }

        ModUtil::apiFunc('Dizkus', 'user', 'stickyunstickytopic',
                     array('topic_id' => $topic_id,
                           'mode'     => $mode));

        AjaxUtil::output($mode, true, false, false);
    }

    /**
     * subscribeunsubscribetopic
     *
     */
    public function subscribeunsubscribetopic()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $topic_id = FormUtil::getPassedValue('topic', '');
        $mode     = FormUtil::getPassedValue('mode', '');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (empty($topic_id)) {
            return AjaxUtil::error($this->__('Error! No topic ID in \'Dizkus_ajax_subscribeunsubscribetopic()\'.'), array(), true, true, '400 Bad Data');
        }

        list($forum_id, $cat_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                array('topic_id' => $topic_id));

        if (!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            LogUtil::registerPermissionError();
            return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
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
                return AjaxUtil::error($this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_subscribeunsubscribetopic()\'.', DataUtil::formatForDisplay($mode)), array(), true, true, '400 Bad Data');
        }

        AjaxUtil::output($newmode, true, false, false);
    }

    /**
     * subscribeunsubscribeforum
     *
     */
    public function subscribeunsubscribeforum()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $forum_id = FormUtil::getPassedValue('forum', '');
        $mode     = FormUtil::getPassedValue('mode', '');

        SessionUtil::setVar('zk_ajax_call', 'ajax');
    /*
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            return AjaxUtil::error(null, array(), true, true);
        }
    */
        if (empty($forum_id)) {
            return AjaxUtil::error($this->__('Error! No forum ID in \'Dizkus_ajax_subscribeunsubscribeforum()\'.'), array(), true, true, '400 Bad Data');
        }

        $cat_id = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category',
                               array('forum_id' => $forum_id));

        if (!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            LogUtil::registerPermissionError();
            return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
        }

        switch ($mode)
        {
            case 'subscribe':
                ModUtil::apiFunc('Dizkus', 'user', 'subscribe_forum',
                             array('forum_id' => $forum_id,
                                   'silent'   => true));
                $newmode = 'subscribed';
                break;

            case 'unsubscribe':
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_forum',
                             array('forum_id' => $forum_id,
                                   'silent'   => true));
                $newmode = 'unsubscribed';
                break;

            default:
                return AjaxUtil::error($this->__f('Error! No or illegal mode (%s) parameter in Dizkus_ajax_subscribeunsubscribeforum()', DataUtil::formatForDisplay($mode)), array(), true, true, '400 Bad Data');
        }

        AjaxUtil::output($newmode, true, false, false);
    }

    /**
     * addremovefavorite
     *
     */
    public function addremovefavorite()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        if (ModUtil::getVar('Dizkus', 'favorites_enabled') == 'no') {
            return AjaxUtil::error($this->__('Error! Favourites have been disabled.'), array(), true, true, '400 Bad Data');
        }

        $forum_id = FormUtil::getPassedValue('forum', '');
        $mode     = FormUtil::getPassedValue('mode', '');

        if (empty($forum_id)) {
            return AjaxUtil::error($this->__('Error! No forum ID in \'Dizkus_ajax_addremovefavorite()\'.'), array(), true, true, '400 Bad Data');
        }
    /*
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            return AjaxUtil::error(null, array(), true, true);
        }
    */
        SessionUtil::setVar('zk_ajax_call', 'ajax');

        $cat_id = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category',
                               array('forum_id' => $forum_id));

        if (!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            LogUtil::registerPermissionError();
            return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
        }

        switch ($mode)
        {
            case 'add':
                ModUtil::apiFunc('Dizkus', 'user', 'add_favorite_forum',
                             array('forum_id' => $forum_id ));
                $newmode = 'added';
                break;

            case 'remove':
                ModUtil::apiFunc('Dizkus', 'user', 'remove_favorite_forum',
                             array('forum_id' => $forum_id ));
                $newmode = 'removed';
                break;

            default:
                return AjaxUtil::error($this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_addremovefavorite()\'.', DataUtil::formatForDisplay($mode)), array(), true, true, '400 Bad Data');
        }

        AjaxUtil::output($newmode, true, false, false);
    }

    /**
     * edittopicsubject
     *
     */
    public function edittopicsubject()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $topic_id = FormUtil::getPassedValue('topic', '');

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

                AjaxUtil::output(array('data' => $this->view->fetch('dizkus_ajax_edittopicsubject.html')),
                                 true, false, false); 
            } else {
                LogUtil::registerPermissionError();
                return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
            }
        }

        return AjaxUtil::error($this->__('Error! No topic ID in \'Dizkus_ajax_readtopic()\'.'), array(), true, true);
    }

    /**
     * updatetopicsubject
     */
    public function updatetopicsubject()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $topic_id = FormUtil::getPassedValue('topic', '');
        $subject  = FormUtil::getPassedValue('subject', '');

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!empty($topic_id)) {
            if (!SecurityUtil::confirmAuthKey()) {
                LogUtil::registerAuthidError();
                return AjaxUtil::error(null, array(), true, true);
            }

            $topicposter = DBUtil::selectFieldById('dizkus_topics', 'topic_poster', $topic_id, 'topic_id');

            list($forum_id, $cat_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $topic_id));
            if (!allowedtomoderatecategoryandforum($cat_id, $forum_id) && UserUtil::getVar('uid') <> $topicposter) {
                LogUtil::registerPermissionError();
                return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
            }

            $subject = trim($subject);
            if (empty($subject)) {
                return AjaxUtil::error($this->__('Error! The post has no subject line.'), array(), true, true);
            }

            $topic['topic_id']    = $topic_id;
            $topic['topic_title'] = $subject;
            $res = DBUtil::updateObject($topic, 'dizkus_topics', '', 'topic_id');

            // Let any hooks know that we have updated an item.
            ModUtil::callHooks('item', 'update', $topic_id, array('module'   => 'Dizkus',
                                                              'topic_id' => $topic_id));

            SessionUtil::delVar('zk_ajax_call');

            AjaxUtil::output(array('topic_title' => DataUtil::formatForDisplay($subject)),
                             true, false, false);
        }

        return AjaxUtil::error($this->__('Error! No topic ID in \'Dizkus_ajax_updatetopicsubject()\''), array(), true, true, '400 Bad Data');
    }

    /**
     * changesortorder
     *
     */
    public function changesortorder()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        if (!UserUtil::isLoggedIn()) {
            return AjaxUtil::error($this->__('Error! This feature is for registered users only.'), array(), true, true, '400 Bad Data');
        }

        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            return AjaxUtil::error(null, array(), true, true, '400 Bad data');
        }

        ModUtil::apiFunc('Dizkus', 'user', 'change_user_post_order');
        $newmode = strtolower(ModUtil::apiFunc('Dizkus','user','get_user_post_order'));

        dzk_jsonizeoutput($newmode, true, true);
    }

    /**
     * newtopic
     *
     */
    public function newtopic()
    {
    /*
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            return AjaxUtil::error(null, array(), true, true);
        }
    */
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        SessionUtil::setVar('zk_ajax_call', 'ajax');

        $forum_id         = FormUtil::getPassedValue('forum');
        $message          = FormUtil::getPassedValue('message', '');
        $subject          = FormUtil::getPassedValue('subject', '');
        $attach_signature = FormUtil::getPassedValue('attach_signature');
        $subscribe_topic  = FormUtil::getPassedValue('subscribe_topic');
        $preview          = (int)FormUtil::getPassedValue('preview', 0);

        $cat_id = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category',
                               array('forum_id' => $forum_id));

        if (!allowedtowritetocategoryandforum($cat_id, $forum_id)) {
            LogUtil::registerPermissionError();
            return AjaxUtil::error(null, array(), true, true, '403 Forbidden');
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
            if (!SecurityUtil::confirmAuthKey()) {
                LogUtil::registerAuthidError();
                return AjaxUtil::error(null, array(), true, true);
            }

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
                $confirmation = $this->view->fetch('dizkus_ajax_newtopicconfirmation.html');
            } else {
                $confirmation = false;
            }

            // --- MediaAttach check ---
            if (ModUtil::available('MediaAttach') && ModUtil::isHooked('MediaAttach', 'Dizkus')) {
                AjaxUtil::output(array('topic'        => $topic,
                                       'confirmation' => $confirmation,
                                       'redirect'     => ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                  array('topic' => $topic_id),
                                                                  null, null, true),
                                       'uploadredirect' => urlencode(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                              array('topic' => $topic_id))),
                                       'uploadobjectid' => $topic_id,
                                       'uploadauthid' => SecurityUtil::generateAuthKey('MediaAttach')
                                      ),
                                 true, false, false);

            } else {
                AjaxUtil::output(array('topic'        => $topic,
                                       'confirmation' => $confirmation,
                                       'redirect'     => ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                  array('topic' => $topic_id),
                                                                  null, null, true)
                                      ),
                                 true, false, false);
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
            $newtopic['message_display'] = Dizkus_replacesignature($newtopic['message_display'], $newtopic['poster_data']['signature']);
        }

        list($newtopic['message_display']) = ModUtil::callHooks('item', 'transform', '', array($newtopic['message_display']));
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

        SessionUtil::delVar('zk_ajax_call');

        AjaxUtil::output(array('data'     => $this->view->fetch('dizkus_user_newtopicpreview.html'),
                               'newtopic' => $newtopic),
                         true, false, false);
    }

    /**
     * forumusers
     * update the "users online" section in the footer
     * original version by gf
     *
     */
    public function forumusers()
    {
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $this->view->add_core_data();
        $this->view->setCaching(false);
        if (System::getVar('shorturls')) {
            include_once('lib/render/plugins/outputfilter.shorturls.php');
            $this->view->register_outputfilter('smarty_outputfilter_shorturls');
        }

        $this->view->display('dizkus_ajax_forumusers.html');
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
        if (dzk_available(false) == false) {
            return AjaxUtil::error(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')), array(), true, true, '400 Bad Data');
        }

        $this->view->add_core_data();
        $this->view->setCaching(false);
        if (System::getVar('shorturls')) {
            include_once 'lib/render/plugins/outputfilter.shorturls.php';
            $this->view->register_outputfilter('smarty_outputfilter_shorturls');
        }

        $out = $this->view->fetch('dizkus_ajax_newposts.html');
        echo $out;
        System::shutDown();
    }

}
