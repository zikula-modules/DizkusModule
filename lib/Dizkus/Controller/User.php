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

        $numposts = ModUtil::apiFunc('Dizkus', 'user', 'countstats', array('id' => '0', 'type' => 'all'));
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

        $managedForum = new Dizkus_Manager_Forum($forumId);
        // Permission check
        $this->throwForbiddenUnless(
            ModUtil::apiFunc($this->name, 'Permission', 'canRead', $managedForum->get())
        );

        $this->view->assign('forum', $managedForum->get())
            ->assign('topics', $managedForum->getTopics($start))
            ->assign('pager', $managedForum->getPager())
            ->assign('permissions', $managedForum->getPermissions())
            ->assign('isModerator', $managedForum->isModerator())
            ->assign('breadcrumbs', $managedForum->getBreadcrumbs())
            ->assign('hot_threshold', $this->getVar('hot_threshold'))
            ->assign('last_visit', $last_visit)
            ->assign('last_visit_unix', $last_visit_unix);

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
        $post_id = (int)$this->request->query->get('post', (isset($args['post'])) ? $args['post'] : null);
        $start = (int)$this->request->query->get('start', (isset($args['start'])) ? $args['start'] : 0);

        list($last_visit, $last_visit_unix) = ModUtil::apiFunc($this->name, 'user', 'setcookies');

        if (!empty($post_id) && is_numeric($post_id) && empty($topicId)) {
            $managedPost = new Dizkus_Manager_Post($post_id);
            $topic_id = $managedPost->getTopicId();
            if ($topic_id <> false) {
                // redirect instad of continue, better for SEO
                return System::redirect(ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $topic_id)));
            }
        }

        $managedTopic = new Dizkus_Manager_Topic($topicId);

        // Permission check
        //$this->throwForbiddenUnless(
        //    ModUtil::apiFunc($this->name, 'Permission', 'canRead', $topic)
        //);

        if (!$managedTopic->exists()) {
            return LogUtil::registerError(
                $this->__f(
                        "Error! The topic you selected (ID: %s) was not found. Please go back and try again.", array($topicId)
                ), null, ModUtil::url('Dizkus', 'user', 'main')
            );
        }
        list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => Dizkus_Entity_Rank::TYPE_POSTCOUNT));
        $this->view->assign('ranks', $ranks);
        $this->view->assign('start', $start);
        $this->view->assign('topic', $managedTopic->get()->toArray());
        $this->view->assign('posts', $managedTopic->getPosts($start));
        $this->view->assign('pager', $managedTopic->getPager());
        $this->view->assign('permissions', $managedTopic->getPermissions());
        $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
        $this->view->assign('isSubscribed', $managedTopic->isSubscribed());
        $this->view->assign('nextTopic', $managedTopic->getNext());
        $this->view->assign('previousTopic', $managedTopic->getPrevious());
        //$this->view->assign('post_count', count($topic['posts']));
        //$this->view->assign('last_visit', $last_visit);
        //$this->view->assign('last_visit_unix', $last_visit_unix);
        //$this->view->assign('favorites', ModUtil::apifunc($this->name, 'user', 'get_favorite_status'));

        $managedTopic->incrementViewsCount();

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
        // the form would need to be instanciated in the viewtopic method
//        $form = FormUtil::newForm($this->name, $this);
//        return $form->execute('user/topic/reply.tpl', new Dizkus_Form_Handler_User_QuickReply());
        // Permission check
        // todo check topic
        $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead'));
        $this->checkCsrfToken();

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
        // check hooked modules for validation
        if ($submit) {
            $hook = new Zikula_ValidationHook('dizkus.ui_hooks.post.validate_edit', new Zikula_Hook_ValidationProviders());
            $hookvalidators = $this->notifyHooks($hook)->getValidators();
            if ($hookvalidators->hasErrors()) {
                LogUtil::registerStatus($this->__('Error! Hooked content does not validate.'));
                $preview = true;
            }
        }

        if ($submit && !$preview) {

            $data = array(
                'topic_id' => $topic_id,
                'post_text' => $message,
                'post_attach_signature' => $attach_signature
            );

            $managedPost = new Dizkus_Manager_Post();
            $managedPost->create($data);
            $start = ModUtil::apiFunc('Dizkus', 'user', 'getTopicPage', array('topic_replies' => $managedPost->get()->getTopic()->getTopic_replies()));
            $params = array(
                'topic' => $topic_id,
                'start' => $start
            );
            $url = new Zikula_ModUrl('Dizkus', 'user', 'viewtopic', ZLanguage::getLanguageCode(), $params, 'pid' . $managedPost->getId());
            $this->notifyHooks(new Zikula_ProcessHook('dizkus.ui_hooks.post.process_edit', $managedPost->getId(), $url));
            
            // notify topic & forum subscribers
            $notified = ModUtil::apiFunc('Dizkus', 'notify', 'emailSubscribers', array('post' => $managedPost->get()));

            return $this->redirect($url->getUrl());
        } else {
            list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $managedTopic = new Dizkus_Manager_Topic($topic_id);
            $managedPoster = new Dizkus_Manager_ForumUser();
            $reply = array(
                'topic_id' => $topic_id,
                'post_id' => $post_id,
                'attach_signature' => $attach_signature,
                'subscribe_topic' => $subscribe_topic,
                'topic' => $managedTopic->toArray(),
                'poster_data' => $managedPoster->toArray(),
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
     * View the posters IP information
     * 
     * @return string
     */
    public function viewIpData()
    {
        $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canModerate'));
        $post_id = (int)$this->request->query->get('post', null);
        $this->view->assign('viewip', ModUtil::apiFunc('Dizkus', 'user', 'get_viewip_data', array('post_id' => $post_id)));
        return $this->view->fetch('user/viewip.tpl');
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
        return $this->changeViewSetting('all');
    }

    /**
     * Show only favorite forums in main view instead of all forums
     *
     */
    public function showFavorites()
    {
        return $this->changeViewSetting('favorites');
    }

    /**
     * Show only favorite forums in main view instead of all forums
     *
     */
    private function changeViewSetting($setting)
    {
        $url = ModUtil::url('Dizkus', 'user', 'main');
        if (!UserUtil::isLoggedIn()) {
            LogUtil::registerPermissionError();
            return System::redirect($url);
        }
        $uid = UserUtil::getVar('uid');
        $forumUser = $this->entityManager->find('Dizkus_Entity_ForumUser', $uid);
        if (!$forumUser) {
            $forumUser = new Dizkus_Entity_ForumUser();
            $coreUser = $this->entityManager->find('Users\Entity\UserEntity', $uid);
            $forumUser->setUser($coreUser);
        }
        $method = ($setting == 'favorites') ? 'showFavoritesOnly' : 'showAllForums';
        $forumUser->$method();
        $this->entityManager->persist($forumUser);
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
     * @return string
     */
    public function moderateForum()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/forum/moderate.tpl', new Dizkus_Form_Handler_User_ModerateForum());        
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

    /**
     * generate and display an RSS feed of recent topics
     * @return string
     */
    public function feed()
    {
        $forum_id = $this->request->query->get('forum_id', null);
        $cat_id = $this->request->query->get('cat_id', null);
        $count = (int)$this->request->query->get('count', 10);
        $feed = $this->request->query->get('feed', 'rss20');
        $user = $this->request->query->get('user', null);

        // get the module info
        $dzkinfo = ModUtil::getInfo(ModUtil::getIdFromName('Dizkus'));
        $dzkname = $dzkinfo['displayname'];
        
        $mainUrl = ModUtil::url($this->name, 'user', 'main');

        if (isset($forum_id) && !is_numeric($forum_id)) {
            LogUtil::registerError($this->__f('Error! An invalid forum ID %s was encountered.', $forum_id));
            return $this->redirect($mainUrl);
        }
        if (isset($cat_id) && !is_numeric($cat_id)) {
            LogUtil::registerError($this->__f('Error! An invalid category ID %s was encountered.', $cat_id));
            return $this->redirect($mainUrl);
        }

        /**
         * check if template for feed exists
         */
        $templatefile = 'feed/' . DataUtil::formatForOS($feed) . '.tpl';
        if (!$this->view->template_exists($templatefile)) {
            // silently stop working
            LogUtil::registerError($this->__f('Error! Could not find a template for an %s-type feed.', $feed));
            return $this->redirect($mainUrl);
        }

        /**
         * get user id
         */
        if (!empty($user)) {
            $uid = UserUtil::getIDFromName($user);
        }

        /**
         * set some defaults
         */
        // form the url
        $link = ModUtil::url('Dizkus', 'user', 'main', array(), null, null, true);

        $forumname = DataUtil::formatForDisplay($dzkname);

        // default where clause => no where clause
        $where = array();

        /**
         * check for forum_id
         */
        if (!empty($forum_id)) {
            $managedForum = new Dizkus_Manager_Forum($forum_id);
            if (!SecurityUtil::checkPermission('Dizkus::', ":$forum_id:", ACCESS_READ)) {
                LogUtil::registerPermissionError($mainUrl);
            }
            $where = array('t.forum', (int)DataUtil::formatForStore($forum_id), '=');
            $link = ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id), null, null, true);
            $forumname = $managedForum->get()->getForum_name();
        } elseif (!empty($cat_id)) {
            $managedForum = new Dizkus_Manager_Forum($cat_id);
            if (!SecurityUtil::checkPermission('Dizkus::', $cat_id . ':.*:', ACCESS_READ)) {
                LogUtil::registerPermissionError($mainUrl);
            }
            $where = array('t.parent', (int)DataUtil::formatForStore($cat_id), '=');
            $link = ModUtil::url('Dizkus', 'user', 'viewforum', array('viewcat' => $cat_id), null, null, true);
            $forumname = $managedForum->get()->getParent()->getForum_name();
        } elseif (isset($uid) && ($uid<>false)) {
            $where = array('p.poster', ' $uid', '=');
        } else {
            $allowedforums = ModUtil::apiFunc('Dizkus', 'forum', 'getForumIdsByPermission');
            if (count($allowedforums) > 0) {
                $where = array('f.forum', DataUtil::formatForStore($allowedforums), 'IN');
            }
        }

        $this->view->assign('forum_name', $forumname);
        $this->view->assign('forum_link', $link);
        $this->view->assign('sitename', System::getVar('sitename'));
        $this->view->assign('adminmail', System::getVar('adminmail'));
        $this->view->assign('current_date', date(DATE_RSS));
        $this->view->assign('current_language', ZLanguage::getLocale());

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t, f, p, fu')
                ->from('Dizkus_Entity_Topic', 't')
                ->join('t.forum', 'f')
                ->join('t.last_post', 'p')
                ->join('p.poster', 'fu');
        if (!empty($where)) {
            if ($where[2] == 'IN') {
                $qb->expr()->in($where[0], $where[1]);
            } else {
                $qb->where("$where[0] $where[2] :param")
                        ->setParameter('param', $where[1]);
            }
        }
        $qb->orderBy('t.topic_time', 'DESC')
                ->setMaxResults($count);
        $topics = $qb->getQuery()->getResult();

        $posts_per_page  = ModUtil::getVar('Dizkus', 'posts_per_page');
        $posts = array();
        $i = 0;
        foreach ($topics as $topic)
        {
            /* @var $topic Dizkus_Entity_Topic */
            $posts[$i]['topic_title'] = $topic->getTopic_title();
            $posts[$i]['cat_title'] = $topic->getForum()->getParent()->getForum_name();
            $posts[$i]['forum_name'] = $topic->getForum()->getForum_name();
            $posts[$i]['time'] = $topic->getTopic_time();
            $posts[$i]['unixtime'] = $topic->getTopic_time()->format('U');
            $start = (int)((ceil(($topic->getTopic_replies() + 1)  / $posts_per_page) - 1) * $posts_per_page);
            $posts[$i]['post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                         array('topic' => $topic->getTopic_id(),
                                               'start' => $start), 
                                         null, null, true);
            $posts[$i]['last_post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                              array('topic' => $topic->getTopic_id(),
                                                    'start' => $start), 
                                              null, "pid" . $topic->getLast_post()->getPost_id(), true);
            $posts[$i]['rsstime'] = $topic->getTopic_time()->format(DATE_RSS);
            $i++;
        }

        $this->view->assign('posts', $posts);
        $this->view->assign('dizkusinfo', $dzkinfo);

        header("Content-Type: text/xml");
        $this->view->display($templatefile);
    }
}