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

class Dizkus_EntityAccess_Topic
{

    private $_topic;
    private $_itemsPerPage;
    private $_numberOfItems;
    private $_firstPost;
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
                ->getQuery();

        $query->setFirstResult($startNumber)->setMaxResults($this->_itemsPerPage);
        $paginator = new Paginator($query);
        $this->_numberOfItems = count($paginator);

        return $paginator;
    }

    /**
     * return page as array
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
     * return page as array
     */
    public function incrementViewsCount()
    {
        $this->_topic->incrementTopic_views();
        $this->entityManager->flush();
    }

    public function setLastPost($lastPost)
    {
        $this->_topic->setLast_post($lastPost);
    }

    public function setTitle($title)
    {
        $this->_topic->setTopic_title($title);
        $this->entityManager->flush();
    }

    /**
     * return page as array
     */
    public function incrementRepliesCount()
    {
        $this->_topic->incrementTopic_replies();
        $this->entityManager->flush();
    }

    /**
     * return page as array
     */
    public function decrementRepliesCount()
    {
        $this->_topic->decrementTopic_replies();
        $this->entityManager->flush();
    }

    public function prepare($data)
    {
        // prepare first post
        $this->_firstPost = new Dizkus_Entity_Post();
        $this->_firstPost->setforum_id($data['forum_id']);
        $this->_firstPost->setpost_text($data['message']);
        unset($data['message']);
        $this->_firstPost->setpost_attach_signature($data['post_attach_signature']);
        unset($data['post_attach_signature']);
        $this->_firstPost->setpost_title($data['topic_title']);

        $this->_subscribe = $data['subscribe_topic'];
        unset($data['subscribe_topic']);


        $this->_forumId = $data['forum_id'];
        unset($data['forum_id']);

        $this->_topic->setLast_post($this->_firstPost);

        $this->_topic->merge($data);

        // prepare poster data
        $uid = UserUtil::getVar('uid');
        $poster = $this->entityManager->find('Dizkus_Entity_Poster', $uid);
        if (!$poster) {
            $poster = new Dizkus_Entity_Poster();
            $poster->setuser_id($uid);
        }
        $poster->incrementUser_posts();
        $this->_firstPost->setposter($poster);
    }

    public function getPreview()
    {
        return $this->_firstPost;
    }

    /**
     * return page as array
     *
     * @return boolean
     */
    public function store()
    {
        // write topic
        $this->entityManager->persist($this->_topic);
        $this->entityManager->flush();

        // write first post
        $this->_firstPost->settopic_id($this->_topic->getTopic_id());
        $this->entityManager->persist($this->_firstPost);
        $this->entityManager->flush();

        // icrement forum post count
        $forum = new Dizkus_EntityAccess_Forum($this->getForumId());
        $forum->incrementPostCount();
        $forum->incrementTopicCount();
        $forum->setLastPost($this->_firstPost);

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
     * return page as array
     *
     * @return boolean
     */
    public function create()
    {
        // add first post to topic
        $this->_firstPost->settopic($this->_topic);


        $forum = new Dizkus_EntityAccess_Forum($this->_forumId);


        // add topic to forum
        $this->_topic->setForum($forum->get());

        // write topic
        $this->entityManager->persist($this->_topic);
        $this->entityManager->persist($this->_firstPost);


        // icrement forum post count
        $forum->incrementPostCount();
        $forum->incrementTopicCount();
        $forum->setLastPost($this->_firstPost);


        $this->entityManager->flush();


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
                ->from('Dizkus_Entity_TopicSubscriptions', 's')
                ->where('s.user_id = :user')
                ->setParameter('user', UserUtil::getVar('uid'))
                ->andWhere('s.topic_id = :topic')
                ->setParameter('topic', $this->_topic->getTopic_id())
                ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();
        return $count > 0 ? true : false;
    }

}