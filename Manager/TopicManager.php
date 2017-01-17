<?php

/**
 * Copyright Dizkus Team 2012.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * @link https://github.com/zikula-modules/Dizkus
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\DizkusModule\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Hook\ProcessHook;
use Zikula\DizkusModule\Entity\ForumUserEntity;
use Zikula\DizkusModule\Entity\PostEntity;
use Zikula\DizkusModule\Entity\TopicEntity;
use Zikula\DizkusModule\Helper\SynchronizationHelper;
use Zikula\DizkusModule\Security\Permission;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Api\CurrentUserApi;

class TopicManager
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CurrentUserApi
     */
    private $userApi;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var forumUserManagerService
     */
    private $forumUserManagerService;

    /**
     * @var ForumManagerService
     */
    private $forumManagerService;

    /**
     * @var synchronizationHelper
     */
    private $synchronizationHelper;

    /**
     * managed topic.
     *
     * @var TopicEntity
     */
    private $_topic;
    private $_itemsPerPage;
    private $_defaultPostSortOrder;
    private $_numberOfItems;

    private $managedForum;

    /**
     * first post in topic.
     *
     * @var PostEntity
     */
    private $_firstPost = null;
    private $_subscribe = false;
    private $_forumId;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $name;

    public function __construct(
            TranslatorInterface $translator,
            RouterInterface $router,
            RequestStack $requestStack,
            EntityManager $entityManager,
            CurrentUserApi $userApi,
            Permission $permission,
            VariableApi $variableApi,
            ForumUserManager $forumUserManagerService,
            ForumManager $forumManagerService,
            SynchronizationHelper $synchronizationHelper
         ) {
        $this->name = 'ZikulaDizkusModule';
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->userApi = $userApi;
        $this->permission = $permission;
        $this->variableApi = $variableApi;

        $this->forumUserManagerService = $forumUserManagerService;
        $this->forumManagerService = $forumManagerService;
        $this->synchronizationHelper = $synchronizationHelper;

        $this->_itemsPerPage = $this->variableApi->get($this->name, 'posts_per_page');
        $this->_defaultPostSortOrder = $this->variableApi->get($this->name, 'post_sort_order');
    }

    /**
     * construct.
     */
    public function getManager($id = null, TopicEntity $topic = null)
    {
        if (isset($topic)) {
            // topic has been injected
            $this->_topic = $topic;
            $this->managedForum = $this->forumManagerService->getManager(null, $this->_topic->getForum()); //new ForumManager(null, $this->_topic->getForum());
        } elseif ($id > 0) {
            // find existing topic
            $this->_topic = $this->entityManager->find('Zikula\DizkusModule\Entity\TopicEntity', $id);
            $this->managedForum = $this->forumManagerService->getManager(null, $this->_topic->getForum()); //new ForumManager(null, $this->_topic->getForum());
        } else {
            // create new topic
            $this->_topic = new TopicEntity();
        }

        return $this;
    }

    /**
     * Check if topic exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->_topic ? true : false;
    }

    /**
     * return page as array.
     *
     * @return mixed array or false
     */
    public function toArray()
    {
        if (!$this->_topic) {
            return false;
        }

        return $this->_topic->toArray();
    }

    /**
     * return topic id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->_topic->getTopic_id();
    }

    /**
     * return topic title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_topic->getTitle();
    }

    /**
     * return topic as doctrine2 object.
     *
     * @return TopicEntity
     */
    public function get()
    {
        return $this->_topic;
    }

    /**
     * return topic forum id.
     *
     * @return int
     */
    public function getForumId()
    {
        return $this->_topic->getForum()->getForum_id();
    }

    /**
     * @return ForumManager
     */
    public function getManagedForum()
    {
        return $this->managedForum;
    }

    public function getFirstPost()
    {
        return $this->_firstPost;
    }

    public function getPermissions()
    {
        return $this->permission->get($this->_topic->getForum());
    }

    /**
     * return posts of a topic as doctrine2 object.
     *
     * @return object
     */
    public function getPosts($startNumber = 1)
    {
        if ($this->userApi->isLoggedIn()) {
            $managedForumUser = $this->forumUserManagerService->getManager(); //new ForumUserManager();
           $postSortOrder = $managedForumUser->getPostOrder();
        } else {
            $postSortOrder = $this->_defaultPostSortOrder;
        }
        dump($postSortOrder);
        // do not allow negative first result
        $startNumber = $startNumber > 0 ? $startNumber : 0;
        // Do a new query in order to limit maxresults, firstresult, order, etc.
        $query = $this->entityManager->createQueryBuilder()
            ->select('p, u, r')
            ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
            ->where('p.topic = :topicId')
            ->setParameter('topicId', $this->_topic->getTopic_id())
            ->leftJoin('p.poster', 'u')
            ->leftJoin('u.rank', 'r')
            ->orderBy('p.post_time', $postSortOrder)
            ->getQuery();
        $query->setFirstResult($startNumber)->setMaxResults($this->_itemsPerPage);
        $paginator = new Paginator($query, false);
        $this->_numberOfItems = $paginator->count();

        return $paginator;
    }

    /**
     * return pager.
     *
     * @return array
     */
    public function getPager()
    {
        return [
            'itemsperpage' => $this->_itemsPerPage,
            'numitems'     => $this->_numberOfItems, ];
    }

    /**
     * get forum bread crumbs.
     *
     * @return string
     */
    public function getBreadcrumbs()
    {
        return $this->managedForum->getBreadcrumbs(false);
    }

    /**
     * add to views count.
     */
    public function incrementViewsCount()
    {
        $this->_topic->incrementViewCount();
        $this->entityManager->flush();
    }

    public function setLastPost(PostEntity $lastPost)
    {
        $this->_topic->setLast_post($lastPost);
    }

    public function setTitle($title)
    {
        $this->_topic->setTitle($title);
        $this->entityManager->flush();
    }

    /**
     * add to replies count.
     */
    public function incrementRepliesCount()
    {
        $this->_topic->incrementReplyCount();
        $this->entityManager->flush();
    }

    /**
     * subtract from replies count.
     */
    public function decrementRepliesCount()
    {
        $this->_topic->decrementReplyCount();
        $this->entityManager->flush();
    }

    /**
     * @param int    $data['forum_id']
     * @param string $data['message']
     * @param bool   $data['attachSignature']
     * @param string $data['title']
     * @param bool   $data['subscribe_topic']
     */
    public function prepare($data)
    {
        // prepare first post
        $this->_firstPost = new PostEntity();

        // @todo this was in controller but should be moved to Post manager.
        //$data['message'] = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $data['message']);
        //$data['title'] = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $data['title']);

        $this->_firstPost->setPost_text($data['message']);
        unset($data['message']);
        $this->_firstPost->setAttachSignature($data['attachSignature']);
        unset($data['attachSignature']);
        $this->_firstPost->setTitle($data['title']);
        $this->_firstPost->setTopic($this->_topic);
        $this->_firstPost->setIsFirstPost(true);
        $this->_subscribe = $data['subscribeTopic'];
        unset($data['subscribeTopic']);
        $this->_forumId = $data['forum_id'];
        $this->managedForum = $this->forumManagerService->getManager($this->_forumId); //new ForumManager($this->_forumId);
        $this->_topic->setForum($this->managedForum->get());
        unset($data['forum_id']);
        $solveStatus = isset($data['isSupportQuestion']) && ($data['isSupportQuestion'] == 1) ? -1 : 0; // -1 = support request
        $this->_topic->setSolved($solveStatus);
        unset($data['isSupportQuestion']);
        $this->_topic->setLast_post($this->_firstPost);
        $this->_topic->merge($data);
        // prepare poster data or assign anonymous creations to the admin
        $uid = $this->userApi->isLoggedIn() ? $this->request->getSession()->get('uid') : $this->variableApi->get($this->name, 'defaultPoster', 2);

        $forumUser = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumUserEntity', $uid);
        if (!$forumUser) {
            $forumUser = new ForumUserEntity($uid);
        }
        $forumUser->incrementPostCount();
        $this->_firstPost->setPoster($forumUser);
        $this->_topic->setPoster($forumUser);
    }

    /**
     * Add hook data to topic.
     *
     * @param ProcessHook $hook
     */
    public function setHookData(ProcessHook $hook)
    {
        $this->_topic->setHookedModule($hook->getCaller());
        $this->_topic->setHookedObjectId($hook->getId());
        $this->_topic->setHookedAreaId($hook->getAreaId());
        $this->_topic->setHookedUrlObject($hook->getUrl());
    }

    public function getPreview()
    {
        return $this->_firstPost;
    }

    /**
     * create topic and post.
     *
     * @return int topic id
     */
    public function create()
    {
        // write topic
        $this->entityManager->persist($this->_topic);
        $this->entityManager->persist($this->_firstPost);
        // increment forum post count
        $this->managedForum->incrementPostCount();
        $this->managedForum->incrementTopicCount();
        $this->managedForum->setLastPost($this->_firstPost);
        $this->entityManager->flush();
        // subscribe
        if ($this->_subscribe) {
            //            $params = [
//                'topic' => $this->_topic->getTopic_id(),
//                'action' => 'subscribe'];
//            ModUtil::apiFunc($this->name, 'topic', 'changeStatus', $params);
            $this->changeStatus($this->_topic->getTopic_id(), 'subscribe');
        }

        return $this->_topic->getTopic_id();
    }

    /**
     * set topic sticky.
     *
     * @return bool
     */
    public function sticky()
    {
        $this->_topic->sticky();
        $this->entityManager->flush();

        return true;
    }

    /**
     * set topic unsticky.
     *
     * @return bool
     */
    public function unsticky()
    {
        $this->_topic->unsticky();
        $this->entityManager->flush();

        return true;
    }

    /**
     * lock topic.
     *
     * @return bool
     */
    public function lock()
    {
        $this->_topic->lock();
        $this->entityManager->flush();

        return true;
    }

    /**
     * unlock topic.
     *
     * @return bool
     */
    public function unlock()
    {
        $this->_topic->unlock();
        $this->entityManager->flush();

        return true;
    }

    /**
     * set topic solved.
     *
     * @param int $postid
     *
     * @return bool
     */
    public function solve($postid)
    {
        $this->_topic->setSolved($postid);
        $this->entityManager->flush();

        return true;
    }

    /**
     * set topic unsolved.
     *
     * @return bool
     */
    public function unsolve()
    {
        $this->_topic->setSolved(-1);
        $this->entityManager->flush();

        return true;
    }

    /**
     * get if the current user is subscribed.
     *
     * @return bool
     */
    public function isSubscribed()
    {
        if (!$this->userApi->isLoggedIn()) {
            return false;
        }

        $uid = $this->request->getSession()->get('uid');

        $topicSubscription = $this->entityManager
            ->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')
            ->findOneBy(['topic' => $this->_topic, 'forumUser' => $uid]);

        return isset($topicSubscription);
    }

    /**
     * find last post by post_time and set.
     */
    public function resetLastPost($flush = false)
    {
        $dql = 'SELECT p FROM Zikula\DizkusModule\Entity\PostEntity p
            WHERE p.topic = :topic
            ORDER BY p.post_time DESC';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('topic', $this->_topic);
        $query->setMaxResults(1);
        $post = $query->getSingleResult();
        $this->_topic->setLast_post($post);
        $this->_topic->setTopic_time($post->getPost_time());
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * get the number of posts in this topic.
     *
     * @return int
     */
    public function getPostCount()
    {
        return $this->_topic->getReplyCount();
    }

    /**
     * Get the next topic (by time) in the same Forum.
     *
     * @return int
     */
    public function getNext()
    {
        return $this->getAdjacent('>', 'ASC');
    }

    /**
     * Get the previous topic (by time) in the same Forum.
     *
     * @return int
     */
    public function getPrevious()
    {
        return $this->getAdjacent('<', 'DESC');
    }

    /**
     * Get the adjacent topic (by time) in the same Forum.
     *
     * @param $oper string less than or greater than operator < or >
     * @param $dir string Sort direction ASC/DESC
     *
     * @return int
     */
    private function getAdjacent($oper, $dir)
    {
        $dql = "SELECT t.topic_id FROM Zikula\DizkusModule\Entity\TopicEntity t
            WHERE t.topic_time {$oper} :time
            AND t.forum = :forum
            AND t.sticky = 0
            ORDER BY t.topic_time {$dir}";
        $result = $this->entityManager->createQuery($dql)
            ->setParameter('time', $this->_topic->getTopic_time())
            ->setParameter('forum', $this->_topic->getForum())
            ->setMaxResults(1)
            ->getScalarResult();
        if ($result) {
            return $result[0]['topic_id'];
        } else {
            return $this->_topic->getTopic_id(); // return current value (checks in template for this)
        }
    }

    /**
     * @param $topic
     * @param $action
     * @param $post
     * @param $title
     *
     * @throws \InvalidArgumentException
     */
    public function changeStatus($topic, $action, $post = null, $title = '')
    {
        if (empty($topic)) {
            throw new \InvalidArgumentException();
        }
        $managedTopic = $this->getManager($topic);
        $perms = $managedTopic->getPermissions();
        switch ($action) {
            case 'subscribe':
                if ($this->userApi->isLoggedIn()) {
                    $this->subscribe($managedTopic->get());
                }
                break;
            case 'unsubscribe':
                if ($this->userApi->isLoggedIn()) {
                    $this->unsubscribe($managedTopic->get());
                }
                break;
            case 'sticky':
                if ($perms['moderate']) {
                    $managedTopic->sticky();
                }
                break;
            case 'unsticky':
                if ($perms['moderate']) {
                    $managedTopic->unsticky();
                }
                break;
            case 'lock':
                if ($perms['moderate']) {
                    $managedTopic->lock();
                }
                break;
            case 'unlock':
                if ($perms['moderate']) {
                    $managedTopic->unlock();
                }
                break;
            case 'solve':
                if ($perms['edit'] || $managedTopic->get()->userAllowedToEdit()) {
                    if (empty($post)) {
                        throw new \InvalidArgumentException();
                    }
                    $managedTopic->solve($post);
                }
                break;
            case 'unsolve':
                if ($perms['edit'] || $managedTopic->get()->userAllowedToEdit()) {
                    $managedTopic->unsolve();
                }
                break;
            case 'setTitle':
                if ($perms['edit'] || $managedTopic->get()->userAllowedToEdit()) {
                    if (empty($title)) {
                        throw new \InvalidArgumentException();
                    }
                    $managedTopic->setTitle($title);
                }
                break;
        }
    }

    /**
     * Subscribe a topic.
     *
     * @param int|object $topic   Topic id or object.
     * @param int        $user_id User id (optional: needs ACCESS_ADMIN).
     *
     * @return bool|void
     */
    public function subscribe($topic, $user_id = null)
    {
        if (isset($user_id) && !$this->permission->canAdministrate()) {
            throw new AccessDeniedException();
        } else {
            $loggedIn = $this->userApi->isLoggedIn();
            $user_id = $loggedIn ? $this->request->getSession()->get('uid') : 1;
        }
        if (!is_object($topic)) {
            // @todo what if topic is not found
            $topic = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->findOneBy(['topic_id' => $topic]);
        }
        // Permission check
        if (!$this->permission->canRead($topic->getForum())) {
            throw new AccessDeniedException();
        }

        $managedForumUser = $this->forumUserManagerService->getManager($user_id); //new ForumUserManager($user_id);
        $searchParams = [
            'topic'     => $topic,
            'forumUser' => $managedForumUser->get(), ];
        $topicSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')->findOneBy($searchParams);
        if (!$topicSubscription) {
            $managedForumUser->get()->addTopicSubscription($topic);
            $this->entityManager->flush();
        }
    }

    /**
     * Unsubscribe a topic.
     *
     * @param int|object $topic   Topic id or object.
     * @param int        $user_id User id (optional: needs ACCESS_ADMIN).
     *
     * @return void|bool
     */
    public function unsubscribe($topic, $user_id = null)
    {
        if (isset($user_id) && !$this->permission->canAdministrate()) {
            throw new AccessDeniedException();
        } else {
            $loggedIn = $this->userApi->isLoggedIn();
            $user_id = $loggedIn ? $this->request->getSession()->get('uid') : 1;
        }
        if (!is_object($topic)) {
            // @todo what if topic is not found
            $topic = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->findOneBy(['topic_id' => $topic]);
        }
        // Permission check
        if (!$this->permission->canRead($topic->getForum())) {
            throw new AccessDeniedException();
        }

        $managedForumUser = $this->forumUserManagerService->getManager($user_id); //new ForumUserManager($user_id);
        if (isset($topic)) {
            $topicSubscription = $this->entityManager
                ->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')
                ->findOneBy(['topic' => $topic, 'forumUser' => $managedForumUser->get()]);
            $managedForumUser->get()->removeTopicSubscription($topicSubscription);
        } else {
            // not used in the code...
            $managedForumUser->get()->clearTopicSubscriptions();
        }
        $this->entityManager->flush();
    }

    /**
     * Get topic subscriptions.
     *
     * @param int $user_id User id (optional).
     *
     * @return \Zikula\Module\DizkusModule\Entity\TopicSubscriptionEntity collection, may be empty
     */
    public function getSubscriptions($user_id = null)
    {
        if (empty($user_id)) {
            $loggedIn = $this->userApi->isLoggedIn();
            $user_id = $loggedIn ? $this->request->getSession()->get('uid') : 1;
        }
        $managedForumUser = $this->forumUserManagerService->getManager($user_id); //new ForumUserManager($user_id);

        return $managedForumUser->get()->getTopicSubscriptions();
    }

    /**
     * getIdByReference.
     *
     * Gets a topic reference as parameter and delivers the internal topic id used for Dizkus as comment module
     *
     * @param string $reference The reference.
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return array Topic data as array
     */
    public function getIdByReference($reference)
    {
        if (empty($reference)) {
            throw new \InvalidArgumentException();
        }

        return $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->findOneBy(['reference' => $reference])->toArray();
    }

    /**
     * getTopicPage
     * Uses the number of replyCount and the posts_per_page settings to determine the page
     * number of the last post in the thread. This is needed for easier navigation.
     *
     * @param $replyCount int number of topic replies
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int page number of last posting in the thread
     */
    public function getTopicPage($replyCount)
    {
        if (!isset($replyCount) || !is_numeric($replyCount) || $replyCount < 0) {
            throw new \InvalidArgumentException();
        }
        // get some environment
        $posts_per_page = $this->variableApi->get($this->name, 'posts_per_page');
        if ($this->userApi->isLoggedIn()) {
            $managedForumUser = $this->forumUserManagerService->getManager($user_id); // new ForumUserManager();
            $postSortOrder = $managedForumUser->getPostOrder();
        } else {
            $postSortOrder = $this->variableApi->get($this->name, 'post_sort_order');
        }

        $last_page = 1;
        if ($postSortOrder == 'ASC') {
            // +1 for the initial posting
            $last_page = floor($replyCount / $posts_per_page) * $posts_per_page + 1;
        }
        // if not ASC then DESC which means latest topic is on top anyway...
        return $last_page;
    }

    /**
     * delete a topic.
     *
     * This function deletes a topic given by id or object
     *
     * @param $topic The topic's id or object
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int the forum's id for redirecting
     */
    public function delete($topic)
    {
        if (is_numeric($topic)) {
            // @todo what if topic not found?
            $topic = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->find($topic);
        } elseif (!$topic instanceof TopicEntity) {
            throw new \InvalidArgumentException();
        }

        $forum = $topic->getForum();

        // Permission check
        if (!$this->permission->canModerate(['forum_id' => $forum->getForum_id()])) {
            throw new AccessDeniedException();
        }

        $posts = $topic->getPosts();
        foreach ($posts as $post) {
            $post->getPoster()->decrementPostCount();
            $forum->decrementPostCount();
        }
        // decrement topicCount
        $forum->decrementTopicCount();
        // update the db
        $this->entityManager->flush();
        // remove all posts in topic
        $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->manualDeletePosts($topic->getTopic_id());
        // remove topic subscriptions
        $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->deleteTopicSubscriptions($topic->getTopic_id());
        // delete the topic (manual dql to avoid cascading deletion errors)
        $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->manualDelete($topic->getTopic_id());
        // sync the forum up with the changes

        $this->synchronizationHelper->forum($forum, false);
        $this->synchronizationHelper->forumLastPost($forum, true);

//        ModUtil::apiFunc($this->name, 'sync', 'forum', ['forum' => $forum, 'flush' => false]);
//        ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', ['forum' => $forum, 'flush' => true]);

        return $forum->getForum_id();
    }

    /**
     * Move topic.
     *
     * This function moves a given topic to another forum
     *
     * @param $topic_id int the topics id
     * @param $forum_id int the destination forums id
     * @param $createshadowtopic   boolean true = create shadow topic
     * @param $topic TopicEntity
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return void
     */
    public function move($topic_id, $forum_id, $createshadowtopic, $topic = false)
    {
        if (!isset($topic) || !$topic instanceof TopicEntity) {
            if (!isset($topic_id)) {
                throw new \InvalidArgumentException();
            }
            $topic = $this->entityManager->find('Zikula\DizkusModule\Entity\TopicEntity', $topic_id);
        }
        $managedTopic = $this->getManager(null, $topic);
        if ($managedTopic->getForumId() != $forum_id) {
            // set new forum
            $oldForumId = $managedTopic->getForumId();
            $forum = $this->entityManager->getReference('Zikula\DizkusModule\Entity\ForumEntity', $forum_id);
            $managedTopic->get()->setForum($forum);
            if ($createshadowtopic == true) {
                // create shadow topic
                $managedShadowTopic = $this->getManager();
                $newUrl = $this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()]);
                $topicData = [
                    'title'           => $this->translator->__f('*** The original posting \'%s\' has been moved', $managedTopic->getTitle()),
                    'message'         => $this->translator->__('The original posting has been moved').' <a title="'.$this->translator->__('moved').'" href="'.$newUrl.'">'.$this->translator->__('here').'</a>.',
                    'forum_id'        => $oldForumId,
                    'topic_time'      => $managedTopic->get()->getTopic_time(),
                    'attachSignature' => false,
                    'subscribe_topic' => false, ];
                $managedShadowTopic->prepare($topicData);
                $managedShadowTopic->lock();
                $this->entityManager->persist($managedShadowTopic->get());
            }

            // re-sync all forum counts and last posts
            $previousForumLocation = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $oldForumId);
            // we need to set it to null here so it can be easly synchronized later forumLastPost id is unique
            $previousForumLocation->setLast_post();

            $this->entityManager->flush();

            $this->synchronizationHelper->forumLastPost($previousForumLocation, false);

            $this->synchronizationHelper->forumLastPost($forum, false);

            $this->synchronizationHelper->forum($oldForumId, false);

            $this->synchronizationHelper->forum($forum, true);

            // @todo sync helper
//            ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', [
//                'forum' => $previousForumLocation,
//                'flush' => false]);
//            ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', [
//                'forum' => $forum,
//                'flush' => false]);
//            ModUtil::apiFunc($this->name, 'sync', 'forum', [
//                'forum' => $oldForumId,
//                'flush' => false]);
//            ModUtil::apiFunc($this->name, 'sync', 'forum', [
//                'forum' => $forum,
//                'flush' => true]);
        }
    }

    /**
     * split the topic at the provided post.
     *
     * @param PostManager $managedPost
     * @param string      $newsubject
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int id of the new topic
     */
    public function split($managedPost, $newsubject)
    {
        if (!isset($managedPost) || !$managedPost instanceof PostManager || !isset($newsubject)) {
            throw new \InvalidArgumentException();
        }
        $managedTopic = $this->getManager(null, $managedPost->get()->getTopic()); //new TopicManager(null, $managedPost->get()->getTopic());
        // create new topic
        $newTopic = new TopicEntity();
        $newTopic->setPoster($managedPost->get()->getPoster());
        $newTopic->setTitle($newsubject);
        $newTopic->setForum($managedTopic->get()->getForum());
        $managedPost->get()->setIsFirstPost(true);
        $managedPost->get()->setTitle($newsubject);
        $this->entityManager->persist($newTopic);
        $this->entityManager->flush();
        // update posts
        $dql = 'SELECT p from Zikula\DizkusModule\Entity\PostEntity p
            WHERE p.topic = :topic
            AND p.post_id >= :post
            ORDER BY p.post_id';
        $query = $this->entityManager->createQuery($dql)->setParameter('topic', $managedTopic->get())->setParameter('post', $managedPost->get()->getPost_id());
        /* @var $posts Array of Zikula\Module\DizkusModule\Entity\PostEntity */
        $posts = $query->getResult();
        // update the topic_id in the postings
        foreach ($posts as $post) {
            $post->setTopic($newTopic);
        }
        // must flush here so sync gets correct information
        $this->entityManager->flush();
        // last iteration of `$post` used below
        // update old topic
        $this->synchronizationHelper->topicLastPost($managedTopic->get(), true);
        //ModUtil::apiFunc($this->name, 'sync', 'topicLastPost', ['topic' => $managedTopic->get(), 'flush' => true]);
        $oldReplyCount = $managedTopic->get()->getReplyCount();
        $managedTopic->get()->setReplyCount($oldReplyCount - count($posts));
        // update new topic with post data
        $newTopic->setLast_post($post);
        $newTopic->setReplyCount(count($posts) - 1);
        $newTopic->setTopic_time($post->getPost_time());
        // resync topic totals, etc
        $this->synchronizationHelper->forum($newTopic->getForum(), false);
        //ModUtil::apiFunc($this->name, 'sync', 'forum', ['forum' => $newTopic->getForum(), 'flush' => false]);
        $this->entityManager->flush();

        return $newTopic->getTopic_id();
    }

    /**
     * joins two topics together.
     *
     * @param $from_topic_id int this topic get integrated into to_topic (origin)
     * @param $to_topic_id int the target topic that will contain the post from from_topic (destination)
     * @param $topic TopicEntity The (origin) topic as object
     *              must have *either* topic or from_topic_id
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int Destination topic ID
     */
    public function join($from_topic_id, $to_topic_id, $topic)
    {
        if (!$topic instanceof TopicEntity && !isset($from_topic_id)) {
            $this->request->getSession()->getFlashBag()->add('error', $this->translator->__f('Either "%1$s" or "%2$s" must be set.', ['topic', 'from_topic_id']));

            throw new \InvalidArgumentException();
        }
        if (!isset($to_topic_id)) {
            throw new \InvalidArgumentException();
        }
        if (isset($topic) && isset($from_topic_id)) {
            // unset the id and use the Object
            $from_topic_id = null;
        }
        $managedOriginTopic = $this->getManager($from_topic_id, $topic); //new TopicManager($from_topic_id, $topic);
        // one param will be null
        $managedDestinationTopic = $this->getManager($to_topic_id); //new TopicManager($to_topic_id);
        if ($managedDestinationTopic->get() === null) {
            // can't use isset() and ->get() at the same time
            $this->request->getSession()->getFlashBag()->add('error', $this->translator->__('Destination topic does not exist.'));

            throw new \InvalidArgumentException();
        }
        // move posts from Origin to Destination topic
        $posts = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\PostEntity')->findBy(['topic' => $managedOriginTopic->get()]);
        $previousPostTime = $managedDestinationTopic->get()->getLast_post()->getPost_time();
        foreach ($posts as $post) {
            $post->setTopic($managedDestinationTopic->get());
            if ($post->getPost_time() <= $previousPostTime) {
                $post->setPost_time($previousPostTime->modify('+1 minute'));
            }
            $previousPostTime = $post->getPost_time();
        }
        $this->entityManager->flush();
        // remove the originTopic from the DB (manual dql to avoid cascading deletion errors)
        $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->manualDelete($managedOriginTopic->getId());
        $managedDestinationTopic->setLastPost($post);
        $managedDestinationTopic->get()->setTopic_time($previousPostTime);
        // resync destination topic and all forums

        $this->synchronizationHelper->topic($managedDestinationTopic->get(), true);
        $this->synchronizationHelper->forum($managedOriginTopic->get()->getForum(), false);
        $this->synchronizationHelper->forumLastPost($managedOriginTopic->get()->getForum(), true);
        $this->synchronizationHelper->forum($managedDestinationTopic->get()->getForum(), false);
        $this->synchronizationHelper->forumLastPost($managedDestinationTopic->get()->getForum(), true);

//        ModUtil::apiFunc($this->name, 'sync', 'topic', ['topic' => $managedDestinationTopic->get(), 'flush' => true]);
//        ModUtil::apiFunc($this->name, 'sync', 'forum', ['forum' => $managedOriginTopic->get()->getForum(), 'flush' => false]);
//        ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', ['forum' => $managedOriginTopic->get()->getForum(), 'flush' => true]);
//        ModUtil::apiFunc($this->name, 'sync', 'forum', ['forum' => $managedDestinationTopic->get()->getForum(), 'flush' => false]);
//        ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', ['forum' => $managedDestinationTopic->get()->getForum(), 'flush' => true]);

        return $managedDestinationTopic->getId();
    }
}
