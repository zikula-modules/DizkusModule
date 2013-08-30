<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
namespace Dizkus\Controller {
    use Doctrine\ORM\Tools\Pagination\Paginator;
    use SecurityUtil;
    use ModUtil;
    use UserUtil;
    use LogUtil;
    use Dizkus_Manager_ForumUser;
    use Dizkus_Manager_Forum;
    use Dizkus_Manager_Post;
    use System;
    use Dizkus_Manager_Topic;
    use Dizkus_Entity_Rank;
    use Zikula_Hook_ValidationProviders;
    use Zikula_ValidationHook;
    use ZLanguage;
    use Zikula_ModUrl;
    use Zikula_ProcessHook;
    use FormUtil;
    use Dizkus_Form_Handler_User_NewTopic;
    use Dizkus_Form_Handler_User_EditPost;
    use Dizkus_Form_Handler_User_DeleteTopic;
    use Dizkus_Form_Handler_User_MoveTopic;
    use Dizkus_Form_Handler_User_Prefs;
    use Dizkus_Form_Handler_User_ForumSubscriptions;
    use Dizkus_Form_Handler_User_TopicSubscriptions;
    use Dizkus_Entity_ForumUser;
    use Dizkus_Form_Handler_User_SignatureManagement;
    use Dizkus_Form_Handler_User_EmailTopic;
    use Dizkus_Form_Handler_User_SplitTopic;
    use Dizkus_Form_Handler_User_MovePost;
    use Dizkus_Form_Handler_User_ModerateForum;
    use Dizkus_Form_Handler_User_Report;
    use DataUtil;
    class UserController extends \Zikula_AbstractController
    {
        /**
         * Show all forums a user may see
         *
         * @return string
         */
        public function indexAction()
        {
            if ($this->getVar('forum_enabled') == 'no' && !SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return $this->view->fetch('dizkus_disabled.tpl');
            }
            $indexTo = $this->getVar('indexTo');
            if (!empty($indexTo)) {
                $this->redirect(ModUtil::url($this->name, 'user', 'viewforum', array('forum' => (int) $indexTo)));
            }
            // Permission check
            $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead'));
            $lastVisitUnix = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $this->view->assign('last_visit_unix', $lastVisitUnix);
            // get the forms to display
            $showOnlyFavorites = ModUtil::apiFunc('Dizkus', 'Favorites', 'getStatus');
            $siteFavoritesAllowed = ModUtil::getVar('Dizkus', 'favorites_enabled') == 'yes';
            $uid = UserUtil::getVar('uid');
            $qb = $this->entityManager->getRepository('Dizkus_Entity_Forum')->childrenQueryBuilder();
            if (UserUtil::isLoggedIn() && $siteFavoritesAllowed && $showOnlyFavorites) {
                // display only favorite forums
                $qb->join('node.favorites', 'fa');
                $qb->andWhere('fa.forumUser = :uid');
                $qb->setParameter('uid', $uid);
            } else {
                // display an index of the level 1 forums
                $qb->andWhere('node.lvl = 1');
            }
            $forums = $qb->getQuery()->getResult();
            // filter the forum array by permissions
            $forums = ModUtil::apiFunc($this->name, 'Permission', 'filterForumArrayByPermission', $forums);
            // check to make sure there are forums to display
            if (count($forums) < 1) {
                if ($showOnlyFavorites) {
                    LogUtil::registerError($this->__('You have not selected any favorite forums. Please select some and try again.'));
                    $managedForumUser = new Dizkus_Manager_ForumUser($uid);
                    $managedForumUser->displayFavoriteForumsOnly(false);
                    $this->redirect(ModUtil::url($this->name, 'user', 'index'));
                } else {
                    LogUtil::registerError($this->__('This site has not set up any forums or they are all private. Contact the administrator.'));
                }
            }
            $this->view->assign('forums', $forums);
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
        public function viewforumAction($args = array())
        {
            if ($this->getVar('forum_enabled') == 'no' && !SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return $this->view->fetch('dizkus_disabled.tpl');
            }
            // get the input
            $forumId = (int) $this->request->query->get('forum', isset($args['forum']) ? $args['forum'] : null);
            $this->throwNotFoundUnless($forumId > 0, $this->__('That forum doesn\'t exist!'));
            $start = (int) $this->request->query->get('start', isset($args['start']) ? $args['start'] : 1);
            $lastVisitUnix = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $managedForum = new Dizkus_Manager_Forum($forumId);
            // Permission check
            $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead', $managedForum->get()));
            // filter the forum children by permissions
            $forum = ModUtil::apiFunc($this->name, 'Permission', 'filterForumChildrenByPermission', $managedForum->get());
            $this->view->assign('forum', $forum)->assign('topics', $managedForum->getTopics($start))->assign('pager', $managedForum->getPager())->assign('permissions', $managedForum->getPermissions())->assign('isModerator', $managedForum->isModerator())->assign('breadcrumbs', $managedForum->getBreadcrumbs())->assign('hot_threshold', $this->getVar('hot_threshold'))->assign('last_visit_unix', $lastVisitUnix);

            return $this->view->fetch('user/forum/view.tpl');
        }

        /**
         * viewtopic
         *
         * @param array $args Arguments array.
         *
         * @return string
         */
        public function viewtopicAction($args = array())
        {
            if ($this->getVar('forum_enabled') == 'no' && !SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return $this->view->fetch('dizkus_disabled.tpl');
            }
            // get the input
            $topicId = (int) $this->request->query->get('topic', isset($args['topic']) ? $args['topic'] : null);
            $post_id = (int) $this->request->query->get('post', isset($args['post']) ? $args['post'] : null);
            $start = (int) $this->request->query->get('start', isset($args['start']) ? $args['start'] : 1);
            $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
            if (!empty($post_id) && is_numeric($post_id) && empty($topicId)) {
                $managedPost = new Dizkus_Manager_Post($post_id);
                $topic_id = $managedPost->getTopicId();
                if ($topic_id != false) {
                    // redirect instad of continue, better for SEO
                    return System::redirect(ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $topic_id)));
                }
            }
            $managedTopic = new Dizkus_Manager_Topic($topicId);
            // Permission check
            $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead', $managedTopic->get()->getForum()));
            if (!$managedTopic->exists()) {
                return LogUtil::registerError($this->__f('Error! The topic you selected (ID: %s) was not found. Please go back and try again.', array($topicId)), null, ModUtil::url('Dizkus', 'user', 'index'));
            }
            list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => Dizkus_Entity_Rank::TYPE_POSTCOUNT));
            $this->view->assign('ranks', $ranks);
            $this->view->assign('start', $start);
            $this->view->assign('topic', $managedTopic->get());
            $this->view->assign('posts', $managedTopic->getPosts(--$start));
            $this->view->assign('pager', $managedTopic->getPager());
            $this->view->assign('permissions', $managedTopic->getPermissions());
            $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
            $this->view->assign('isSubscribed', $managedTopic->isSubscribed());
            $this->view->assign('nextTopic', $managedTopic->getNext());
            $this->view->assign('previousTopic', $managedTopic->getPrevious());
            $this->view->assign('last_visit_unix', $lastVisitUnix);
            $this->view->assign('preview', false);
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
        public function replyAction()
        {
            // Comment Permission check
            $forum_id = (int) $this->request->request->get('forum', null);
            $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canWrite', array('forum_id' => $forum_id)));
            $this->checkCsrfToken();
            // get the input
            $topic_id = (int) $this->request->request->get('topic', null);
            $post_id = (int) $this->request->request->get('post', null);
            $returnurl = $this->request->request->get('returnurl', null);
            $message = $this->request->request->get('message', '');
            $attach_signature = (int) $this->request->request->get('attach_signature', 0);
            $subscribe_topic = (int) $this->request->request->get('subscribe_topic', 0);
            // convert form submit buttons to boolean
            $isPreview = $this->request->request->get('preview', null);
            $isPreview = isset($isPreview) ? true : false;
            $submit = $this->request->request->get('submit', null);
            $submit = isset($submit) ? true : false;
            $cancel = $this->request->request->get('cancel', null);
            $cancel = isset($cancel) ? true : false;
            /**
             * if cancel is submitted move to topic-view
             */
            if ($cancel) {
                return $this->redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id)));
            }
            $message = ModUtil::apiFunc('Dizkus', 'user', 'dzkstriptags', $message);
            // check for maximum message size
            if (strlen($message) + strlen('[addsig]') > 65535) {
                LogUtil::registerStatus($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
                // switch to preview mode
                $isPreview = true;
            }
            if (empty($message)) {
                LogUtil::registerStatus($this->__('Error! The message is empty. Please add some text.'));
                // switch to preview mode
                $isPreview = true;
            }
            // check hooked modules for validation
            if ($submit) {
                $hook = new Zikula_ValidationHook('dizkus.ui_hooks.post.validate_edit', new Zikula_Hook_ValidationProviders());
                $hookvalidators = $this->notifyHooks($hook)->getValidators();
                if ($hookvalidators->hasErrors()) {
                    LogUtil::registerStatus($this->__('Error! Hooked content does not validate.'));
                    $isPreview = true;
                }
            }
            if ($submit && !$isPreview) {
                $data = array('topic_id' => $topic_id, 'post_text' => $message, 'attachSignature' => $attach_signature);
                $managedPost = new Dizkus_Manager_Post();
                $managedPost->create($data);
                // handle subscription
                if ($subscribe_topic) {
                    ModUtil::apiFunc($this->name, 'topic', 'subscribe', array('topic' => $topic_id));
                } else {
                    ModUtil::apiFunc($this->name, 'topic', 'unsubscribe', array('topic' => $topic_id));
                }
                $start = ModUtil::apiFunc('Dizkus', 'user', 'getTopicPage', array('replyCount' => $managedPost->get()->getTopic()->getReplyCount()));
                $params = array('topic' => $topic_id, 'start' => $start);
                $url = new Zikula_ModUrl('Dizkus', 'user', 'viewtopic', ZLanguage::getLanguageCode(), $params, 'pid' . $managedPost->getId());
                $this->notifyHooks(new Zikula_ProcessHook('dizkus.ui_hooks.post.process_edit', $managedPost->getId(), $url));
                // notify topic & forum subscribers
                $notified = ModUtil::apiFunc('Dizkus', 'notify', 'emailSubscribers', array('post' => $managedPost->get()));
                // if viewed in hooked state, redirect back to hook subscriber
                if (isset($returnurl)) {
                    $urlParams = unserialize(htmlspecialchars_decode($returnurl));
                    $mod = $urlParams['module'];
                    unset($urlParams['module']);
                    $type = $urlParams['type'];
                    unset($urlParams['type']);
                    $func = $urlParams['func'];
                    unset($urlParams['func']);
                    $params = array_merge($params, $urlParams);
                    $url = new Zikula_ModUrl($mod, $type, $func, ZLanguage::getLanguageCode(), $params, 'pid' . $managedPost->getId());
                }

                return $this->redirect($url->getUrl());
            } else {
                $lastVisitUnix = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
                $managedTopic = new Dizkus_Manager_Topic($topic_id);
                $managedPoster = new Dizkus_Manager_ForumUser();
                $reply = array('topic_id' => $topic_id, 'post_id' => $post_id, 'attach_signature' => $attach_signature, 'subscribe_topic' => $subscribe_topic, 'topic' => $managedTopic->toArray(), 'message' => $message);
                $post = array('post_id' => 0, 'topic_id' => $topic_id, 'poster' => $managedPoster->toArray(), 'post_time' => time(), 'attachSignature' => $attach_signature, 'post_text' => $message, 'userAllowedToEdit' => false);
                // Do not show edit link
                $permissions = array();
                list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => Dizkus_Entity_Rank::TYPE_POSTCOUNT));
                $this->view->assign('ranks', $ranks);
                $this->view->assign('post', $post);
                $this->view->assign('reply', $reply);
                $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
                $this->view->assign('preview', $isPreview);
                $this->view->assign('last_visit_unix', $lastVisitUnix);
                $this->view->assign('permissions', $permissions);

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
        public function newtopicAction()
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
        public function editpostAction()
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
        public function deletetopicAction()
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
        public function movetopicAction()
        {
            $form = FormUtil::newForm($this->name, $this);

            return $form->execute('user/topic/move.tpl', new Dizkus_Form_Handler_User_MoveTopic());
        }

        /**
         * View the posters IP information
         *
         * @return string
         */
        public function viewIpDataAction()
        {
            $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canModerate'));
            $post_id = (int) $this->request->query->filter('post', 0, FILTER_VALIDATE_INT);
            if ($post_id == 0) {
                return LogUtil::registerArgsError();
            }
            $this->view->assign('viewip', ModUtil::apiFunc('Dizkus', 'user', 'get_viewip_data', array('post_id' => $post_id)))->assign('post_id', $post_id);

            return $this->view->fetch('user/viewip.tpl');
        }

        /**
         * prefs
         *
         * Interface for a user to manage general user preferences.
         *
         * @return string
         */
        public function prefsAction()
        {
            $form = FormUtil::newForm($this->name, $this);

            return $form->execute('user/prefs/prefs.tpl', new Dizkus_Form_Handler_User_Prefs());
        }

        /**
         * Interface for a user to manage topic subscriptions
         *
         * @return string
         */
        public function manageForumSubscriptionsAction()
        {
            $form = FormUtil::newForm($this->name, $this);

            return $form->execute('user/prefs/manageForumSubscriptions.tpl', new Dizkus_Form_Handler_User_ForumSubscriptions());
        }

        /**
         * Interface for a user to manage topic subscriptions
         *
         * @return string
         */
        public function manageTopicSubscriptionsAction()
        {
            $form = FormUtil::newForm($this->name, $this);

            return $form->execute('user/prefs/manageTopicSubscriptions.tpl', new Dizkus_Form_Handler_User_TopicSubscriptions());
        }

        /**
         * Show all forums in index view instead of only favorite forums
         *
         */
        public function showAllForumsAction()
        {
            return $this->changeViewSetting('all');
        }

        /**
         * Show only favorite forums in index view instead of all forums
         *
         */
        public function showFavoritesAction()
        {
            return $this->changeViewSetting('favorites');
        }

        /**
         * Show only favorite forums in index view instead of all forums
         *
         */
        private function changeViewSetting($setting)
        {
            $url = ModUtil::url('Dizkus', 'user', 'index');
            if (!UserUtil::isLoggedIn()) {
                LogUtil::registerPermissionError();

                return System::redirect($url);
            }
            $uid = UserUtil::getVar('uid');
            $forumUser = $this->entityManager->find('Dizkus_Entity_ForumUser', $uid);
            if (!$forumUser) {
                $forumUser = new Dizkus_Entity_ForumUser();
                $coreUser = $this->entityManager->find('Zikula\\Module\\UsersModule\\Entity\\UserEntity', $uid);
                $forumUser->setUser($coreUser);
            }
            $method = $setting == 'favorites' ? 'showFavoritesOnly' : 'showAllForums';
            $forumUser->{$method}();
            $this->entityManager->persist($forumUser);
            $this->entityManager->flush();

            return System::redirect($url);
        }

        /**
         * Add/remove a forum from the favorites
         */
        public function modifyForumAction()
        {
            $params = array('action' => $this->request->query->get('action'), 'forum_id' => (int) $this->request->query->get('forum'));
            ModUtil::apiFunc($this->name, 'Forum', 'modify', $params);

            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $params['forum_id'])));
        }

        /**
         * Add/remove the sticky status of a topic
         */
        public function changeTopicStatusAction()
        {
            $params = array();
            $params['action'] = $this->request->query->get('action');
            $params['topic_id'] = (int) $this->request->query->get('topic');
            ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);

            return System::redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $params['topic_id'])));
        }

        /**
         * Interface for a user to manage signature
         *
         * @return string
         */
        public function signaturemanagementAction()
        {
            $form = FormUtil::newForm($this->name, $this);

            return $form->execute('user/prefs/signaturemanagement.tpl', new Dizkus_Form_Handler_User_SignatureManagement());
        }

        /**
         * User interface to email a topic to a arbitrary email-address
         *
         * @return string
         */
        public function emailtopicAction()
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
        public function viewlatestAction($args = array())
        {
            // Permission check
            $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead'));
            if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
                return System::redirect(ModUtil::url('Dizkus', 'user', 'index'));
            }
            // get the input
            $params['selorder'] = $this->request->query->get('selorder', $this->request->request->get('selorder', isset($args['selorder']) ? $args['selorder'] : 1));
            $params['nohours'] = (int) $this->request->request->get('nohours', isset($args['nohours']) ? $args['nohours'] : null);
            $params['unanswered'] = (int) $this->request->query->get('unanswered', isset($args['unanswered']) ? $args['unanswered'] : 0);
            $params['amount'] = (int) $this->request->query->get('amount', isset($args['amount']) ? $args['amount'] : null);
            $params['last_visit_unix'] = (int) $this->request->query->get('last_visit_unix', isset($args['last_visit_unix']) ? $args['last_visit_unix'] : time());
            $this->view->assign($params);
            list($posts, $text, $pager) = ModUtil::apiFunc('Dizkus', 'post', 'getLatest', $params);
            $this->view->assign('posts', $posts);
            $this->view->assign('text', $text);
            $this->view->assign('pager', $pager);
            $lastVisitUnix = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $this->view->assign('last_visit_unix', $lastVisitUnix);

            return $this->view->fetch('user/post/latest.tpl');
        }

        /**
         * Display my posts or topics
         *
         * @param array $args Arguments array.
         *
         * @return string
         */
        public function mineAction($args)
        {
            // Permission check
            $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead'));
            if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
                return System::redirect(ModUtil::url('Dizkus', 'user', 'index'));
            }
            $params = array();
            $params['action'] = $this->request->query->get('action', isset($args['action']) ? $args['action'] : 'posts');
            $params['uid'] = $this->request->query->get('user', isset($args['user']) ? $args['user'] : null);
            list($posts, $text, $pager) = ModUtil::apiFunc('Dizkus', 'post', 'search', $params);
            $this->view->assign('posts', $posts);
            $this->view->assign('text', $text);
            $this->view->assign('pager', $pager);
            $lastVisitUnix = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $this->view->assign('last_visit_unix', $lastVisitUnix);

            return $this->view->fetch('user/post/mine.tpl');
        }

        /**
         * Split topic
         *
         * @return string
         */
        public function splittopicAction()
        {
            $form = FormUtil::newForm($this->name, $this);

            return $form->execute('user/topic/split.tpl', new Dizkus_Form_Handler_User_SplitTopic());
        }

        /**
         * User interface to move a single post to another thread
         *
         * @return string
         */
        public function movepostAction()
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
        public function moderateForumAction()
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
        public function reportAction()
        {
            $form = FormUtil::newForm($this->name, $this);

            return $form->execute('user/notifymod.tpl', new Dizkus_Form_Handler_User_Report());
        }

        /**
         * generate and display an RSS feed of recent topics
         * @return string
         */
        public function feedAction()
        {
            $forum_id = $this->request->query->get('forum_id', null);
            $count = (int) $this->request->query->get('count', 10);
            $feed = $this->request->query->get('feed', 'rss20');
            $user = $this->request->query->get('user', null);
            // get the module info
            $dzkinfo = ModUtil::getInfo(ModUtil::getIdFromName('Dizkus'));
            $dzkname = $dzkinfo['displayname'];
            $mainUrl = ModUtil::url($this->name, 'user', 'index');
            if (isset($forum_id) && !is_numeric($forum_id)) {
                LogUtil::registerError($this->__f('Error! An invalid forum ID %s was encountered.', $forum_id));

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
            $link = ModUtil::url('Dizkus', 'user', 'index', array(), null, null, true);
            $forumname = DataUtil::formatForDisplay($dzkname);
            // default where clause => no where clause
            $where = array();
            /**
             * check for forum_id
             */
            if (!empty($forum_id)) {
                $managedForum = new Dizkus_Manager_Forum($forum_id);
                $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead', array('forum_id' => $forum_id)));
                $where = array('t.forum', (int) DataUtil::formatForStore($forum_id), '=');
                $link = ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id), null, null, true);
                $forumname = $managedForum->get()->getName();
            } elseif (isset($uid) && $uid != false) {
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
            $qb->select('t, f, p, fu')->from('Dizkus_Entity_Topic', 't')->join('t.forum', 'f')->join('t.last_post', 'p')->join('p.poster', 'fu');
            if (!empty($where)) {
                if ($where[2] == 'IN') {
                    $qb->expr()->in($where[0], $where[1]);
                } else {
                    $qb->where("{$where['0']} {$where['2']} :param")->setParameter('param', $where[1]);
                }
            }
            $qb->orderBy('t.topic_time', 'DESC')->setMaxResults($count);
            $topics = $qb->getQuery()->getResult();
            $posts_per_page = ModUtil::getVar('Dizkus', 'posts_per_page');
            $posts = array();
            $i = 0;
            foreach ($topics as $topic) {
                /* @var $topic Dizkus_Entity_Topic */
                $posts[$i]['title'] = $topic->getTitle();
                $posts[$i]['parenttitle'] = $topic->getForum()->getParent()->getName();
                $posts[$i]['forum_name'] = $topic->getForum()->getName();
                $posts[$i]['time'] = $topic->getTopic_time();
                $posts[$i]['unixtime'] = $topic->getTopic_time()->format('U');
                $start = (int) ((ceil(($topic->getReplyCount() + 1) / $posts_per_page) - 1) * $posts_per_page) + 1;
                $posts[$i]['post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic->getTopic_id(), 'start' => $start), null, null, true);
                $posts[$i]['last_post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic->getTopic_id(), 'start' => $start), null, 'pid' . $topic->getLast_post()->getPost_id(), true);
                $posts[$i]['rsstime'] = $topic->getTopic_time()->format(DATE_RSS);
                $i++;
            }
            $this->view->assign('posts', $posts);
            $this->view->assign('dizkusinfo', $dzkinfo);
            header('Content-Type: text/xml');

            return $this->view->display($templatefile);
        }

    }
}
