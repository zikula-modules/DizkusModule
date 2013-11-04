<?php

/**
 * Copyright Dizkus Team 2012
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Dizkus
 * @link https://github.com/zikula-modules/Dizkus
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\DizkusModule\Manager;

use ServiceUtil;
use ModUtil;
use UserUtil;
use DataUtil;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Module\DizkusModule\Entity\PostEntity;
use Zikula\Module\DizkusModule\Entity\TopicEntity;
use Zikula\Module\DizkusModule\Manager\ForumUserManager;
use Zikula\Module\DizkusModule\Manager\ForumManager;
use Zikula\Module\DizkusModule\Entity\ForumUserEntity;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TopicManager
{

    /**
     * managed topic
     * @var TopicEntity
     */
    private $_topic;
    private $_itemsPerPage;
    private $_defaultPostSortOrder;
    private $_numberOfItems;

    private $managedForum;

    /**
     * first post in topic
     * @var PostEntity
     */
    private $_firstPost = null;
    private $_subscribe = false;
    private $_forumId;
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    protected $name;

    /**
     * construct
     */
    public function __construct($id = null, TopicEntity $topic = null)
    {
        $this->entityManager = ServiceUtil::get('doctrine.entitymanager');
        $this->name = 'ZikulaDizkusModule';
        if (isset($topic)) {
            // topic has been injected
            $this->_topic = $topic;
            $this->managedForum = new ForumManager(null, $this->_topic->getForum());
        } elseif ($id > 0) {
            // find existing topic
            $this->_topic = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\TopicEntity', $id);
            $this->managedForum = new ForumManager(null, $this->_topic->getForum());
        } else {
            // create new topic
            $this->_topic = new TopicEntity();
        }
        $this->_itemsPerPage = ModUtil::getVar($this->name, 'posts_per_page');
        $this->_defaultPostSortOrder = ModUtil::getVar($this->name, 'post_sort_order');
    }

    /**
     * Check if topic exists
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->_topic ? true : false;
    }

    /**
     * return page as array
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
     * return topic id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_topic->getTopic_id();
    }

    /**
     * return topic title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_topic->getTitle();
    }

    /**
     * return topic as doctrine2 object
     *
     * @return TopicEntity
     */
    public function get()
    {
        return $this->_topic;
    }

    /**
     * return topic forum id
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
        return ModUtil::apiFunc($this->name, 'Permission', 'get', $this->_topic->getForum());
    }

    /**
     * return posts of a topic as doctrine2 object
     *
     * @return object
     */
    public function getPosts($startNumber = 1)
    {
        if (UserUtil::isLoggedIn()) {
            $managedForumUser = new ForumUserManager();
            $postSortOrder = $managedForumUser->getPostOrder();
        } else {
            $postSortOrder = $this->_defaultPostSortOrder;
        }
        // do not allow negative first result
        $startNumber = $startNumber > 0 ? $startNumber : 0;
        // Do a new query in order to limit maxresults, firstresult, order, etc.
        $query = $this->entityManager->createQueryBuilder()
            ->select('p, u, r')
            ->from('Zikula\Module\DizkusModule\Entity\PostEntity', 'p')
            ->where('p.topic = :topicId')
            ->setParameter('topicId', $this->_topic->getTopic_id())
            ->leftJoin('p.poster', 'u')
            ->leftJoin('u.rank', 'r')
            ->orderBy('p.post_time', $postSortOrder)
            ->getQuery();
        $query->setFirstResult($startNumber)->setMaxResults($this->_itemsPerPage);
        $paginator = new Paginator($query);
        $this->_numberOfItems = count($paginator);

        return $paginator;
    }

    /**
     * return pager
     *
     * @return array
     */
    public function getPager()
    {
        return array(
            'itemsperpage' => $this->_itemsPerPage,
            'numitems' => $this->_numberOfItems);
    }

    /**
     * get forum bread crumbs
     *
     * @return string
     */
    public function getBreadcrumbs()
    {
        return $this->managedForum->getBreadcrumbs(false);
    }

    /**
     * add to views count
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
     * add to replies count
     */
    public function incrementRepliesCount()
    {
        $this->_topic->incrementReplyCount();
        $this->entityManager->flush();
    }

    /**
     * subtract from replies count
     */
    public function decrementRepliesCount()
    {
        $this->_topic->decrementReplyCount();
        $this->entityManager->flush();
    }

    /**
     *
     * @param integer $data['forum_id']
     * @param string $data['message']
     * @param boolean $data['attachSignature']
     * @param string $data['title']
     * @param boolean $data['subscribe_topic']
     */
    public function prepare($data)
    {
        // prepare first post
        $this->_firstPost = new PostEntity();
        $this->_firstPost->setPost_text($data['message']);
        unset($data['message']);
        $this->_firstPost->setAttachSignature($data['attachSignature']);
        unset($data['attachSignature']);
        $this->_firstPost->setTitle($data['title']);
        $this->_firstPost->setTopic($this->_topic);
        $this->_firstPost->setIsFirstPost(true);
        $this->_subscribe = $data['subscribe_topic'];
        unset($data['subscribe_topic']);
        $this->_forumId = $data['forum_id'];
        $this->managedForum = new ForumManager($this->_forumId);
        $this->_topic->setForum($this->managedForum->get());
        unset($data['forum_id']);
        $solveStatus = $data['solveStatus'] == 1 ? -1 : 0; // -1 = support request
        $this->_topic->setSolved($solveStatus);
        unset($data['solveStatus']);
        $this->_topic->setLast_post($this->_firstPost);
        $this->_topic->merge($data);
        // prepare poster data
        $uid = UserUtil::getVar('uid');
        $forumUser = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\ForumUserEntity', $uid);
        if (!$forumUser) {
            $forumUser = new ForumUserEntity($uid);
        }
        $forumUser->incrementPostCount();
        $this->_firstPost->setPoster($forumUser);
        $this->_topic->setPoster($forumUser);
    }

    /**
     * Add hook data to topic
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
     * create topic and post
     *
     * @return integer topic id
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
            $params = array(
                'topic_id' => $this->_topic->getTopic_id(),
                'action' => 'subscribe');
            ModUtil::apiFunc($this->name, 'topic', 'changeStatus', $params);
        }

        return $this->_topic->getTopic_id();
    }

    /**
     * set topic sticky
     *
     * @return boolean
     */
    public function sticky()
    {
        $this->_topic->sticky();
        $this->entityManager->flush();

        return true;
    }

    /**
     * set topic unsticky
     *
     * @return boolean
     */
    public function unsticky()
    {
        $this->_topic->unsticky();
        $this->entityManager->flush();

        return true;
    }

    /**
     * lock topic
     *
     * @return boolean
     */
    public function lock()
    {
        $this->_topic->lock();
        $this->entityManager->flush();

        return true;
    }

    /**
     * unlock topic
     *
     * @return boolean
     */
    public function unlock()
    {
        $this->_topic->unlock();
        $this->entityManager->flush();

        return true;
    }

    /**
     * set topic solved
     *
     * @param integer $postid
     * @return boolean
     */
    public function solve($postid)
    {
        $this->_topic->setSolved($postid);
        $this->entityManager->flush();

        return true;
    }

    /**
     * set topic unsolved
     *
     * @return boolean
     */
    public function unsolve()
    {
        $this->_topic->setSolved(-1);
        $this->entityManager->flush();

        return true;
    }

    /**
     * get if the current user is subscribed
     *
     * @return boolean
     */
    public function isSubscribed()
    {
        if (!UserUtil::isLoggedIn()) {
            return false;
        }
        $topicSubscription = $this->entityManager
            ->getRepository('Zikula\Module\DizkusModule\Entity\TopicSubscriptionEntity')
            ->findOneBy(array('topic' => $this->_topic, 'forumUser' => UserUtil::getVar('uid')));

        return isset($topicSubscription);
    }

    /**
     * find last post by post_time and set
     */
    public function resetLastPost($flush = false)
    {
        $dql = 'SELECT p FROM Zikula\Module\DizkusModule\Entity\PostEntity p
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
     * get the number of posts in this topic
     * @return integer
     */
    public function getPostCount()
    {
        return $this->_topic->getReplyCount();
    }

    /**
     * Get the next topic (by time) in the same Forum
     * @return integer
     */
    public function getNext()
    {
        return $this->getAdjacent('>', 'ASC');
    }

    /**
     * Get the previous topic (by time) in the same Forum
     * @return integer
     */
    public function getPrevious()
    {
        return $this->getAdjacent('<', 'DESC');
    }

    /**
     * Get the adjacent topic (by time) in the same Forum
     * @param $oper string less than or greater than operator < or >
     * @param $dir string Sort direction ASC/DESC
     * @return integer
     */
    private function getAdjacent($oper, $dir)
    {
        $dql = "SELECT t.topic_id FROM Zikula\Module\DizkusModule\Entity\TopicEntity t
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
            return '';
        }
    }

}
