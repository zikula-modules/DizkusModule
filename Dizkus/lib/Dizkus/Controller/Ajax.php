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

include_once 'modules/Dizkus/common.php';

class Dizkus_Controller_Ajax extends Zikula_Controller {
    
    /**
     * reply
     */
    public function reply()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
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
            dzk_ajaxerror($this->__('Error! The user who started this topic is ignoring you, and does not want you to be able to write posts under this topic. Please contact the topic originator for more information.'));
        }
    
        // check for maximum message size
        if ((strlen($message) + 8/*strlen('[addsig]')*/) > 65535) {
            dzk_ajaxerror($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
        }
    
        if ($preview == false) {
            if (!SecurityUtil::confirmAuthKey()) {
               dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
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
                $post['post_textdisplay'] = Dizkus_replacesignature($post['post_textdisplay'], $post['poster_data']['_SIGNATURE']);
            }
            // call hooks for $message_display ($message remains untouched for the textarea)
            list($post['post_textdisplay']) = ModUtil::callHooks('item', 'transform', $post['post_id'], array($post['post_textdisplay']));
            $post['post_textdisplay']       = dzkVarPrepHTMLDisplay($post['post_textdisplay']);
    
            $post['post_text'] = $post['post_textdisplay'];
        }
    
        $render = Renderer::getInstance('Dizkus', false, null, true);
        $render->assign('topic', $topic);
        $render->assign('post', $post);
        $render->assign('preview', $preview);
    
        //---- begin of MediaAttach integration ----
        if (ModUtil::available('MediaAttach') && ModUtil::isHooked('MediaAttach', 'Dizkus')) {
            dzk_jsonizeoutput(array('data'    => $render->fetch('dizkus_user_singlepost.html'),
                                    'post_id' => $post['post_id'],
                                    'uploadauthid' => SecurityUtil::generateAuthKey('MediaAttach')),
                              true);
    
        } else {
            dzk_jsonizeoutput(array('data'    => $render->fetch('dizkus_user_singlepost.html'),
                                    'post_id' => $post['post_id']),
                              true);
        }
        //---- end of MediaAttach integration ----
    }
    
    /**
     * preparequote
     */
    public function preparequote()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $post_id = FormUtil::getPassedValue('post');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!empty($post_id)) {
            $post = ModUtil::apiFunc('Dizkus', 'user', 'preparereply',
                                 array('post_id'     => $post_id,
                                       'quote'       => true,
                                       'reply_start' => true));
            dzk_jsonizeoutput($post, false);
        }
    
        dzk_ajaxerror($this->__('Error! No post ID in \'Dizkus_ajax_preparequote()\'.'));
    }
    
    /**
     * readpost
     */
    public function readpost()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $post_id = FormUtil::getPassedValue('post');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!empty($post_id)) {
            $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                 array('post_id'     => $post_id));
            if ($post['poster_data']['edit'] == true) {
                dzk_jsonizeoutput($post, false);
            } else {
                dzk_ajaxerror($this->__('Error! You do not have authorisation to perform this action.'));
            }
        }
    
        dzk_ajaxerror($this->__('Error! No post ID in \'Dizkus_ajax_readpost()\'.'));
    }
    
    /**
     * editpost
     */
    public function editpost()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $post_id = FormUtil::getPassedValue('post');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!empty($post_id)) {
            $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost',
                                 array('post_id'     => $post_id));
    
            if ($post['poster_data']['edit'] == true) {
                $render = Renderer::getInstance('Dizkus', false, null, true);
    
                $render->assign('post', $post);
                // simplify our live
                $render->assign('postingtextareaid', 'postingtext_' . $post['post_id'] . '_edit');
    
                SessionUtil::delVar('zk_ajax_call');
    
                return array('data'    => $render->fetch('dizkus_ajax_editpost.html'),
                             'post_id' => $post['post_id']);
            } else {
                dzk_ajaxerror($this->__('Error! You do not have authorisation to perform this action.'));
            }
        }
    
        dzk_ajaxerror($this->__('Error! No post ID in \'Dizkus_ajax_readrawtext()\'.'));
    }
    
    /**
     * updatepost
     */
    public function updatepost()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $post_id = FormUtil::getPassedValue('post', '');
        $subject = FormUtil::getPassedValue('subject', '');
        $message = FormUtil::getPassedValue('message', '');
        $delete  = FormUtil::getPassedValue('delete');
        $attach_signature = FormUtil::getPassedValue('attach_signature');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!empty($post_id)) {
            if (!SecurityUtil::confirmAuthKey()) {
               dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
            }
     
            $message = dzkstriptags($message);
            // check for maximum message size
            if ((strlen($message) + 8/*strlen('[addsig]')*/) > 65535) {
                dzk_ajaxerror($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
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
                                  'redirect' => ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $orig_post['forum_id']), null, null, true),
                                  'post_id'  => $post_id);
                } else {
                    $post = array('action'  => 'deleted',
                                  'post_id' => $post_id);
                }
            }
    
            SessionUtil::delVar('zk_ajax_call');
    
            return $post;
        }
    
        dzk_ajaxerror($this->__('Error! No post ID in \'Dizkus_ajax_updatepost()\'.'));
    }
    
    /**
     * lockunlocktopic
     *
     */
    public function lockunlocktopic()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $topic_id = FormUtil::getPassedValue('topic', '');
        $mode     = FormUtil::getPassedValue('mode', '');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!SecurityUtil::confirmAuthKey()) {
            // dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
        }
    
        if (empty($topic_id)) {
            dzk_ajaxerror($this->__('Error! No topic ID in \'Dizkus_ajax_lockunlocktopic()\'.'));
        }
        if (empty($mode) || (($mode <> 'lock') && ($mode <> 'unlock')) ) {
            dzk_ajaxerror( $this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_lockunlocktopic()\'.', DataUtil::formatForDisplay($mode), $dom));
        }
    
        list($forum_id, $cat_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                array('topic_id' => $topic_id));
    
        if (!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
            return dzk_ajaxerror($this->__('Error! You do not have authorisation to moderate this category or forum.'));
        }
    
        ModUtil::apiFunc('Dizkus', 'user', 'lockunlocktopic',
                     array('topic_id' => $topic_id,
                           'mode'     => $mode));
    
        $newmode = ($mode=='lock') ? 'locked' : 'unlocked';
    
        dzk_jsonizeoutput($newmode);
    }
    
    /**
     * stickyunstickytopic
     *
     */
    public function stickyunstickytopic()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $topic_id = FormUtil::getPassedValue('topic', '');
        $mode     = FormUtil::getPassedValue('mode', '');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!SecurityUtil::confirmAuthKey()) {
            //dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
        }
    
        if (empty($topic_id)) {
            dzk_ajaxerror($this->__('Error! No topic ID in \'Dizkus_ajax_stickyunstickytopic()\'.'));
        }
        if (empty($mode) || (($mode <> 'sticky') && ($mode <> 'unsticky')) ) {
            dzk_ajaxerror( $this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_stickyunstickytopic()\'.', DataUtil::formatForDisplay($mode), $dom));
        }
    
        list($forum_id, $cat_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                array('topic_id' => $topic_id));
    
        if (!allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
            return dzk_ajaxerror($this->__('Error! You do not have authorisation to moderate this category or forum.'));
        }
    
        ModUtil::apiFunc('Dizkus', 'user', 'stickyunstickytopic',
                     array('topic_id' => $topic_id,
                           'mode'     => $mode));
    
        dzk_jsonizeoutput($mode);
    }
    
    /**
     * subscribeunsubscribetopic
     *
     */
    public function subscribeunsubscribetopic()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $topic_id = FormUtil::getPassedValue('topic', '');
        $mode     = FormUtil::getPassedValue('mode', '');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    /*
        if (!SecurityUtil::confirmAuthKey()) {
            dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
        }
    */
        if (empty($topic_id)) {
            dzk_ajaxerror($this->__('Error! No topic ID in \'Dizkus_ajax_subscribeunsubscribetopic()\'.'));
        }
    
        list($forum_id, $cat_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                array('topic_id' => $topic_id));
    
        if (!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            return dzk_ajaxerror($this->__('Error! You do not have authorisation to read the content in this category or forum.'));
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
                dzk_ajaxerror( $this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_subscribeunsubscribetopic()\'.', DataUtil::formatForDisplay($mode), $dom));
        }
    
        dzk_jsonizeoutput($newmode);
    }
    
    /**
     * subscribeunsubscribeforum
     *
     */
    public function subscribeunsubscribeforum()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $forum_id = FormUtil::getPassedValue('forum', '');
        $mode     = FormUtil::getPassedValue('mode', '');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    /*
        if (!SecurityUtil::confirmAuthKey()) {
            // dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
        }
    */
        if (empty($forum_id)) {
            dzk_ajaxerror($this->__('Error! No forum ID in \'Dizkus_ajax_subscribeunsubscribeforum()\'.'));
        }
    
        $cat_id = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category',
                               array('forum_id' => $forum_id));
    
        if (!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            return dzk_ajaxerror($this->__('Error! You do not have authorisation to read the content in this category or forum.'));
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
                dzk_ajaxerror( $this->__f('Error! No or illegal mode (%s) parameter in Dizkus_ajax_subscribeunsubscribeforum()', DataUtil::formatForDisplay($mode), $dom));
        }
    
        dzk_jsonizeoutput(array('newmode' => $newmode,
                                'forum_id' => $forum_id));
    }
    
    /**
     * addremovefavorite
     *
     */
    public function addremovefavorite()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        if (ModUtil::getVar('Dizkus', 'favorites_enabled') == 'no') {
            dzk_ajaxerror($this->__('Error! Favourites have been disabled.'));
        }
    
        $forum_id = FormUtil::getPassedValue('forum', '');
        $mode     = FormUtil::getPassedValue('mode', '');
    
        if (empty($forum_id)) {
            dzk_ajaxerror($this->__('Error! No forum ID in \'Dizkus_ajax_addremovefavorite()\'.'));
        }
    /*
        if (!SecurityUtil::confirmAuthKey()) {
            dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
        }
    */
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        $cat_id = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category',
                               array('forum_id' => $forum_id));
    
        if (!allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            return dzk_ajaxerror($this->__('Error! You do not have authorisation to read the content in this category or forum.'));
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
                dzk_ajaxerror( $this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus_ajax_addremovefavorite()\'.', DataUtil::formatForDisplay($mode), $dom));
        }
    
        dzk_jsonizeoutput(array('newmode' => $newmode,
                                'forum_id' => $forum_id));
    }
    
    /**
     * edittopicsubject
     *
     */
    public function edittopicsubject()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $topic_id = FormUtil::getPassedValue('topic', '');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!empty($topic_id)) {
            $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic',
                                 array('topic_id' => $topic_id,
                                       'count'    => false,
                                       'complete' => false));
    
            if ($topic['access_topicsubjectedit'] == true) {
                $render = Renderer::getInstance('Dizkus', false, null, true);
                $render->assign('topic', $topic);
    
                SessionUtil::delVar('zk_ajax_call');
    
                return array('data'     => $render->fetch('dizkus_ajax_edittopicsubject.html'),
                             'topic_id' => $topic_id);
            } else {
                dzk_ajaxerror($this->__('Error! You do not have authorisation to perform this action.'));
            }
        }
    
        dzk_ajaxerror($this->__('Error! No topic ID in \'Dizkus_ajax_readtopic()\'.'));
    }
    
    /**
     * updatetopicsubject
     */
    public function updatetopicsubject()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $topic_id = FormUtil::getPassedValue('topic', '');
        $subject  = FormUtil::getPassedValue('subject', '');
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!empty($topic_id)) {
            if (!SecurityUtil::confirmAuthKey()) {
                dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
            }
    
            $topicposter = DBUtil::selectFieldById('dizkus_topics', 'topic_poster', $topic_id, 'topic_id');
    
            list($forum_id, $cat_id) = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $topic_id));
            if (!allowedtomoderatecategoryandforum($cat_id, $forum_id) && UserUtil::getVar('uid') <> $topicposter) {
                dzk_ajaxerror($this->__('Error! You do not have authorisation to moderate this category or forum.'));
            }
    
            $subject = trim($subject);
            if (empty($subject)) {
                dzk_ajaxerror($this->__('Error! The post has no subject line.'));
            }
    
            $topic['topic_id']    = $topic_id;
            $topic['topic_title'] = $subject;
            $res = DBUtil::updateObject($topic, 'dizkus_topics', '', 'topic_id');
    
            // Let any hooks know that we have updated an item.
            ModUtil::callHooks('item', 'update', $topic_id, array('module'   => 'Dizkus',
                                                              'topic_id' => $topic_id));
    
            SessionUtil::delVar('zk_ajax_call');
    
            return array('topic_title' => DataUtil::formatForDisplay($subject),
                         'topic_id'    => $topic_id);
        }
    
        dzk_ajaxerror($this->__('Error! No topic ID in \'Dizkus_ajax_updatetopicsubject()\''));
    }
    
    /**
     * changesortorder
     *
     */
    public function changesortorder()
    {
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!UserUtil::isLoggedIn()) {
           dzk_ajaxerror($this->__('Error! This feature is for registered users only.'));
        }
    
        if (!SecurityUtil::confirmAuthKey()) {
            dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
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
           dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
        }
    */
        if (dzk_available(false) == false) {
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
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
            return dzk_ajaxerror($this->__('Error! You do not have authorisation to post in this category or forum.'));
        }
    
        $preview          = ($preview == 1) ? true : false;
        //$attach_signature = ($attach_signature=='1') ? true : false;
        //$subscribe_topic  = ($subscribe_topic=='1') ? true : false;
    
        $message = dzkstriptags($message);
        // check for maximum message size
        if ((strlen($message) + 8/*strlen('[addsig]')*/) > 65535) {
            dzk_ajaxerror($this->__('Error! The message is too long. The maximum length is 65,535 characters.'), true);
        }
        if (strlen($message) == 0) {
            dzk_ajaxerror($this->__('Error! You tried to post a blank message. Please go back and try again.'), true);
        }
    
        if (strlen($subject) == 0) {
            dzk_ajaxerror($this->__('Error! The post has no subject line.'), true);
        }
    
        $render = Renderer::getInstance('Dizkus', false, null, true);
    
        if ($preview == false) {
            if (!SecurityUtil::confirmAuthKey()) {
               dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
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
                $render->assign('topic', $topic);
                $confirmation = $render->fetch('dizkus_ajax_newtopicconfirmation.html');
            } else {
                $confirmation = false;
            }
    
            // --- MediaAttach check ---
            if (ModUtil::available('MediaAttach') && ModUtil::isHooked('MediaAttach', 'Dizkus')) {
                dzk_jsonizeoutput(array('topic'        => $topic,
                                        'confirmation' => $confirmation,
                                        'redirect'     => ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                   array('topic' => $topic_id),
                                                                   null, null, true),
                                        'uploadredirect' => urlencode(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                               array('topic' => $topic_id))),
                                        'uploadobjectid' => $topic_id,
                                        'uploadauthid' => SecurityUtil::generateAuthKey('MediaAttach')
                                       ),
                                  true);
    
            } else {
                dzk_jsonizeoutput(array('topic'        => $topic,
                                        'confirmation' => $confirmation,
                                        'redirect'     => ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                   array('topic' => $topic_id),
                                                                   null, null, true)
                                       ),
                                  true);
            }
        }
    
        // preview == true, create fake topic
        $newtopic['cat_id']     = $cat_id;
        $newtopic['forum_id']   = $forum_id;
        //$newtopic['forum_name'] = DataUtil::formatForDisplay($myrow['forum_name']);
        //$newtopic['cat_title']  = DataUtil::formatForDisplay($myrow['cat_title']);
    
        $newtopic['topic_unixtime'] = time();
    
        // need at least "comment" to add newtopic
        if (!allowedtowritetocategoryandforum($newtopic['cat_id'], $newtopic['forum_id'])) {
            // user is not allowed to post
            return LogUtil::registerPermissionError();
        }
    
        $newtopic['poster_data'] = Dizkus_userapi_get_userdata_from_id(array('userid' => UserUtil::getVar('uid')));
    
        $newtopic['subject'] = $subject;
        $newtopic['message'] = $message;
        $newtopic['message_display'] = $message; // phpbb_br2nl($message);
    
        if ($attach_signature == 1) {
            $newtopic['message_display'] .= '[addsig]';
            $newtopic['message_display'] = Dizkus_replacesignature($newtopic['message_display'], $newtopic['poster_data']['_SIGNATURE']);
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
    
        $render->assign('newtopic', $newtopic);
    
        SessionUtil::delVar('zk_ajax_call');
    
        return array('data'     => $render->fetch('dizkus_user_newtopicpreview.html'),
                     'newtopic' => $newtopic);
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
            dzk_ajaxerror(strip_tags(ModUtil::getVar('Dizkus', 'forum_disabled_info')));
        }
    
        $render = Renderer::getInstance('Dizkus', false);
    
        if (System::getVar('shorturls')) {
            include_once('lib/render/plugins/outputfilter.shorturls.php');
            $render->register_outputfilter('smarty_outputfilter_shorturls');
        }
    
        $render->display('dizkus_ajax_forumusers.html');
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
        if (!is_bool($disabled = dzk_available())) {
            echo $disabled;
            System::shutDown();
        }
    
        $render = Renderer::getInstance('Dizkus', false);
    
        if (System::getVar('shorturls')) {
            include_once 'lib/render/plugins/outputfilter.shorturls.php';
            $render->register_outputfilter('smarty_outputfilter_shorturls');
        }
    
        $out = $render->fetch('dizkus_ajax_newposts.html');
        echo $out;
        System::shutDown();
    }

}
