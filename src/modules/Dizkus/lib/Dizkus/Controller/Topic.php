<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Controller_Topic extends Zikula_AbstractController
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
      return System::redirect(ModUtil::url('Dizkus', 'user', 'main', $args));
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
        $topic_id = (int)$this->request->query->get('topic', (isset($args['topic'])) ? $args['topic'] : null);
        // begin patch #3494 part 1, credits to teb
        $post_id  = (int)$this->request->query->get('post', (isset($args['post'])) ? $args['post'] : null);
        // end patch #3494 part 1
        $start    = (int)$this->request->query->get('start', (isset($args['start'])) ? $args['start'] : 0);
        $view     = strtolower($this->request->query->get('view', (isset($args['view'])) ? $args['view'] : ''));
    
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc($this->name, 'user', 'setcookies');
    
        if (!empty($view) && ($view=='next' || $view=='previous')) {
            $topic_id = ModUtil::apiFunc($this->name, 'topic', 'get_previous_or_next_topic_id',
                                     array('topic_id' => $topic_id,
                                           'view'     => $view));
            return System::redirect(ModUtil::url($this->name, 'user', 'viewtopic',
                                array('topic' => $topic_id)));
        }
    
        // begin patch #3494 part 2, credits to teb
        if (!empty($post_id) && is_numeric($post_id) && empty($topic_id)) {
            $topic_id = ModUtil::apiFunc($this->name, 'topic', 'get_topicid_by_postid', array('post_id' => $post_id));
            if ($topic_id <> false) {
                // redirect instad of continue, better for SEO
                return System::redirect(ModUtil::url($this->name, 'user', 'viewtopic', 
                                           array('topic' => $topic_id)));
            }
        }
        // end patch #3494 part 2
    
        $topic = ModUtil::apiFunc($this->name, 'Topic', 'read',
                              array('topic_id'   => $topic_id,
                                    'start'      => $start,
                                    'count'      => true));
    
        $this->view->assign('topic', $topic);
        $this->view->assign('post_count', count($topic['posts']));
        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);
        $this->view->assign('favorites', ModUtil::apifunc($this->name, 'user', 'get_favorite_status'));
    
        return $this->view->fetch('topic/viewtopic.tpl');
    }
   
    
    /**
     * newtopic
     *
     */
    public function newtopic()
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('topic/newtopic.tpl', new Dizkus_Form_Handler_Topic_NewTopic());
    }
    

    /**
     * Delete topic
     *
     * @return string
     */
    public function deletetopic() {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('topic/deletetopic.tpl', new Dizkus_Form_Handler_Topic_DeleteTopic());
    }
    
      /**
     * Delete topic
     *
     * @return string
     */
    public function movetopic() {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('topic/movetopic.tpl', new Dizkus_Form_Handler_Topic_MoveTopic());
    }
    
    
    
  
    
   
    /**
     * emailtopic
     *
     * @return string
     */
    public function emailtopic()
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('topic/emailtopic.tpl', new Dizkus_Form_Handler_Topic_EmailTopic());
    }
    

    
    /**
     * Split topic
     *
     * @return string
     */
    public function splittopic()
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('topic/splittopic.tpl', new Dizkus_Form_Handler_Topic_SplitTopic());
    }
    
    /**
     * print
     *
     * prepare print view of the selected posting or topic
     *
     * @return string
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
        $post_id  = (int)$this->request->query->get('post', (isset($args['post'])) ? $args['post'] : null);
        $topic_id = (int)$this->request->query->get('topic', (isset($args['topic'])) ? $args['topic'] : null);
    
        if (useragent_is_bot() == true) {
            if ($post_id <> 0 ) {
                $topic_id = ModUtil::apiFunc('Dizkus', 'topic', 'get_topicid_by_postid',
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
                $post = ModUtil::apiFunc('Dizkus', 'post', 'readpost',
                                     array('post_id' => $post_id));
    
                $this->view->assign('post', $post);
    
                $output = $this->view->fetch('post/printpost.tpl');
            } elseif ($topic_id <> 0) {
                $topic = ModUtil::apiFunc('Dizkus', 'topic', 'readtopic',
                                     array('topic_id'  => $topic_id,
                                           'complete' => true,
                                           'count' => false ));
    
                $this->view->assign('topic', $topic);
    
                $output = $this->view->fetch('topic/printtopic.tpl');
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
     * jointopics
     * Join a topic with another toipic                                                                                                  ?>
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
        $post_id       = (int)$this->request->query->get('post_id', (isset($args['post_id'])) ? $args['post_id'] : null);
        $submit        = $this->request->query->get('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
        $to_topic_id   = (int)$this->request->query->get('to_topic_id', (isset($args['to_topic_id'])) ? $args['to_topic_id'] : null);
        $from_topic_id = (int)$this->request->query->get('from_topic_id', (isset($args['from_topic_id'])) ? $args['from_topic_id'] : null);
    
        $post = ModUtil::apiFunc('Dizkus', 'post', 'readpost', array('post_id' => $post_id));
    
        if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
        if (!$submit) {
            $this->view->assign('post', $post);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
            return $this->view->fetch('topic/jointopics.tpl');
    
        } else {
            /*if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }*/
    
            // check if from_topic exists. this function will return an error if not
            $from_topic = ModUtil::apiFunc('Dizkus', 'topic', 'readtopic', array('topic_id' => $from_topic_id, 'complete' => false, 'count' => false));
            // check if to_topic exists. this function will return an error if not
            $to_topic = ModUtil::apiFunc('Dizkus', 'topic', 'readtopic', array('topic_id' => $to_topic_id, 'complete' => false, 'count' => false));
            // submit is set, we split the topic now
            //$post['new_topic'] = $totopic;
            //$post['old_topic'] = $old_topic;
            $res = ModUtil::apiFunc('Dizkus', 'topic', 'jointopics', array('from_topic' => $from_topic,
                                                                       'to_topic'   => $to_topic));
    
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $res)));
        }
    }
    

    
    /**
     * topicsubscriptions
     *
     * Manage the users topic subscription.
     *
     * @return string
     */
    public function topicsubscriptions()
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('topic/topicsubscriptions.tpl', new Dizkus_Form_Handler_Topic_TopicSubscriptions());
    }

}