<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Controller_Post extends Zikula_AbstractController
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
        $topic_id = (int)$this->request->query->get('topic', (isset($args['topic'])) ? $args['topic'] : null);
        $post_id  = (int)$this->request->query->get('post', (isset($args['post'])) ? $args['post'] : null);
        $message  = $this->request->query->get('message', (isset($args['message'])) ? $args['message'] : '');
        $attach_signature = (int)$this->request->query->get('attach_signature', (isset($args['attach_signature'])) ? $args['attach_signature'] : 0);
        $subscribe_topic = (int)$this->request->query->get('subscribe_topic', (isset($args['subscribe_topic'])) ? $args['subscribe_topic'] : 0);
        $preview = $this->request->query->get('preview', (isset($args['preview'])) ? $args['preview'] : '');
        $submit = $this->request->query->get('submit', (isset($args['submit'])) ? $args['submit'] : '');
        $cancel = $this->request->query->get('cancel', (isset($args['cancel'])) ? $args['cancel'] : '');
            
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
            $topic = ModUtil::apiFunc('Dizkus', 'topic', 'readtopci0', $topic_id);
            $ignorelist_setting = ModUtil::apiFunc('Dizkus','user','get_settings_ignorelist',array('uid' => $topic['topic_poster']));
            if (ModUtil::available('ContactList') && ($ignorelist_setting == 'strict') && (ModUtil::apiFunc('ContactList','user','isIgnored',array('uid' => (int)$topic['topic_poster'], 'iuid' => UserUtil::getVar('uid'))))) {
                return LogUtil::registerError($this->__('Error! The user who started this topic is ignoring you, and does not want you to be able to write posts under this topic. Please contact the topic originator for more information.'), null, ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
            }
    
            list($start,
                 $post_id ) = ModUtil::apiFunc('Dizkus', 'post', 'storereply',
                                           array('topic_id'         => $topic_id,
                                                 'message'          => $message,
                                                 'attach_signature' => $attach_signature,
                                                 'subscribe_topic'  => $subscribe_topic));
    
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                array('topic' => $topic_id,
                                      'start' => $start)) . '#pid' . $post_id);
        } else {
            list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $reply = ModUtil::apiFunc('Dizkus', 'post', 'preparereply',
                                  array('topic_id'   => $topic_id,
                                        'post_id'    => $post_id,
                                        'last_visit' => $last_visit,
                                        'reply_start'=> empty($message),
                                        'attach_signature' => $attach_signature,
                                        'subscribe_topic'  => $subscribe_topic));
            if ($preview == true) {
                $reply['message'] = dzkVarPrepHTMLDisplay($message);
                //list($reply['message_display']) = ModUtil::callHooks('item', 'transform', '', array($message));
                $reply['message_display'] = nl2br($reply['message_display']);
            }

            $this->view->assign('reply', $reply);
            $this->view->assign('preview', $preview);
            $this->view->assign('last_visit', $last_visit);
            $this->view->assign('last_visit_unix', $last_visit_unix);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
            return $this->view->fetch('post/reply.tpl');
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
        $topic_id = (int)$this->request->query->get('topic', (isset($args['topic'])) ? $args['topic'] : null);
        $post_id  = (int)$this->request->query->get('post', (isset($args['post'])) ? $args['post'] : null);
        $subject  = $this->request->query->get('subject', (isset($args['subject'])) ? $args['subject'] : '');
        $message  = $this->request->query->get('message', (isset($args['message'])) ? $args['message'] : '');
        $attach_signature = (int)$this->request->query->get('attach_signature', (isset($args['attach_signature'])) ? $args['attach_signature'] : 0);
        $delete   = $this->request->query->get('delete', (isset($args['delete'])) ? $args['delete'] : '');
        $preview  = $this->request->query->get('preview', (isset($args['preview'])) ? $args['preview'] : '');
        $submit   = $this->request->query->get('submit', (isset($args['submit'])) ? $args['submit'] : '');
        $cancel   = $this->request->query->get('cancel', (isset($args['cancel'])) ? $args['cancel'] : '');
                    
        if (empty($post_id) || !is_numeric($post_id)) {
            return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        $post = ModUtil::apiFunc('Dizkus', 'post', 'readpost',
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
            $redirect = ModUtil::apiFunc('Dizkus', 'post', 'updatepost',
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
            $firstpost = ModUtil::apiFunc('Dizkus', 'topic', 'get_firstlast_post_in_topic',
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
    
            return $this->view->fetch('post/editpost.tpl');
        }
    }
   
    /**
     * movepost
     * 
     * Move a single post to another thread
     *
     * @return string
     */
    public function movepost()
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('post/movepost.tpl', new Dizkus_Form_Handler_Post_MovePost());
    }
    
    /**
     
    /**
     * report
     *
     * Notify a moderator about a posting.
     *
     * @return string
     */
    public function report()
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('post/notifymod.tpl', new Dizkus_Form_Handler_Post_Report());
    }
    
       public function isSpam($message)
    {        
        // Akismet
        if (ModUtil::available('Akismet') && $this->getVar('spam_protector') == 'Akismet') {
            if (ModUtil::apiFunc('Akismet', 'user', 'isspam', array('content' => $message))) {
                return true;
            }
        }
        
        return false;
    }

}