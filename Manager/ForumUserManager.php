<?php

/**
 * Copyright Dizkus Team 2012.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\DizkusModule\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\Entity\ForumUserEntity;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Entity\TopicEntity;
use Zikula\DizkusModule\Helper\RankHelper;
use Zikula\DizkusModule\Security\Permission;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * Forum User manager
 */
class ForumUserManager
{
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RankHelper
     */
    private $ranksHelper;

    /**
     * Managed forum user
     *
     * @var ForumUserEntity
     */
    private $_forumUser;

    private $loggedIn = false;

    private $lastVisit;

    protected $name;

    /**
     * Construct the manager
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     * @param EntityManager $entityManager
     * @param CurrentUserApi $userApi
     * @param Permission $permission
     * @param VariableApi $variableApi
     * @param RankHelper $ranksHelper
     */
    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        RequestStack $requestStack,
        EntityManager $entityManager,
        CurrentUserApi $userApi,
        Permission $permission,
        VariableApi $variableApi,
        RankHelper $ranksHelper
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
        $this->ranksHelper = $ranksHelper;
    }

    /**
     * Get manager
     *
     * @param int  $uid    user id (optional: defaults to current user)
     * @param bool $create create the ForumUser if does not exist (default: true)
     */
    public function getManager($uid = null, $create = true)
    {
        if (!empty($uid)) {
            $this->_forumUser = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumUserEntity', $uid);
        } elseif (empty($uid)) {
            $uid = $this->userApi->isLoggedIn() ? $this->request->getSession()->get('uid') : 1;
            $this->_forumUser = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumUserEntity', $uid);
        } else {
            $this->_forumUser = null;
        }
        if ($this->exists()) {
            $this->loggedIn = true;
        } elseif ($create) {
            //$this->variableApi->get($this->name, 'defaultPoster', 2); ???
             // zikula guest account
            $zuser = $this->entityManager->find('Zikula\UsersModule\Entity\UserEntity', $uid);
            if ($zuser) {
                $this->_forumUser = new ForumUserEntity();
                $this->_forumUser->setUser($zuser);
                $this->entityManager->persist($this->_forumUser);
                $this->entityManager->flush();
                $this->loggedIn = true;
            }
        }
        // $this
        return $this->checkLastVisit();
    }

    /**
     * Check if user exists
     *
     * @return bool
     */
    public function getManagedByUserName($uname, $create = false)
    {
        $zuser = $this->entityManager->getRepository('Zikula\UsersModule\Entity\UserEntity')->findOneBy(['uname' => $uname]);
        if ($zuser) {
            return $this->getManager($zuser->getUid(), $create);
        }

        return $this;
    }

    /**
     * Check if user exists
     *
     * @return bool
     */
    public function exists()
    {
        return $this->_forumUser instanceof ForumUserEntity ? true : false;
    }

    /**
     * Return forum user as doctrine2 object
     *
     * @return ForumUserEntity
     */
    public function get()
    {
        return $this->_forumUser;
    }

    /**
     * Get user id
     *
     * @return ForumUserEntity
     */
    public function getId()
    {
        return $this->exists() ? $this->_forumUser->getUserId() : false;
    }

    /**
     * Create user from provided data but do not yet persist
     *
     * @todo Add create validation
     * @todo event
     *
     * @return bool
     */
    public function create($data = null)
    {
        if (!is_null($data)) {
            $this->_forumUser = new ForumUserEntity();
        } else {
            // throw new \InvalidArgumentException($this->translator->__('Cannot create Post, no data provided.'));
            $this->_forumUser = new ForumUserEntity();
        }

        return $this;
    }

    /**
     * Update
     *
     * @param array $data forum user data
     */
    public function update($data)
    {
        if ($data instanceof ForumUserEntity) {
            $this->_forumUser = $data;
        }

        $this->_forumUser->merge($data);

        return $this;
    }

    /**
     * Persist and flush
     *
     * @param array $data forum user data
     */
    public function store()
    {
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Delete user
     *
     * @param array $data forum user data
     */
    public function delete()
    {
        return $this;
    }

    /**
     * Return forum user as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_forumUser->toArray();
    }

    /**
     * Return zikula user as doctrine2 object
     *
     * @return ForumUserEntity
     */
    public function getUser()
    {
        return $this->_forumUser->getUser();
    }

    /**
     * Return username
     *
     * @return string
     */
    public function getUserName()
    {
        return ($this->exists() && !$this->isAnonymous()) ? $this->_forumUser->getUser()->getUname() : 'Anonymous';
    }

    /**
     * Return forum user logged in status
     *
     * @return ForumUserEntity
     */
    public function isLoggedIn()
    {
        return ($this->loggedIn && $this->getId() > 1) ? true : false;
    }

    /**
     * Return forum user online in status
     *
     * @todo remove this duplicate
     * @return ForumUserEntity
     */
    public function isOnline()
    {
        return ($this->loggedIn && $this->getId() > 1) ? true : false;
    }

    /**
     * check to remove... or rename to isCurrent()
     *
     * @return string
     */
    public function isMe($user)
    {
        return $this->_forumUser->getUserId() == $user->getUserId() ? true : false;
    }

    /**
     * Return forum user logged in status.
     *
     * @return ForumUserEntity
     */
    public function isAnonymous()
    {
        return ($this->loggedIn && $this->getId() == 1) ? true : false;
    }

    /**
     * Return current user page
     *
     * @deprecated to remove
     *
     * @return ForumUserEntity
     */
    public function getCurrentPosition()
    {
        return $this->request->attributes->get('_route');
    }

    /**
     * Is user allowed to comment check
     *
     * @param object Object to chceck comment permissions for
     *
     * @return bool
     */
    public function allowedToComment($object)
    {
        if ($object instanceof PostManager) {
            return $this->permission->canWrite($object->getManagedTopic()->getForum());
        }

        if ($object instanceof TopicManager) {
            return $this->permission->canWrite($object->get()->getForum());
        }

        if ($object instanceof ForumManager) {
            return $this->permission->canWrite($object->get());
        }

        if ($object instanceof self) {
            return true;
        }

        return false;
    }

    /**
     * Is user allowed to edit check
     *
     * @param object Object to chceck edit permissions for
     *
     * @return bool
     */
    public function allowedToEdit($object)
    {
        if ($object instanceof PostManager) {
            if ($object->getManagedPoster()->getId() == $this->getId()) {
                return true;
            }

            return false;
        }

        if ($object instanceof TopicManager) {
            return $object->getManagedPoster()->getId() == $this->getId() ? true : false;
        }

        if ($object instanceof ForumManager) {
            return false;
        }

        if ($object instanceof self) {
            if ($object->getId() == $this->getId()) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Is user allowed to moderate check
     *
     * @param object Object to chceck moderate permissions for
     *
     * @return bool
     */
    public function allowedToModerate($object)
    {
        if ($object instanceof PostManager) {
            return $this->permission->canModerate($object->getManagedTopic()->getForum());
        }

        if ($object instanceof TopicManager) {
            return $this->permission->canModerate($object->get()->getForum());
        }

        if ($object instanceof ForumManager) {
            //is node or forum moderator
            $nodeModeratorUsersCollection = $object->get()->getNodeModeratorUsers();
            if ($nodeModeratorUsersCollection->indexOf($this->_forumUser)) {
                return true;
            }
            //belongs to group that is node or forum moderator
            $nodeModeratorGroups = $object->get()->getNodeModeratorGroups();
            if (!$nodeModeratorGroups->isEmpty()) {
                //dump($nodeModeratorGroups);
                $userGroups = $this->_forumUser->getUser()->getGroups();
                foreach ($nodeModeratorGroups as $group) {
                    return $userGroups->indexOf($group) === false ? false : true;
                }
            }
            // check zikula perms
            if ($this->permission->canModerate($object->get())) {
                return true;
            }

            return false;
        }

        if ($object instanceof self) {
            return $object->getId() == $this->getId();
        }

        return false;
    }

    /**
     * Increment user post count
     *
     * @return string
     */
    public function incrementPostCount()
    {
        $this->_forumUser->incrementPostCount();

        return $this;
    }

    /**
     * Get user post order setting
     *
     * @return string
     */
    public function getPostOrder()
    {
        return (!$this->isAnonymous() && $this->_forumUser->getPostOrder() == 1) ? 'ASC' : 'DESC';
    }

    /**
     * Set user post order setting
     *
     * @param string $sort asc|desc
     */
    public function setPostOrder($sort)
    {
        $this->_forumUser->setPostOrder($sort);

        return $this;
    }

    /**
     * Get user avatar
     *
     * @todo - add deleted user and anonymous avatar image
     *
     * @return string
     */
    public function getAvatar()
    {
        $userAttr = $this->_forumUser->getUser()->getAttributes();
        if ($userAttr->offsetExists('avatar')) {
            //@todo add anonymous avatar setting
            return $this->_forumUser->getUser()->getAttributeValue('avatar');
        } else {
            return 'web/modules/zikuladizkus/images/anonymous.png';
        }
    }

    /**
     * Get user signature
     *
     * @param string
     */
    public function getSignature()
    {
        return $this->_forumUser->getUser()->getAttributes()->offsetExists('signature') ? $this->_forumUser->getUser()->getAttributeValue('signature') : '';
    }

    /**
     * Set user signature
     *
     * @param string $signature
     */
    public function setSignature($signature)
    {
        $zuser = $this->_forumUser->getUser();
        $zuser->setAttribute('signature', $signature);
        $this->entityManager->persist($zuser);
        $this->entityManager->flush();
    }

    /**
     * Get user rank
     *
     * @param string $signature
     */
    public function getRank()
    {
        if ($this->_forumUser->getRank()) {
            return $this->_forumUser->getRank();
        }

        $ranks = $this->ranksHelper->getAll(['ranktype' => RankEntity::TYPE_POSTCOUNT]);
        $posterRank = $ranks[0];
        foreach ($ranks as $rank) {
            if (($this->_forumUser->getPostCount() >= $rank->getMinimumCount()) && ($this->_forumUser->getPostCount() <= $rank->getMaximumCount())) {
                $posterRank = $rank;
                //$posterRank->setImage($this->ranksHelper->getImageLink($posterRank->getImage()));
            }
        }

        return $posterRank;
    }

    /**
     * Set user rank
     *
     * @param string $rank
     */
    public function setRank($rank)
    {
        if (!$this->permission->canAdministrate()) {
            throw new AccessDeniedException();
        }

        $rank = $this->entityManager->getReference('Zikula\DizkusModule\Entity\RankEntity', $rank);

        if (isset($rank)) {
            $this->_forumUser->setRank($rank);
        } else {
            $this->_forumUser->clearRank();
        }

        return $this;
    }

    /**
     * Change the value of Autosubscribe setting
     *
     * @param bool $value
     */
    public function setAutosubscribe($value)
    {
        if (!$this->variableApi->get($this->name, 'topic_subscriptions_enabled')) {
            return $this;
        }

        $this->_forumUser->setAutosubscribe($value);

        return $this;
    }

    /**
     * Get forum subscriptions
     *
     * @return ArrayCollection
     */
    public function getForumSubscriptionsCollection()
    {
        return $this->_forumUser->getForumSubscriptions();
    }

    /**
     * Subscribe a forum
     *
     * @param obj $forum  The forum
     *
     * @return bool
     */
    public function subscribeForum(ForumEntity $forum)
    {
        if (!$this->variableApi->get($this->name, 'forum_subscriptions_enabled')) {
            return $this;
        }

        $forumSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumSubscriptionEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get()
        ]);

        if ($forumSubscription) {
            return true; // nothing to do
        }

        $this->_forumUser->addForumSubscription($forum);

        return $this;
    }

    /**
     * Unsubscribe a forum
     *
     * @param int $forum  The forum
     *
     * @return bool
     */
    public function unsubscribeForum($forum)
    {
        if (!$this->variableApi->get($this->name, 'forum_subscriptions_enabled')) {
            return $this;
        }

        $forumSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumSubscriptionEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get()
        ]);

        if (!$forumSubscription) {
            return $this;
        }

        $this->_forumUser->removeForumSubscription($forumSubscription);

        return $this;
    }

    /**
     * Is forum subscribed
     *
     * @param int $forum  The forum
     *
     * @return bool
     */
    public function isForumSubscribed($forum)
    {
        if (!$this->variableApi->get($this->name, 'forum_subscriptions_enabled')) {
            return $this;
        }

        $forumSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumSubscriptionEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get()
        ]);

        if ($forumSubscription) {
            return $forumSubscription;
        }

        return false;
    }

    /**
     * Get topic subscriptions
     *
     * @return ArrayCollection
     */
    public function getTopicSubscriptionsCollection()
    {
        if (!$this->variableApi->get($this->name, 'topic_subscriptions_enabled')) {
            return [];
        }

        return $this->_forumUser->getTopicSubscriptions();
    }

    /**
     * Subscribe a topic
     *
     * @param obj $topic  The topic
     *
     * @return bool
     */
    public function subscribeTopic(TopicEntity $topic = null)
    {
        if (!$this->variableApi->get($this->name, 'topic_subscriptions_enabled')) {
            return $this;
        }

        $topicSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')->findOneBy([
            'topic' => $topic,
            'forumUser' => $this->get()
        ]);

        if ($topicSubscription) {
            return $this;
        }

        $this->_forumUser->addTopicSubscription($topic);

        return $this;
    }

    /**
     * Unsubscribe a topic
     *
     * @param int $topic  The topic
     *
     * @return bool
     */
    public function unsubscribeFromTopic($topic)
    {
        if (!$this->variableApi->get($this->name, 'topic_subscriptions_enabled')) {
            return $this;
        }

        $topicSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')->findOneBy([
            'topic' => $topic,
            'forumUser' => $this->get()
        ]);

        if (!$topicSubscription) {
            return $this;
        }

        $this->_forumUser->removeTopicSubscription($topicSubscription);

        return $this;
    }

    /**
     * Is topic subscribed
     *
     * @param int $topic  The topic
     *
     * @return bool
     */
    public function isTopicSubscribed(TopicEntity $topic)
    {
        if (!$this->variableApi->get($this->name, 'topic_subscriptions_enabled')) {
            return false;
        }

        $topicSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')->findOneBy([
            'topic' => $topic,
            'forumUser' => $this->get()
        ]);

        if ($topicSubscription) {
            return true;
        }

        return false;
    }

    /**
     * Get forum view setting
     *
     * @param string $setting
     */
    public function getForumViewSettings()
    {
        if (!$this->variableApi->get($this->name, 'favorites_enabled')) {
            return false;
        }

        return $this->_forumUser->getDisplayOnlyFavorites();
    }

    /**
     * Set forum view setting
     *
     * @param string $setting
     */
    public function setForumViewSettings($setting)
    {
        if (!$this->variableApi->get($this->name, 'favorites_enabled')) {
            return $this;
        }

        if ($setting) {
            $this->_forumUser->showFavoritesOnly();
        } else {
            $this->_forumUser->showAllForums();
        }

        return $this;
    }

    /**
     * Add forum to favorites
     *
     * @param obj $page  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function getFavoriteForumsCollection()
    {
        if (!$this->variableApi->get($this->name, 'favorites_enabled')) {
            return [];
        }

        return $this->_forumUser->getFavoriteForums();
    }

    /**
     * Add forum to favorites
     *
     * @param obj $page  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function addFavoriteForum(ForumEntity $forum)
    {
        if (!$this->variableApi->get($this->name, 'favorites_enabled')) {
            return $this;
        }

        $forumIsFav = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumUserFavoriteEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get()
        ]);

        if ($forumIsFav) {
            return true;
        }

        $this->_forumUser->addFavoriteForum($forum);

        return $this;
    }

    /**
     * Add forum to favorites
     *
     * @param obj $page  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function removeFavoriteForum($forum)
    {
        if (!$this->variableApi->get($this->name, 'favorites_enabled')) {
            return $this;
        }

        $forumIsFav = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumUserFavoriteEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get()
        ]);

        if (!$forumIsFav) {
            return true;
        }

        $this->_forumUser->removeFavoriteForum($forumIsFav);

        return $this;
    }

    /**
     * Add forum to favorites
     *
     * @param obj $page  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function isForumFavorite($forum)
    {
        if (!$this->variableApi->get($this->name, 'favorites_enabled')) {
            return false;
        }

        $forumIsFav = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumUserFavoriteEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get()
        ]);

        if ($forumIsFav) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve all user topics
     *
     * @param integer $offset 'offset' pager offset (default=0)
     *
     * @return array
     */
    public function getTopics($offset = 0)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'l')
        ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
        ->leftJoin('t.last_post', 'l')
        ->leftJoin('t.posts', 'p')
        ->orderBy('l.post_time', 'DESC');

        $qb->where('t.poster = :uid');

        $qb->setParameter('uid', $this->getId());

        $limit = $this->variableApi->get('ZikulaDizkusModule', 'topics_per_page');
        $qb->setFirstResult($offset)
        ->setMaxResults($limit);
        $topics = new Paginator($qb);
        $pager = [
            'numitems' => $topics->count(),
            'itemsperpage' => $limit
        ];

        return [$topics, $pager];
    }

    /**
     * Retrieve all my posts
     *
     * @param $offset integer 'offset' pager offset (default=0)
     *
     * @return array
     */
    public function getPosts($offset = 0)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
        ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
        ->orderBy('p.post_time', 'DESC');
        $qb->where('p.poster = :uid');
        $qb->setParameter('uid', $this->getId());
        $limit = $this->variableApi->get('ZikulaDizkusModule', 'posts_per_page'); //$this->variableApi->get($perPageVar);
        $qb->setFirstResult($offset)
        ->setMaxResults($limit);
        $posts = new Paginator($qb);
        $pager = [
            'numitems' => $posts->count(),
            'itemsperpage' => $limit
        ];

        return [$posts, $pager];
    }

    /**
     * Check last visit
     * reads the cookie, updates it and returns the last visit date in unix timestamp
     *
     * @param none
     *
     * @return unix timestamp last visit date
     */
    public function checkLastVisit()
    {
        /**
         * set last visit cookies and get last visit time
         * set LastVisit cookie, which always gets the current time and lasts one year.
         */
        $time = time();
        $response = new Response();
        $cookie = new Cookie('DizkusLastVisit', $time, $time + 1800);
        $cookies = $this->request->cookies;
        if ($cookies->has('DizkusLastVisit')) {
            $this->lastVisit = $cookies->get('DizkusLastVisit');
            if ($this->lastVisit < $time - 1800) {
//                $response->headers->setCookie($cookie);
//                $response->sendHeaders();
                dump('expired');
            }
        } else {
            $response->headers->setCookie($cookie);
            $response->sendHeaders();
            $this->lastVisit = $time;
        }

        return $this;
    }

    /**
     * Get last visit
     *
     * @return unix timestamp last visit date
     */
    public function getLastVisit()
    {
        return $this->lastVisit;
    }

    /**
     * Get user activity based on same ip usage
     *
     * @param int $ip The posters IP
     *
     * @return array with information
     */
    public function getUserActivity($ip)
    {
        $viewip = [
            'poster_ip' => $ip,
            'poster_host' => ($ip != 'unrecorded') ? gethostbyaddr($ip) : $this->__('Host unknown'),
        ];
        $dql = 'SELECT p
            FROM Zikula\DizkusModule\Entity\PostEntity p
            WHERE p.poster_ip = :pip
            GROUP BY p.poster';
        $query = $this->entityManager->createQuery($dql)->setParameter('pip', $ip);
        $posts = $query->getResult();
        foreach ($posts as $post) {
            /* @var $post \Zikula\Module\DizkusModule\Entity\PostEntity */
            $coreUser = $post->getPoster()->getUser();
            $viewip['users'][] = [
                'uid' => $post->getPoster()->getUser_id(),
                'uname' => $coreUser['uname'],
                'postcount' => $post->getPoster()->getPostCount()
            ];
        }

        return $viewip;
    }
}
