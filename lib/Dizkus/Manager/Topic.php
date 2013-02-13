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
use Doctrine\ORM\Tools\Pagination\Paginator;

class Dizkus_Manager_Topic
{

    /**
     * managed topic
     * @var Dizkus_Entity_Topic
     */
    private $_topic;
    private $_itemsPerPage;
    private $_numberOfItems;
    /**
     * first post in topic
     * @var Dizkus_Entity_Post
     */
    private $_firstPost = null;
    private $_subscribe = false;
    private $_forumId;
    protected $entityManager;
    protected $name;

    /**
     * construct
     */
    public function __construct($id = null, Dizkus_Entity_Topic $topic = null)
    {
        $this->entityManager = ServiceUtil::getService('doctrine.entitymanager');
        $this->name = 'Dizkus';

        if (isset($topic)) {
            // topic has been injected
            $this->_topic = $topic;
        } elseif ($id > 0) {
            // find existing topic
            $this->_topic = $this->entityManager->find('Dizkus_Entity_Topic', $id);
        } else {
            // create new topic
            $this->_topic = new Dizkus_Entity_Topic();
        }
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
        return $this->_topic->getTopic_title();
    }

    /**
     * return topic as doctrine2 object
     *
     * @return object
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
    
    public function getFirstPost()
    {
        return $this->_firstPost;
    }

    public function getPermissions()
    {
        return ModUtil::apiFunc($this->name, 'Permission', 'get', $this->_topic);
    }

    /**
     * return posts of a topic as doctrine2 object
     *
     * @return object
     */
    public function getPosts($startNumber = 1)
    {
        // Feb 1, 2013 - the posts are part of the $_topic var - could we pull them from there?

        $this->_itemsPerPage = ModUtil::getVar($this->name, 'posts_per_page');

        $id = $this->_topic->getTopic_id();

        $query = $this->entityManager
                ->createQueryBuilder()
                ->select('p, u, r')
                ->from('Dizkus_Entity_Post', 'p')
                ->where('p.topic = :topicId')
                ->setParameter('topicId', $id)
                ->leftJoin('p.poster', 'u')
                ->leftJoin('u.user_rank', 'r')
                ->orderBy('p.post_time', 'ASC') // should be set by user preference item @see Poster
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
            'numitems' => $this->_numberOfItems
        );
    }

    /**
     * get forum bread crumbs
     *
     * @return string
     */
    public function getBreadcrumbs()
    {
        $i = $this->entityManager->find('Dizkus_Entity_Forum', $this->getForumId());

        $output = array();
        while ($i->getLvl() != 0) {
            $url = ModUtil::url($this->name, 'user', 'viewforum', array('forum' => $i->getForum_id()));
            $output[] = array(
                'url' => $url,
                'title' => $i->getForum_name()
            );
            $i = $i->getParent();
        }
        // root
        $url = ModUtil::url($this->name, 'user', 'main', array('viewcat' => $i->getForum_id()));
        $output[] = array(
            'url' => $url,
            'title' => $i->getForum_name()
        );
        return array_reverse($output);
    }

    /**
     * add to views count
     */
    public function incrementViewsCount()
    {
        $this->_topic->incrementTopic_views();
        $this->entityManager->flush();
    }

    public function setLastPost(Dizkus_Entity_Post $lastPost)
    {
        $this->_topic->setLast_post($lastPost);
    }

    public function setTitle($title)
    {
        $this->_topic->setTopic_title($title);
        $this->entityManager->flush();
    }

    /**
     * add to replies count
     */
    public function incrementRepliesCount()
    {
        $this->_topic->incrementTopic_replies();
        $this->entityManager->flush();
    }

    /**
     * subtract from replies count
     */
    public function decrementRepliesCount()
    {
        $this->_topic->decrementTopic_replies();
        $this->entityManager->flush();
    }

    /**
     * 
     * @param type $data['forum_id']
     * @param type $data['message']
     * @param type $data['post_attach_signature']
     * @param type $data['topic_title']
     * @param type $data['subscribe_topic']
     */
    public function prepare($data)
    {
        // prepare first post
        $this->_firstPost = new Dizkus_Entity_Post();
        $this->_firstPost->setForum_id($data['forum_id']);
        $this->_firstPost->setPost_text($data['message']);
        unset($data['message']);
        $this->_firstPost->setPost_attach_signature($data['post_attach_signature']);
        unset($data['post_attach_signature']);
        $this->_firstPost->setPost_title($data['topic_title']);
        $this->_firstPost->setTopic($this->_topic);
        $this->_subscribe = $data['subscribe_topic'];
        unset($data['subscribe_topic']);
        $this->_forumId = $data['forum_id'];
        $managedForum = new Dizkus_Manager_Forum($this->_forumId);
        $this->_topic->setForum($managedForum->get());
        unset($data['forum_id']);

        $this->_topic->setLast_post($this->_firstPost);

        $this->_topic->merge($data);

        // prepare poster data
        $uid = UserUtil::getVar('uid');
        $forumUser = $this->entityManager->find('Dizkus_Entity_ForumUser', $uid);
        if (!$forumUser) {
            $forumUser = new Dizkus_Entity_ForumUser();
            $coreUser = $this->entityManager->find('Users\Entity\UserEntity', $uid);
            $forumUser->setUser($coreUser);
        }
        $forumUser->incrementUser_posts();
        $this->_firstPost->setPoster($forumUser);
    }

    public function getPreview()
    {
        return $this->_firstPost;
    }

    /**
     * persist the topic
     *
     * @return boolean
     */
    public function store()
    {
        // write topic & first post
        $this->entityManager->persist($this->_topic);
        $this->entityManager->persist($this->_firstPost);
        $this->entityManager->flush();

        // increment forum post count
        $managedForum = new Dizkus_Manager_Forum($this->getForumId());
        $managedForum->incrementPostCount();
        $managedForum->incrementTopicCount();
        $managedForum->setLastPost($this->_firstPost);

        // subscribe
        if ($this->_subscribe) {
            $params = array(
                'topic_id' => $this->_topic->getTopic_id(),
                'action' => 'subscribe'
            );
            ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);
        }

        return $this->_topic->getTopic_id();
    }

    /**
     * create topic and post
     *
     * @return integer topic id
     */
    public function create()
    {
        // add first post to topic
        $this->_firstPost->settopic($this->_topic);

        $managedForum = new Dizkus_Manager_Forum($this->_forumId);

        // add topic to forum
        $this->_topic->setForum($managedForum->get());

        // write topic
        $this->entityManager->persist($this->_topic);
        $this->entityManager->persist($this->_firstPost);

        // increment forum post count
        $managedForum->incrementPostCount();
        $managedForum->incrementTopicCount();
        $managedForum->setLastPost($this->_firstPost);

        $this->entityManager->flush();

        // subscribe
        if ($this->_subscribe) {
            $params = array(
                'topic_id' => $this->_topic->getTopic_id(),
                'action' => 'subscribe'
            );
            ModUtil::apiFunc($this->name, 'topic', 'changeStatus', $params);
        }

        return $this->_topic->getTopic_id();
    }

    /**
     * remove topic
     *
     * @return boolean
     */
    public function remove()
    {
        $this->entityManager->remove($this->_topic);
        $this->entityManager->flush();
        return true;
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
     * @return boolean
     */
    public function solve()
    {
        $this->_topic->setSolved(true);
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
        $this->_topic->setSolved(false);
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

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(s)')
                ->from('Dizkus_Entity_TopicSubscription', 's')
                ->where('s.user_id = :user')
                ->setParameter('user', UserUtil::getVar('uid'))
                ->andWhere('s.topic = :topic')
                ->setParameter('topic', $this->_topic)
                ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();
        return $count > 0 ? true : false;
    }
    
    /**
     * find last post by post_time and set
     */
    public function resetLastPost($flush = false)
    {
        $dql = "SELECT p FROM Dizkus_Entity_Post p
            WHERE p.topic = :topic
            ORDER BY p.post_time DESC";
        
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

    public function getPostCount()
    {
        $dql = "SELECT COUNT(p) FROM Dizkus_Entity_Post p
            WHERE p.topic = :topic";
        return $this->entityManager->createQuery($dql)
                ->setParameter('topic', $this->_topic)
                ->getSingleScalarResult();
    }
}