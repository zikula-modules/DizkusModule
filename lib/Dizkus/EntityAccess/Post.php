<?php

/**
 * Copyright Pages Team 2012
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
use Doctrine\ORM\Tools\Pagination\Paginator;

class Dizkus_EntityAccess_Post
{

    private $_post;
    private $_topic;
    protected $entityManager;
    protected $name;

    /**
     * construct
     */
    public function __construct($id = null)
    {
        $this->entityManager = ServiceUtil::getService('doctrine.entitymanager');
        $this->name = 'Dizkus';

        if ($id > 0) {
            $this->_post = $this->entityManager->find('Dizkus_Entity_Post', $id);
            $this->_topic = new Dizkus_EntityAccess_Topic($this->_post->getTopic_id());
        } else {
            $this->_post = new Dizkus_Entity_Post();
        }
    }

    /**
     * return page as array
     *
     * @return mixed array or false
     */
    public function toArray($title = true)
    {
        if (!$this->_post) {
            return false;
        }
        $output = $this->_post->toArray();
        $output['topic_subject'] = $this->_topic->getTitle();
        $output = array_merge($output, $this->_topic->getPermissions());

        return $output;
    }

    public function getId()
    {
        return $this->_post->getPageId();
    }

    public function getTopicId()
    {
        return $this->_topic->getId();
    }

    public function isFirst()
    {
        return $this->_post->getpost_first();
    }

    /**
     * return topic as doctrine2 object
     *
     * @return object
     */
    public function get()
    {
        return $this->_post;
    }

    public function prepare($data)
    {
        $this->_post->merge($data);
    }

    /**
     * return page as array
     *
     * @return boolean
     */
    public function update($data = null)
    {
        if (!is_null($data)) {
            $this->prepare($data);
        }

        // update topic
        $this->entityManager->persist($this->_post);
        $this->entityManager->flush();
    }

    /**
     * return page as array
     *
     * @return boolean
     */
    public function create($data = null)
    {
        if (!is_null($data)) {
            $this->prepare($data);
        }

        // increment poster posts
        $uid = UserUtil::getVar('uid');
        $poster = $this->entityManager->find('Dizkus_Entity_Poster', $uid);
        if (!$poster) {
            $poster = new Dizkus_Entity_Poster();
            $poster->setuser_id($uid);
        }
        $poster->incrementUser_posts();

        // increment topic posts
        $this->_topic = new Dizkus_EntityAccess_Topic($data['topic_id']);
        $this->_topic->setLastPost($this->_post);
        $this->_topic->incrementRepliesCount();

        // increment forum posts
        $forum = new Dizkus_EntityAccess_Forum($this->_topic->getForumId());
        $forum->incrementPostCount();

        $this->_post->setposter($poster);
        $this->_post->setforum_id($this->_topic->getForumId());
        $this->entityManager->persist($this->_post);
        $this->entityManager->flush();
    }

    /**
     * return page as array
     *
     * @return boolean
     */
    public function delete()
    {
        $this->_post->getposter()->decrementUser_posts();
        $this->_topic->decrementRepliesCount();

        $forum = new Dizkus_EntityAccess_Forum($this->_topic->getForumId());
        $forum->decrementPostCount();

        $this->entityManager->remove($this->_post);
        $this->entityManager->flush();
    }

}