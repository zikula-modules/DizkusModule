<?php

use Doctrine\ORM\Tools\Pagination\Paginator;

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

    /**
     * Show all categories and forums a user may see
     *
     * $args['viewcat'] int only expand the category, all others shall be hidden / collapsed
     *
     * @param array $args Arguments array.
     *
     * @return string
     */
    public function main($args = array())
    {
        // Permission check
        $this->throwForbiddenUnless(
                ModUtil::apiFunc($this->name, 'Permission', 'canRead')
        );

        // Check if all forums or only one category should be shown
        $viewcat = (int)$this->request->query->get('viewcat', (isset($args['viewcat'])) ? $args['viewcat'] : 0);
        $this->view->assign('viewcat', $viewcat);

        list($lastVisit, $lastVisitUnix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
        $this->view->assign('last_visit', $lastVisit);
        $this->view->assign('last_visit_unix', $lastVisitUnix);

        // get tree level
        $level = $this->entityManager->getRepository('Dizkus_Entity_Forum')->getOneLevel($viewcat);
        $this->view->assign('tree', $level);

        $numposts = ModUtil::apiFunc('Dizkus', 'user', 'boardstats', array('id' => '0', 'type' => 'all'));
        $this->view->assign('numposts', $numposts);

        return $this->view->fetch('user/main.tpl');
    }

    /**
     * View forum by id
     *
     * opens a forum and shows the last postings
     * $args['forum'] int the forum id
     * $args['start'] int the posting to start with if on page 1+
     *
     * @param array $args Arguments array.
     *
     * @return string
     */
    public function viewforum($args = array())
    {
        // get the input
        $forumId = (int)$this->request->query->get('forum', (isset($args['forum'])) ? $args['forum'] : null);
        $start = (int)$this->request->query->get('start', (isset($args['start'])) ? $args['start'] : 1);

        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');

        $forum = new Dizkus_Manager_Forum($forumId);
        $this->view->assign('forum', $forum->get());
        $this->view->assign('topics', $forum->getTopics($start));
        $this->view->assign('pager', $forum->getPager());
        $this->view->assign('permissions', $forum->getPermissions());
        $this->view->assign('breadcrumbs', $forum->getBreadcrumbs());

        // Permission check
        $this->throwForbiddenUnless(
                ModUtil::apiFunc($this->name, 'Permission', 'canRead', $forum)
        );

        $this->view->assign('hot_threshold', $this->getVar('hot_threshold'));
        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);

        return $this->view->fetch('user/forum/view.tpl');
    }

    /**
     * viewtopic
     *
     * @param array $args Arguments array.
     *
     * @return string
     */
    public function viewtopic($args = array())
    {
        // get the input
        $topicId = (int)$this->request->query->get('topic', (isset($args['topic'])) ? $args['topic'] : null);
        // begin patch #3494 part 1, credits to teb
        $post_id = (int)$this->request->query->get('post', (isset($args['post'])) ? $args['post'] : null);
        // end patch #3494 part 1
        $start = (int)$this->request->query->get('start', (isset($args['start'])) ? $args['start'] : 0);
        $view = strtolower($this->request->query->get('view', (isset($args['view'])) ? $args['view'] : ''));

        /* list($last_visit, $last_visit_unix) = ModUtil::apiFunc($this->name, 'user', 'setcookies');

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
          // end patch #3494 part 2 */

        $topic = new Dizkus_Manager_Topic($topicId);

        // Permission check
        //$this->throwForbiddenUnless(
        //    ModUtil::apiFunc($this->name, 'Permission', 'canRead', $topic)
        //);

        if (!$topic->exists()) {
            return LogUtil::registerError(
                $this->__f(
                        "Error! The topic you selected (ID: %s) was not found. Please go back and try again.", array($topicId)
                ), null, ModUtil::url('Dizkus', 'user', 'main')
            );
        }

        $this->view->assign('start', $start);
        $this->view->assign('topic', $topic->get()->toArray());
        $this->view->assign('posts', $topic->getPosts($start));
        $this->view->assign('pager', $topic->getPager());
        $this->view->assign('permissions', $topic->getPermissions());
        $this->view->assign('breadcrumbs', $topic->getBreadcrumbs());
        $this->view->assign('isSubscribed', $topic->isSubscribed());
        //$this->view->assign('post_count', count($topic['posts']));
        //$this->view->assign('last_visit', $last_visit);
        //$this->view->assign('last_visit_unix', $last_visit_unix);
        //$this->view->assign('favorites', ModUtil::apifunc($this->name, 'user', 'get_favorite_status'));

        $topic->incrementViewsCount();

        return $this->view->fetch('user/topic/view.tpl');
    }

    /**
     * reply
     *
     * @param array $args Arguments array.
     *
     * @return string
     */
    public function reply()
    {
        // I cannot see how using the Form lib will work here. This method is post submission of the form...
//        $form = FormUtil::newForm($this->name, $this);
//        return $form->execute('user/topic/reply.tpl', new Dizkus_Form_Handler_User_QuickReply());
        // Permission check
        // todo check topic
        $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead'));

        // get the input
        $topic_id = (int)$this->request->request->get('topic', null);
        $post_id = (int)$this->request->request->get('post', null);
        $message = $this->request->request->get('message', '');
        $attach_signature = (int)$this->request->request->get('attach_signature', 0);
        $subscribe_topic = (int)$this->request->request->get('subscribe_topic', 0);
        $preview = $this->request->request->get('preview', '');
        $submit = $this->request->request->get('submit', '');
        $cancel = $this->request->request->get('cancel', '');

        /**
         * if cancel is submitted move to topic-view
         */
        if (!empty($cancel)) {
            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
        }

        $preview = (empty($preview)) ? false : true;
        $submit = (empty($submit)) ? false : true;

        $message = ModUtil::apiFunc('Dizkus', 'user', 'dzkstriptags', $message);
        // check for maximum message size
        if ((strlen($message) + strlen('[addsig]')) > 65535) {
            LogUtil::registerStatus($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
            // switch to preview mode
            $preview = true;
        }
        if (empty($message)) {
            LogUtil::registerStatus($this->__('Error! The message is empty. Please add some text.'));
            // switch to preview mode
            $preview = true;
        }

        if ($submit && !$preview) {

            $data = array(
                'topic_id' => $topic_id,
                'post_text' => $message,
                'post_attach_signature' => $attach_signature
            );

            $post = new Dizkus_Manager_Post();
            $post->create($data);
            $params = array(
                'topic' => $topic_id,
                'start' => $start
            );
            $url = ModUtil::url('Dizkus', 'user', 'viewtopic', $params) . '#pid' . $post->getId();

            return $this->redirect($url);
        } else {
            list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $topic = new Dizkus_Manager_Topic($topic_id);
            $poster = new Dizkus_Manager_PosterData();
            $reply = array(
                'topic_id' => $topic_id,
                'post_id' => $post_id,
                'attach_signature' => $attach_signature,
                'subscribe_topic' => $subscribe_topic,
                'topic' => $topic->toArray(),
                'poster_data' => $poster->toArray(),
            );
            if ($preview) {
                $reply['message'] = ModUtil::apiFunc('Dizkus', 'user', 'dzkVarPrepHTMLDisplay', $message);
                $reply['message_display'] = nl2br($reply['message']);
            }

            $this->view->assign('reply', $reply);
            $this->view->assign('preview', $preview);
            $this->view->assign('last_visit', $last_visit);
            $this->view->assign('last_visit_unix', $last_visit_unix);

            return $this->view->fetch('user/topic/reply.tpl');
        }
    }

    /**
     * Create new topic
     *
     * User interface to create a new topic
     *
     * @return string
     */
    public function newtopic()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/topic/new.tpl', new Dizkus_Form_Handler_User_NewTopic());
    }

    /**
     * Edit post
     *
     * User interface to edit a new post
     *
     * @return string
     */
    public function editpost()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/post/edit.tpl', new Dizkus_Form_Handler_User_EditPost());
    }

    /**
     * Delete topic
     *
     * User interface to delete a post.
     *
     * @return string
     */
    public function deletetopic()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/topic/delete.tpl', new Dizkus_Form_Handler_User_DeleteTopic());
    }

    /**
     * Move topic
     *
     * User interface to move a topic to another forum.
     *
     * @return string
     */
    public function movetopic()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/topic/move.tpl', new Dizkus_Form_Handler_User_MoveTopic());
    }

    /**
     * topicadmin
     *
     */
    public function topicadmin($args = array())
    {
        // Permission check
        $this->throwForbiddenUnless(
                ModUtil::apiFunc($this->name, 'Permission', 'canRead')
        );

        // get the input
        if ($this->request->isPost()) {
            $topic_id = (int)$this->request->request->get('topic', (isset($args['topic'])) ? $args['topic'] : null);
            $post_id = (int)$this->request->request->get('post', (isset($args['post'])) ? $args['post'] : null);
            $forum_id = (int)$this->request->request->get('forum', (isset($args['forum'])) ? $args['forum'] : null);
            $mode = $this->request->request->get('mode', (isset($args['mode'])) ? $args['mode'] : '');
            $submit = $this->request->request->get('submit', (isset($args['submit'])) ? $args['submit'] : '');
            $shadow = $this->request->request->get('createshadowtopic', (isset($args['createshadowtopic'])) ? $args['createshadowtopic'] : '');
        } else {
            $topic_id = (int)$this->request->query->get('topic', (isset($args['topic'])) ? $args['topic'] : null);
            $post_id = (int)$this->request->query->get('post', (isset($args['post'])) ? $args['post'] : null);
            $forum_id = (int)$this->request->query->get('forum', (isset($args['forum'])) ? $args['forum'] : null);
            $mode = $this->request->query->get('mode', (isset($args['mode'])) ? $args['mode'] : '');
            $submit = $this->request->query->get('submit', (isset($args['submit'])) ? $args['submit'] : '');
            $shadow = $this->request->query->get('createshadowtopic', (isset($args['createshadowtopic'])) ? $args['createshadowtopic'] : '');
        }
        $shadow = (empty($shadow)) ? false : true;
        if (empty($topic_id) && !empty($post_id)) {
            $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_postid', array('post_id' => $post_id));
        }

        $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $topic_id,
                    'count' => false));

        /* This does not work. Commenting out until we decide to fix or remove totally.
          if ($topic['access_moderate'] <> true) {
          return LogUtil::registerPermissionError();
          }
         */

        if (empty($submit)) {
            switch ($mode) {
                case 'lock':
                case 'unlock':
                    $templatename = 'user/topic/lock.tpl';
                    break;

                case 'sticky':
                case 'unsticky':
                    $templatename = 'user/topic/sticky.tpl';
                    break;

                case 'viewip':
                    $this->view->assign('viewip', ModUtil::apiFunc('Dizkus', 'user', 'get_viewip_data', array('post_id' => $post_id)));
                    $templatename = 'user/viewip.tpl';
                    break;

                default:
                    return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
            }

            $this->view->add_core_data();
            $this->view->setCaching(false);
            $this->view->assign('mode', $mode);
            $this->view->assign('topic_id', $topic_id);

            return $this->view->fetch($templatename);
        } else { // submit is set
            /* if (!SecurityUtil::confirmAuthKey()) {
              return LogUtil::registerAuthidError();
              } */

            switch ($mode) {
                case 'lock':
                case 'unlock':
                    // TODO: get_forumid_and_categoryid_from_topicid no longer defined
                    $topic = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $topic_id));
                    if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $topic)) {
                        return LogUtil::registerPermissionError();
                    }
                    ModUtil::apiFunc('Dizkus', 'user', 'lockunlocktopic', array('topic_id' => $topic_id,
                        'mode' => $mode));
                    break;

                case 'sticky':
                case 'unsticky':
                    // TODO: get_forumid_and_categoryid_from_topicid no longer defined
                    $topic = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $topic_id));
                    if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $topic)) {
                        return LogUtil::registerPermissionError();
                    }
                    ModUtil::apiFunc('Dizkus', 'user', 'stickyunstickytopic', array('topic_id' => $topic_id,
                        'mode' => $mode));
                    break;
                default:
            }

            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
        }
    }

    /**
     * prefs
     *
     * Interface for a user to manage general user preferences.
     *
     * @return string
     */
    public function prefs()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/prefs/prefs.tpl', new Dizkus_Form_Handler_User_Prefs());
    }

    /**
     * Interface for a user to manage topic subscriptions
     *
     * @return string
     */
    public function manageForumSubscriptions()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/prefs/manageForumSubscriptions.tpl', new Dizkus_Form_Handler_User_ForumSubscriptions());
    }

    /**
     * Interface for a user to manage topic subscriptions
     *
     * @return string
     */
    public function manageTopicSubscriptions()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/prefs/manageTopicSubscriptions.tpl', new Dizkus_Form_Handler_User_TopicSubscriptions());
    }

    /**
     * Show all forums in main view instead of only favorite forums
     *
     */
    public function showAllForums()
    {
        $url = ModUtil::url('Dizkus', 'user', 'main');
        if (!UserUtil::isLoggedIn()) {
            LogUtil::registerPermissionError();
            return System::redirect($url);
        }
        $uid = UserUtil::getVar('uid');
        $posterData = $this->entityManager->find('Dizkus_Entity_Poster', $uid);
        if (!$posterData) {
            $posterData = new Dizkus_Entity_Poster();
        }
        $posterData->setUser_favorites(false);
        $this->entityManager->flush();

        return System::redirect($url);
    }

    /**
     * Show only favorite forums in main view instead of all forums
     *
     */
    public function showFavorites()
    {
        $url = ModUtil::url('Dizkus', 'user', 'main');
        if (!UserUtil::isLoggedIn()) {
            LogUtil::registerPermissionError();
            return System::redirect($url);
        }
        $uid = UserUtil::getVar('uid');
        $posterData = $this->entityManager->find('Dizkus_Entity_Poster', $uid);
        if (!$posterData) {
            $posterData = new Dizkus_Entity_Poster();
            $posterData->setUser_id($uid);
        }
        $posterData->setUser_favorites(true);
        $this->entityManager->persist($posterData);
        $this->entityManager->flush();

        return System::redirect($url);
    }

    /**
     * Add/remove a forum from the favorites
     */
    public function modifyForum()
    {
        $params = array(
            'action' => $this->request->query->get('action'),
            'forum_id' => (int)$this->request->query->get('forum')
        );
        ModUtil::apiFunc($this->name, 'Forum', 'modify', $params);

        return System::redirect(ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $params['forum_id'])));
    }

    /**
     * Add/remove the sticky status of a topic
     */
    public function changeTopicStatus()
    {
        $params = array();
        $params['action'] = $this->request->query->get('action');
        $params['topic_id'] = (int)$this->request->query->get('topic');
        ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);

        return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $params['topic_id'])));
    }

    /**
     * Interface for a user to manage signature
     *
     * @return string
     */
    public function signaturemanagement()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/prefs/signaturemanagement.tpl', new Dizkus_Form_Handler_User_SignatureManagement());
    }

    /**
     * ignorelist management
     * 
     */
    public function ignorelistmanagement()
    {
        // Permission check
        $this->throwForbiddenUnless(
                ModUtil::apiFunc($this->name, 'Permission', 'canRead')
        );

        if (!UserUtil::isLoggedIn()) {
            return ModUtil::func('Users', 'user', 'loginscreen', array('redirecttype' => 1));
        }
        // Security check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT)) {
            return LogUtil::registerPermissionError();
        }

        // check for Contactlist module and admin settings
        $ignorelist_handling = ModUtil::getVar('Dizkus', 'ignorelist_handling');
        if (!ModUtil::available('ContactList') || ($ignorelist_handling == 'none')) {
            return LogUtil::registerError($this->__("No 'ignore list' configuration is currently possible."), null, ModUtil::url('Dizkus', 'user', 'prefs'));
        }

        // Create output and assign data
        $render = FormUtil::newForm($this->name, $this);

        // Return the output
        return $render->execute('user/prefs/ignorelistmanagement.tpl', new Dizkus_Form_Handler_User_IgnoreListManagement());
    }

    /**
     * User interface to email a topic to a arbitrary email-address
     *
     * @return string
     */
    public function emailtopic()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/topic/email.tpl', new Dizkus_Form_Handler_User_EmailTopic());
    }

    /**
     * View latest topics
     *
     * @param array $args Arguments array.
     *
     * @return string
     */
    public function viewlatest($args = array())
    {
        // Permission check
        $this->throwForbiddenUnless(
                ModUtil::apiFunc($this->name, 'Permission', 'canRead')
        );
        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
            return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
        }

        // get the input
        $params['selorder'] = $this->request->query->get('selorder', (isset($args['selorder'])) ? $args['selorder'] : 1);
        $params['nohours'] = (int)$this->request->query->get('nohours', (isset($args['nohours'])) ? $args['nohours'] : null);
        $params['unanswered'] = (int)$this->request->query->get('unanswered', (isset($args['unanswered'])) ? $args['unanswered'] : 0);
        $params['amount'] = (int)$this->request->query->get('amount', (isset($args['amount'])) ? $args['amount'] : null);
        $this->view->assign($params);

        list($posts, $text, $pager) = ModUtil::apiFunc('Dizkus', 'post', 'getLatest', $params);

        $this->view->assign('posts', $posts);
        $this->view->assign('text', $text);
        $this->view->assign('pager', $pager);

        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');

        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);

        return $this->view->fetch('user/post/latest.tpl');
    }

    public function myposts()
    {
        $params = array('action' => $this->request->query->get('action', 'posts'));

        return $this->search($params);
    }

    /**
     * View latest topics
     *
     * @param array $args Arguments array.
     *
     * @return string
     */
    public function search($args)
    {
        // Permission check
        $this->throwForbiddenUnless(
                ModUtil::apiFunc($this->name, 'Permission', 'canRead')
        );

        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
            return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
        }

        $params = array();
        $params['action'] = $this->request->query->get('action', (isset($args['action'])) ? $args['action'] : 'posts');
        $params['uid'] = $this->request->query->get('user', (isset($args['user'])) ? $args['user'] : null);


        list($posts, $text, $pager) = ModUtil::apiFunc('Dizkus', 'post', 'search', $params);

        $this->view->assign('posts', $posts);
        $this->view->assign('text', $text);
        $this->view->assign('pager', $pager);

        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');

        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);

        return $this->view->fetch('user/post/search.tpl');
    }

    /**
     * Split topic
     *
     * @return string
     */
    public function splittopic()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/topic/split.tpl', new Dizkus_Form_Handler_User_SplitTopic());
    }

    /**
     * print
     *
     * prepare print view of the selected posting or topic
     * 
     * Only used if Printer theme not available
     *
     * @param array $args Argument array.
     *
     * @return string
     */
    public function printtopic($args = array())
    {
        // Permission check
        $this->throwForbiddenUnless(
                ModUtil::apiFunc($this->name, 'Permission', 'canRead')
        );

        // get the input
        $post_id = (int)$this->request->query->get('post', (isset($args['post'])) ? $args['post'] : null);
        $topic_id = (int)$this->request->query->get('topic', (isset($args['topic'])) ? $args['topic'] : null);

        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
            if ($post_id <> 0) {
                $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_postid', array('post_id' => $post_id));
            }
            if (($topic_id <> 0) && ($topic_id <> false)) {
                return $this->viewtopic(array('topic' => $topic_id, 'start' => 0));
            } else {
                return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
            }
        } else {
            $this->view->add_core_data();
            $this->view->setCaching(false);
            if ($post_id <> 0) {
                $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost', array('post_id' => $post_id));
                $this->view->assign('post', $post);
                $output = $this->view->fetch('user/post/print.tpl');
            } elseif ($topic_id <> 0) {
                $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $topic_id,
                            'complete' => true,
                            'count' => false));

                $this->view->assign('topic', $topic);

                $output = $this->view->fetch('user/topic/print.tpl');
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
     * User interface to move a single post to another thread
     *
     * @return string
     */
    public function movepost()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/post/move.tpl', new Dizkus_Form_Handler_User_MovePost());
    }

    /**
     * Moderate forum
     *
     * User interface for moderation of multiple topics.
     *
     * @param array $args The Arguments array.
     *
     * @return string
     */
    public function moderateForum($args = array())
    {
        // Permission check
        $this->throwForbiddenUnless(
                ModUtil::apiFunc($this->name, 'Permission', 'canRead')
        );

        // get the input
        $forum_id = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
        $start = (int)FormUtil::getPassedValue('start', (isset($args['start'])) ? $args['start'] : 0, 'GETPOST');
        $mode = FormUtil::getPassedValue('mode', (isset($args['mode'])) ? $args['mode'] : '', 'GETPOST');
        $submit = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
        $topic_ids = FormUtil::getPassedValue('topic_id', (isset($args['topic_id'])) ? $args['topic_id'] : array(), 'GETPOST');
        $shadow = FormUtil::getPassedValue('createshadowtopic', (isset($args['createshadowtopic'])) ? $args['createshadowtopic'] : '', 'GETPOST');
        $moveto = (int)FormUtil::getPassedValue('moveto', (isset($args['moveto'])) ? $args['moveto'] : null, 'GETPOST');
        $jointo = (int)FormUtil::getPassedValue('jointo', (isset($args['jointo'])) ? $args['jointo'] : null, 'GETPOST');

        $shadow = (empty($shadow)) ? false : true;

        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');

        // Get the Forum for Display and Permission-Check
        $forum = ModUtil::apiFunc('Dizkus', 'user', 'readforum', array('forum_id' => $forum_id,
                    'start' => $start,
                    'last_visit' => $last_visit,
                    'last_visit_unix' => $last_visit_unix));

        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $forum)) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }


        // Submit isn't set'
        if (empty($submit)) {
            $this->view->assign('forum_id', $forum_id);
            $this->view->assign('mode', $mode);
            $this->view->assign('topic_ids', $topic_ids);
            $this->view->assign('last_visit', $last_visit);
            $this->view->assign('last_visit_unix', $last_visit_unix);
            $this->view->assign('forum', $forum);
            // For Movetopic
            $this->view->assign('forums', ModUtil::apiFunc('Dizkus', 'user', 'readuserforums'));

            return $this->view->fetch('user/forum/moderate.tpl');
        } else {
            // submit is set
            //if (!SecurityUtil::confirmAuthKey()) {
            //    return LogUtil::registerAuthidError();
            //}*/
            if (count($topic_ids) <> 0) {
                switch ($mode) {
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
                            ModUtil::apiFunc('Dizkus', 'topic', 'move', array('topic_id' => $topic_id,
                                'forum_id' => $moveto,
                                'shadow' => $shadow));
                        }
                        break;

                    case 'lock':
                    case 'unlock':
                        foreach ($topic_ids as $topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'lockunlocktopic', array('topic_id' => $topic_id, 'mode' => $mode));
                        }
                        break;

                    case 'sticky':
                    case 'unsticky':
                        foreach ($topic_ids as $topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'stickyunstickytopic', array('topic_id' => $topic_id, 'mode' => $mode));
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
                            ModUtil::apiFunc('Dizkus', 'topic', 'join', array('from_topic_id' => $from_topic_id,
                                'to_topic_id' => $jointo));
                        }
                        break;

                    default:
                }

                // Refresh Forum Info
                $forum = ModUtil::apiFunc('Dizkus', 'user', 'readforum', array('forum_id' => $forum_id,
                            'start' => $start,
                            'last_visit' => $last_visit,
                            'last_visit_unix' => $last_visit_unix));
            }
        }

        return System::redirect(ModUtil::url('Dizkus', 'user', 'moderateforum', array('forum' => $forum_id)));
    }

    /**
     * Report
     *
     * User interface to notify a moderator about a (bad) posting.
     *
     * @return string
     */
    public function report()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/notifymod.tpl', new Dizkus_Form_Handler_User_Report());
    }

}