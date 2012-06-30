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
        
        $viewcat   =  (int)$this->request->query->get('viewcat', (isset($args['viewcat'])) ? $args['viewcat'] : -1);
        $favorites = (bool)$this->request->query->get('favorites', (isset($args['favorites'])) ? $args['favorites'] : false);
    
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
            $tree = ModUtil::apiFunc('Dizkus', 'category', 'readcategorytree',
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
     *
     * opens a forum and shows the last postings
     *
     * @params 'forum' int the forum id
     * @params 'start' int the posting to start with if on page 1+
     *
     * @return string
     */
    public function viewforum($args=array())
    {
       return ModUtil::Func('Dizkus', 'Forum', 'viewforum',$args);
    }
    /**
     * viewtopic
     *
     */
    public function viewtopic($args=array())
    {        
      return ModUtil::Func('Dizkus', 'Topic', 'viewtopic',$args);
    }
    
    /**
     * reply
     *
     */
    public function reply($args=array())
    {
         return ModUtil::Func('Dizkus', 'Post', 'reply', $args);
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
     * editpost
     *
     */
    public function editpost($args=array())
    {
       return ModUtil::Func('Dizkus', 'Post', 'editpost', $args);
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
        $topic_id  = (int)$this->request->query->get('topic', (isset($args['topic'])) ? $args['topic'] : null);
        $act       = $this->request->query->get('act', (isset($args['act'])) ? $args['act'] : '');
        $return_to = $this->request->query->get('return_to', (isset($args['return_to'])) ? $args['return_to'] : '');
        $forum_id  = (int)$this->request->query->get('forum', (isset($args['forum'])) ? $args['forum'] : null);
        $user_id   = (int)$this->request->query->get('user', (isset($args['user'])) ? $args['user'] : null);
    
        // user_id will only be used if we have admin permissions otherwise the
        // user can edit his prefs only but not others users prefs
    
        switch ($act)
        {
            case 'subscribe_topic':
                $return_to = (!empty($return_to))? $return_to : 'viewtopic';
                ModUtil::apiFunc('Dizkus', 'topic', 'subscribe_topic',
                             array('topic_id' => $topic_id ));
                $params = array('topic' => $topic_id);
                break;
    
            case 'unsubscribe_topic':
                $return_to = (!empty($return_to))? $return_to : 'viewtopic';
                ModUtil::apiFunc('Dizkus', 'topic', 'unsubscribe_topic',
                             array('topic_id' => $topic_id ));
                $params = array('topic' => $topic_id);
                break;
    
            case 'subscribe_forum':
                $return_to = (!empty($return_to))? $return_to : 'viewforum';
                ModUtil::apiFunc('Dizkus', 'forum', 'subscribe_forum',
                             array('forum_id' => $forum_id ));
                $params = array('forum' => $forum_id);
                break;
    
            case 'unsubscribe_forum':
                $return_to = (!empty($return_to))? $return_to : 'viewforum';
                ModUtil::apiFunc('Dizkus', 'forum', 'unsubscribe_forum',
                             array('forum_id' => $forum_id ));
                $params = array('forum' => $forum_id);
                break;
    
            case 'add_favorite_forum':
                if (ModUtil::getVar('Dizkus', 'favorites_enabled')=='yes') {
                    $return_to = (!empty($return_to))? $return_to : 'viewforum';
                    ModUtil::apiFunc('Dizkus', 'forum', 'add_favorite_forum',
                                 array('forum_id' => $forum_id ));
                    $params = array('forum' => $forum_id);
                }
                break;
    
            case 'remove_favorite_forum':
                if (ModUtil::getVar('Dizkus', 'favorites_enabled')=='yes') {
                    $return_to = (!empty($return_to))? $return_to : 'viewforum';
                    ModUtil::apiFunc('Dizkus', 'forum', 'remove_favorite_forum',
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
                if (ModUtil::getVar('Dizkus', 'favorites_enabled')=='yes') {
                    $return_to = (!empty($return_to))? $return_to : 'main';
                    $favorites = ModUtil::apiFunc('Dizkus', 'user', 'change_favorite_status');
                    $params = array();
                }
                break;
            case 'showfavorites':
                if (ModUtil::getVar('Dizkus', 'favorites_enabled')=='yes') {
                    $return_to = (!empty($return_to))? $return_to : 'main';
                    $favorites = ModUtil::apiFunc('Dizkus', 'user', 'change_favorite_status');
                    $params = array();
                }
                break;

            case 'noautosubscribe':
            case 'autosubscribe':
                $return_to = (!empty($return_to))? $return_to : 'prefs';
                $nm = ModUtil::apiFunc('Dizkus', 'topic', 'togglenewtopicsubscription');
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
                $this->view->assign('tree', ModUtil::apiFunc('Dizkus', 'category', 'readcategorytree', array('last_visit' => $last_visit )));
    
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
     *
     * @return string
     */
    public function emailtopic()
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('topic/emailtopic.tpl', new Dizkus_Form_Handler_Topic_EmailTopic());
    }
    
    /**
     * latest
     *
     * @return string
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
        $selorder   = (int)$this->request->query->get('selorder', (isset($args['selorder'])) ? $args['selorder'] : 1);
        $nohours    = (int)$this->request->query->get('nohours', (isset($args['nohours'])) ? $args['nohours'] : null);
        $unanswered = (int)$this->request->query->get('unanswered', (isset($args['unanswered'])) ? $args['unanswered'] : 0);
        $amount     = (int)$this->request->query->get('amount', (isset($args['amount'])) ? $args['amount'] : null);
    
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
    
        list($posts, $m2fposts, $rssposts, $text) = ModUtil::apiFunc('Dizkus', 'post', 'get_latest_posts',
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
    
        return $this->view->fetch('post/latestposts.tpl');
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
        return ModUtil::Func('Dizkus', 'Topic', 'printtopic', $args);
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
     * jointopics
     * Join a topic with another toipic                                                                                                  ?>
     *
     */
    public function jointopics($args=array())
    {
       return ModUtil::Func('Dizkus', 'Topic', 'jointopic', $args);
    }
    
    /**
     * moderateforum
     *
     * Simple moderation of multiple topics.
     *
     * @param array $args The Arguments array.
     *
     * @return string
     */
    public function moderateforum($args=array())
    {
       return ModUtil::Func('Dizkus', 'Forum', 'moderateforum', $args);
    }
    
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
        return $form->execute('user/notifymod.tpl', new Dizkus_Form_Handler_Post_Report());
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