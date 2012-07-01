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
    
     public function viewip()
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
    if ($this->request->isPost()) {
        $post_id  = (int)$this->request->request->get('post', (isset($args['post'])) ? $args['post'] : null);
        } else {
        $post_id  = (int)$this->request->query->get('post', (isset($args['post'])) ? $args['post'] : null);
        }

        $this->view->assign('viewip', ModUtil::apiFunc('Dizkus', 'user', 'get_viewip_data', array('post_id' => $post_id)));
        $templatename = 'user/viewip.tpl';
  
        $this->view->add_core_data();
        $this->view->setCaching(false);
        $this->view->assign('mode', $mode);
        $this->view->assign('topic_id', ModUtil::apifunc('Dizkus', 'topic', 'get_topicid_by_postid', $post_id));
        $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));

        return $this->view->fetch($templatename);
    }
    
    
    /**
     * Forum to remove?
     */
     
    public function viewforum($args=array())
    {
       return ModUtil::Func('Dizkus', 'Forum', 'viewforum',$args);
    }
    
     /**
     * moderateforum will be removed
     */
    public function moderateforum($args=array())
    {
       return ModUtil::Func('Dizkus', 'Forum', 'moderateforum', $args);
    }
    
     /**
     * viewtopic
     *
     */
    public function viewtopic($args=array())
    {        
      return ModUtil::Func('Dizkus', 'topic', 'viewtopic',$args);
    }
    
        /**
     * newtopic
     *
     */
    public function newtopic()
    {
       return ModUtil::Func('Dizkus', 'topic', 'newtopic', $args); 
    }
    
     /**
     * emailtopic
     *
     * @return string
     */
    public function emailtopic()
    {
        return ModUtil::Func('Dizkus', 'topic', 'emailtopic', $args); 
    }
    
     /**
     * Split topic
     *
     * @return string
     */
    public function splittopic()
    {
       return ModUtil::Func('Dizkus', 'topic', 'splittopic', $args);
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
     * jointopics
     * Join a topic with another toipic                                                                                                  ?>
     *
     */
    public function jointopics($args=array())
    {
       return ModUtil::Func('Dizkus', 'Topic', 'jointopic', $args);
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
        return ModUtil::Func('Dizkus', 'topic', 'topicsubscriptions', $args); 
    }
      /**
     * Delete topic
     *
     * @return string
     */
    public function deletetopic() 
    {
        return ModUtil::Func('Dizkus', 'topic', 'deletetopic', $args);    
    }
  
    /**
     * Post 
     *
     */
    public function reply($args=array())
    {
         return ModUtil::Func('Dizkus', 'Post', 'reply', $args);
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
     * latest
     *
     * @return string
     */
    public function viewlatest($args=array())
    {
       return ModUtil::Func('Dizkus', 'Post', 'viewlatest', $args);  
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
        return ModUtil::Func('Dizkus', 'Post', 'movepost', $args);
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
        return ModUtil::Func('Dizkus', 'Post', 'report', $args);
    }
    
}