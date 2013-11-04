<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Controller;

use SecurityUtil;
use ModUtil;
use UserUtil;
use LogUtil;
use FormUtil;
use DataUtil;
use System;
use ZLanguage;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\ModUrl;
use Zikula\Module\DizkusModule\Entity\RankEntity;
use Zikula\Module\DizkusModule\Entity\ForumUserEntity;
use Zikula\Module\DizkusModule\Manager\ForumUserManager;
use Zikula\Module\DizkusModule\Manager\ForumManager;
use Zikula\Module\DizkusModule\Manager\PostManager;
use Zikula\Module\DizkusModule\Manager\TopicManager;
use Zikula\Module\DizkusModule\Form\Handler\User\NewTopic;
use Zikula\Module\DizkusModule\Form\Handler\User\EditPost;
use Zikula\Module\DizkusModule\Form\Handler\User\DeleteTopic;
use Zikula\Module\DizkusModule\Form\Handler\User\MoveTopic;
use Zikula\Module\DizkusModule\Form\Handler\User\Prefs;
use Zikula\Module\DizkusModule\Form\Handler\User\ForumSubscriptions;
use Zikula\Module\DizkusModule\Form\Handler\User\TopicSubscriptions;
use Zikula\Module\DizkusModule\Form\Handler\User\SignatureManagement;
use Zikula\Module\DizkusModule\Form\Handler\User\EmailTopic;
use Zikula\Module\DizkusModule\Form\Handler\User\SplitTopic;
use Zikula\Module\DizkusModule\Form\Handler\User\MovePost;
use Zikula\Module\DizkusModule\Form\Handler\User\ModerateForum;
use Zikula\Module\DizkusModule\Form\Handler\User\Report;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
            return $this->view->fetch('user/dizkus_disabled.tpl');
        }
        $indexTo = $this->getVar('indexTo');
        if (!empty($indexTo)) {
            $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'viewforum', array('forum' => (int) $indexTo))));
            $response->send();
            exit;
        }
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw $this->createAccessDeniedHttpException();
        }
        $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
        $this->view->assign('last_visit_unix', $lastVisitUnix);
        // get the forms to display
        $showOnlyFavorites = ModUtil::apiFunc($this->name, 'Favorites', 'getStatus');
        $siteFavoritesAllowed = $this->getVar('favorites_enabled') == 'yes';
        $uid = UserUtil::getVar('uid');
        $qb = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->childrenQueryBuilder();
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
                $managedForumUser = new ForumUserManager($uid);
                $managedForumUser->displayFavoriteForumsOnly(false);
                $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
                $response->send();
                exit;
            } else {
                LogUtil::registerError($this->__('This site has not set up any forums or they are all private. Contact the administrator.'));
            }
        }
        $this->view->assign('forums', $forums);
        $numposts = ModUtil::apiFunc($this->name, 'user', 'countstats', array('id' => '0', 'type' => 'all'));
        $this->view->assign('numposts', $numposts);

        return $this->response($this->view->fetch('user/main.tpl'));
    }

    /**
     * View forum by id
     *
     * opens a forum and shows the last postings
     * @param integer forum (via GET) the forum id
     * @param uinteger start (via GET) the posting to start with if on page 1+
     *
     * @return string
     */
    public function viewforumAction()
    {
        if ($this->getVar('forum_enabled') == 'no' && !SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return $this->view->fetch('user/dizkus_disabled.tpl');
        }
        // get the input
        $forumId = (int)$this->request->query->get('forum', null);
        if (!($forumId > 0)) {
            throw new NotFoundHttpException($this->__('That forum doesn\'t exist!'));
        }
        $start = (int)$this->request->query->get('start', 1);
        $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
        $managedForum = new ForumManager($forumId);
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $managedForum->get())) {
            throw $this->createAccessDeniedHttpException();
        }
        // filter the forum children by permissions
        $forum = ModUtil::apiFunc($this->name, 'Permission', 'filterForumChildrenByPermission', $managedForum->get());
        $this->view->assign('forum', $forum)
            ->assign('topics', $managedForum->getTopics($start))
            ->assign('pager', $managedForum->getPager())
            ->assign('permissions', $managedForum->getPermissions())
            ->assign('isModerator', $managedForum->isModerator())
            ->assign('breadcrumbs', $managedForum->getBreadcrumbs())
            ->assign('hot_threshold', $this->getVar('hot_threshold'))
            ->assign('last_visit_unix', $lastVisitUnix);

        return $this->response($this->view->fetch('user/forum/view.tpl'));
    }

    /**
     * viewtopic
     *
     * @param integer topic (via GET) the topic ID
     * @param integer post (via GET) a post ID
     * @param integer start (via GET) pager value
     *
     * @return string
     */
    public function viewtopicAction()
    {
        if ($this->getVar('forum_enabled') == 'no' && !SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return $this->view->fetch('user/dizkus_disabled.tpl');
        }
        // get the input
        $topicId = (int)$this->request->query->get('topic', null);
        $post_id = (int)$this->request->query->get('post', null);
        $start = (int)$this->request->query->get('start', 1);
        $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
        if (!empty($post_id) && is_numeric($post_id) && empty($topicId)) {
            $managedPost = new PostManager($post_id);
            $topic_id = $managedPost->getTopicId();
            if ($topic_id != false) {
                // redirect instad of continue, better for SEO
                $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $topic_id))));
                $response->send();
                exit;
            }
        }
        $managedTopic = new TopicManager($topicId);
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $managedTopic->get()->getForum())) {
            throw $this->createAccessDeniedHttpException();
        }
        if (!$managedTopic->exists()) {
            return LogUtil::registerError($this->__f('Error! The topic you selected (ID: %s) was not found. Please go back and try again.', array($topicId)), null, ModUtil::url($this->name, 'user', 'index'));
        }
        list(, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => RankEntity::TYPE_POSTCOUNT));
        $this->view->assign('ranks', $ranks);
        $this->view->assign('start', $start);
        $this->view->assign('topic', $managedTopic->get());
        $this->view->assign('posts', $managedTopic->getPosts(--$start));
        $this->view->assign('pager', $managedTopic->getPager());
        $this->view->assign('permissions', $managedTopic->getPermissions());
        $this->view->assign('isModerator', $managedTopic->getManagedForum()->isModerator());
        $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
        $this->view->assign('isSubscribed', $managedTopic->isSubscribed());
        $this->view->assign('nextTopic', $managedTopic->getNext());
        $this->view->assign('previousTopic', $managedTopic->getPrevious());
        $this->view->assign('last_visit_unix', $lastVisitUnix);
        $this->view->assign('preview', false);
        $managedTopic->incrementViewsCount();

        return $this->response($this->view->fetch('user/topic/view.tpl'));
    }

    /**
     * reply to a post
     *
     * @param integer 'forum' (via POST) the forum ID
     * @param integer 'topic' (via POST) the topic ID
     * @param integer 'post' (via POST) the post ID
     * @param string 'returnurl' (via POST) encoded url string
     * @param string 'message' (via POST) the content of the post
     * @param integer 'attach_signature' (via POST)
     * @param integer 'subscribe_topic' (via POST)
     * @param string 'preview' (via POST) submit button converted to boolean
     * @param string 'submit' (via POST) submit button converted to boolean
     * @param string 'cancel' (via POST) submit button converted to boolean
     *
     * @return string
     */
    public function replyAction()
    {
        // Comment Permission check
        $forum_id = (int) $this->request->request->get('forum', null);
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', array('forum_id' => $forum_id))) {
            throw $this->createAccessDeniedHttpException();
        }
        $this->checkCsrfToken();
        // get the input
        $topic_id = (int)$this->request->request->get('topic', null);
        $post_id = (int)$this->request->request->get('post', null);
        $returnurl = $this->request->request->get('returnurl', null);
        $message = $this->request->request->get('message', '');
        $attach_signature = (int)$this->request->request->get('attach_signature', 0);
        $subscribe_topic = (int)$this->request->request->get('subscribe_topic', 0);
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
            $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $topic_id))));
            $response->send();
            exit;
        }
        $message = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $message);
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
            $hook = new ValidationHook(new ValidationProviders());
            $hookvalidators = $this->dispatchHooks('dizkus.ui_hooks.post.validate_edit', $hook)->getValidators();
            if ($hookvalidators->hasErrors()) {
                LogUtil::registerError($this->__('Error! Hooked content does not validate.'));
                $isPreview = true;
            }
        }
        if ($submit && !$isPreview) {
            $data = array(
                'topic_id' => $topic_id,
                'post_text' => $message,
                'attachSignature' => $attach_signature);
            $managedPost = new PostManager();
            $managedPost->create($data);
            // check to see if the post contains spam
            if (ModUtil::apiFunc($this->name, 'user', 'isSpam', $managedPost->get())) {
                LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
                return false;
            }
            $managedPost->persist();
            // handle subscription
            if ($subscribe_topic) {
                ModUtil::apiFunc($this->name, 'topic', 'subscribe', array('topic' => $topic_id));
            } else {
                ModUtil::apiFunc($this->name, 'topic', 'unsubscribe', array('topic' => $topic_id));
            }
            $start = ModUtil::apiFunc($this->name, 'user', 'getTopicPage', array('replyCount' => $managedPost->get()->getTopic()->getReplyCount()));
            $params = array(
                'topic' => $topic_id,
                'start' => $start);
            $url = new ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), $params, 'pid' . $managedPost->getId());
            $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedPost->getId(), $url));
            // notify topic & forum subscribers
            $notified = ModUtil::apiFunc($this->name, 'notify', 'emailSubscribers', array('post' => $managedPost->get()));
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
                $url = new ModUrl($mod, $type, $func, ZLanguage::getLanguageCode(), $params, 'pid' . $managedPost->getId());
            }

            $response = new RedirectResponse(System::normalizeUrl($url->getUrl()));
            $response->send();
            exit;
        } else {
            $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
            $managedTopic = new TopicManager($topic_id);
            $managedPoster = new ForumUserManager();
            $reply = array(
                'topic_id' => $topic_id,
                'post_id' => $post_id,
                'attach_signature' => $attach_signature,
                'subscribe_topic' => $subscribe_topic,
                'topic' => $managedTopic->toArray(),
                'message' => $message);
            $post = array(
                'post_id' => 0,
                'topic_id' => $topic_id,
                'poster' => $managedPoster->toArray(),
                'post_time' => time(),
                'attachSignature' => $attach_signature,
                'post_text' => $message,
                'userAllowedToEdit' => false);
            // Do not show edit link
            $permissions = array();
            list(, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => RankEntity::TYPE_POSTCOUNT));
            $this->view->assign('ranks', $ranks);
            $this->view->assign('post', $post);
            $this->view->assign('reply', $reply);
            $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
            $this->view->assign('preview', $isPreview);
            $this->view->assign('last_visit_unix', $lastVisitUnix);
            $this->view->assign('permissions', $permissions);

            return $this->response($this->view->fetch('user/topic/reply.tpl'));
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

        return $form->execute('user/topic/new.tpl', new NewTopic());
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

        return $form->execute('user/post/edit.tpl', new EditPost());
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

        return $form->execute('user/topic/delete.tpl', new DeleteTopic());
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

        return $form->execute('user/topic/move.tpl', new MoveTopic());
    }

    /**
     * View the posters IP information
     *
     * @return string
     */
    public function viewIpDataAction()
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
            throw $this->createAccessDeniedHttpException();
        }
        $post_id = (int)$this->request->query->filter('post', 0, FILTER_VALIDATE_INT);
        if ($post_id == 0) {
            return LogUtil::registerArgsError();
        }
        $this->view->assign('viewip', ModUtil::apiFunc($this->name, 'user', 'get_viewip_data', array('post_id' => $post_id)))->assign('post_id', $post_id);

        return $this->response($this->view->fetch('user/viewip.tpl'));
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

        return $form->execute('user/prefs/prefs.tpl', new Prefs());
    }

    /**
     * Interface for a user to manage topic subscriptions
     *
     * @return string
     */
    public function manageForumSubscriptionsAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/prefs/manageForumSubscriptions.tpl', new ForumSubscriptions());
    }

    /**
     * Interface for a user to manage topic subscriptions
     *
     * @return string
     */
    public function manageTopicSubscriptionsAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/prefs/manageTopicSubscriptions.tpl', new TopicSubscriptions());
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
        $url = ModUtil::url($this->name, 'user', 'index');
        if (!UserUtil::isLoggedIn()) {
            LogUtil::registerPermissionError();
            $response = new RedirectResponse(System::normalizeUrl($url));
            $response->send();
            exit;
        }
        $uid = UserUtil::getVar('uid');
        $forumUser = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\ForumUserEntity', $uid);
        if (!$forumUser) {
            $forumUser = new ForumUserEntity($uid);
        }
        $method = $setting == 'favorites' ? 'showFavoritesOnly' : 'showAllForums';
        $forumUser->{$method}();
        $this->entityManager->persist($forumUser);
        $this->entityManager->flush();

        $response = new RedirectResponse(System::normalizeUrl($url));
        $response->send();
        exit;
    }

    /**
     * Add/remove a forum from the favorites
     */
    public function modifyForumAction()
    {
        $params = array(
            'action' => $this->request->query->get('action'),
            'forum_id' => (int)$this->request->query->get('forum'));
        ModUtil::apiFunc($this->name, 'Forum', 'modify', $params);

        $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'viewforum', array('forum' => $params['forum_id']))));
        $response->send();
        exit;
    }

    /**
     * Add/remove the sticky status of a topic
     */
    public function changeTopicStatusAction()
    {
        $params = array();
        $params['action'] = $this->request->query->get('action');
        $params['topic_id'] = (int)$this->request->query->get('topic');
        $params['post_id'] = (int)$this->request->query->get('post', null);
        ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);

        $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $params['topic_id']))));
        $response->send();
        exit;
    }

    /**
     * Interface for a user to manage signature
     *
     * @return string
     */
    public function signaturemanagementAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/prefs/signaturemanagement.tpl', new SignatureManagement());
    }

    /**
     * User interface to email a topic to a arbitrary email-address
     *
     * @return string
     */
    public function emailtopicAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/topic/email.tpl', new EmailTopic());
    }

    /**
     * View latest topics
     *
     * @param string selorder (via GET)
     * @param integer nohours (via GET)
     * @param integer unanswered (via GET)
     * @param integer last_visit_unix (via get)
     *
     * @return string
     */
    public function viewlatestAction()
    {
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw $this->createAccessDeniedHttpException();
        }
        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
            $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
            $response->send();
            exit;
        }
        // get the input
        $params = array();
        $params['selorder'] = $this->request->get('selorder', 1);
        $params['nohours'] = (int)$this->request->request->get('nohours', 24);
        $params['unanswered'] = (int)$this->request->query->get('unanswered', 0);
        $params['amount'] = (int)$this->request->query->get('amount', null);
        $params['last_visit_unix'] = (int)$this->request->query->get('last_visit_unix', time());
        $this->view->assign($params);
        list($topics, $text, $pager) = ModUtil::apiFunc($this->name, 'post', 'getLatest', $params);
        $this->view->assign('topics', $topics);
        $this->view->assign('text', $text);
        $this->view->assign('pager', $pager);
        $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
        $this->view->assign('last_visit_unix', $lastVisitUnix);

        return $this->response($this->view->fetch('user/topic/latest.tpl'));
    }

    /**
     * Display my posts or topics
     *
     * @param string action (via GET)
     * @param number uid (via GET)
     *
     * @return string
     */
    public function mineAction()
    {
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw $this->createAccessDeniedHttpException();
        }
        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
            $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'index')));
            $response->send();
            exit;
        }
        $params = array();
        $params['action'] = $this->request->query->get('action', 'posts');
        $params['uid'] = $this->request->query->get('user', null);
        list($topics, $text, $pager) = ModUtil::apiFunc($this->name, 'post', 'search', $params);
        $this->view->assign('topics', $topics);
        $this->view->assign('text', $text);
        $this->view->assign('pager', $pager);
        $this->view->assign('action', $params['action']);
        $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
        $this->view->assign('last_visit_unix', $lastVisitUnix);

        return $this->response($this->view->fetch('user/post/mine.tpl'));
    }

    /**
     * Split topic
     *
     * @return string
     */
    public function splittopicAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/topic/split.tpl', new SplitTopic());
    }

    /**
     * User interface to move a single post to another thread
     *
     * @return string
     */
    public function movepostAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('user/post/move.tpl', new MovePost());
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

        return $form->execute('user/forum/moderate.tpl', new ModerateForum());
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

        return $form->execute('user/notifymod.tpl', new Report());
    }

    /**
     * generate and display an RSS feed of recent topics
     * @return string
     */
    public function feedAction()
    {
        $forum_id = $this->request->query->get('forum_id', null);
        $count = (int)$this->request->query->get('count', 10);
        $feed = $this->request->query->get('feed', 'rss20');
        $user = $this->request->query->get('user', null);
        // get the module info
        $dzkinfo = ModUtil::getInfo(ModUtil::getIdFromName($this->name));
        $dzkname = $dzkinfo['displayname'];
        $mainUrl = ModUtil::url($this->name, 'user', 'index');
        if (isset($forum_id) && !is_numeric($forum_id)) {
            LogUtil::registerError($this->__f('Error! An invalid forum ID %s was encountered.', $forum_id));

            $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($mainUrl)));
            $response->send();
            exit;
        }
        /**
         * check if template for feed exists
         */
        $templatefile = 'feed/' . DataUtil::formatForOS($feed) . '.tpl';
        if (!$this->view->template_exists($templatefile)) {
            // silently stop working
            LogUtil::registerError($this->__f('Error! Could not find a template for an %s-type feed.', $feed));

            $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($mainUrl)));
            $response->send();
            exit;
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
        $link = ModUtil::url($this->name, 'user', 'index', array(), null, null, true);
        $forumname = DataUtil::formatForDisplay($dzkname);
        // default where clause => no where clause
        $where = array();
        /**
         * check for forum_id
         */
        if (!empty($forum_id)) {
            $managedForum = new ForumManager($forum_id);
            if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', array('forum_id' => $forum_id))) {
                throw $this->createAccessDeniedHttpException();
            }
            $where = array('t.forum', (int)$forum_id, '=');
            $link = ModUtil::url($this->name, 'user', 'viewforum', array('forum' => $forum_id), null, null, true);
            $forumname = $managedForum->get()->getName();
        } elseif (isset($uid) && $uid != false) {
            $where = array('p.poster', $uid, '=');
        } else {
            $allowedforums = ModUtil::apiFunc($this->name, 'forum', 'getForumIdsByPermission');
            if (count($allowedforums) > 0) {
                $where = array('f.forum', $allowedforums, 'IN');
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
            ->from('Zikula\Module\DizkusModule\Entity\TopicEntity', 't')
            ->join('t.forum', 'f')
            ->join('t.last_post', 'p')
            ->join('p.poster', 'fu');
        if (!empty($where)) {
            if (is_array($where[1])) {
                $qb->expr()->in($where[0], $where[1]);
            } else {
                $qb->where("{$where['0']} {$where['2']} :param")->setParameter('param', $where[1]);
            }
        }
        $qb->orderBy('t.topic_time', 'DESC')->setMaxResults($count);
        $topics = $qb->getQuery()->getResult();
        $posts_per_page = $this->getVar('posts_per_page');
        $posts = array();
        $i = 0;
        foreach ($topics as $topic) {
            /* @var $topic \Zikula\Module\DizkusModule\Entity\TopicEntity */
            $posts[$i]['title'] = $topic->getTitle();
            $posts[$i]['parenttitle'] = $topic->getForum()->getParent()->getName();
            $posts[$i]['forum_name'] = $topic->getForum()->getName();
            $posts[$i]['time'] = $topic->getTopic_time();
            $posts[$i]['unixtime'] = $topic->getTopic_time()->format('U');
            $start = (int) ((ceil(($topic->getReplyCount() + 1) / $posts_per_page) - 1) * $posts_per_page) + 1;
            $posts[$i]['post_url'] = ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $topic->getTopic_id(), 'start' => $start), null, null, true);
            $posts[$i]['last_post_url'] = ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $topic->getTopic_id(), 'start' => $start), null, 'pid' . $topic->getLast_post()->getPost_id(), true);
            $posts[$i]['rsstime'] = $topic->getTopic_time()->format(DATE_RSS);
            $i++;
        }
        $this->view->assign('posts', $posts);
        $this->view->assign('dizkusinfo', $dzkinfo);
        header('Content-Type: text/xml');

        return $this->view->display($templatefile); // using plainresponse here isn't working
    }

}