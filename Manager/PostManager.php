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

namespace Dizkus\Manager;

use ServiceUtil;
use Dizkus_Manager_Topic;
use Dizkus_Entity_Post;
use DataUtil;
use Zikula_Exception_Fatal;
use UserUtil;
use Dizkus_Entity_ForumUser;
use Dizkus_Manager_Forum;
use ModUtil;

class PostManager
{

    /**
     * managed post
     * @var Dizkus_Entity_Post
     */
    private $_post;

    /**
     * Post topic
     * @var Dizkus_Manager_Topic
     */
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
            $this->_topic = new Dizkus_Manager_Topic(null, $this->_post->getTopic());
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
        return $this->_post->getPost_id();
    }

    public function getTopicId()
    {
        return $this->_topic->getId();
    }

    /**
     * get the Post entity
     *
     * @return Dizkus_Entity_Post
     */
    public function get()
    {
        return $this->_post;
    }

    public function prepare($data)
    {
        $this->_post->merge(DataUtil::formatForStore($data));
    }

    /**
     * update the post
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
     * create a post from provided data
     *
     * @return boolean
     */
    public function create($data = null)
    {
        if (!is_null($data)) {
            $this->_topic = new Dizkus_Manager_Topic($data['topic_id']);
            $this->_post->setTopic($this->_topic->get());
            unset($data['topic_id']);
            $this->prepare($data);
        } else {
            throw new Zikula_Exception_Fatal('Cannot create Post, no data provided.');
        }
        // increment poster posts
        $uid = UserUtil::getVar('uid');
        $forumUser = $this->entityManager->find('Dizkus_Entity_ForumUser', $uid);
        if (!$forumUser) {
            $forumUser = new Dizkus_Entity_ForumUser();
            $coreUser = $this->entityManager->find('Zikula\\Module\\UsersModule\\Entity\\UserEntity', $uid);
            $forumUser->setUser($coreUser);
        }
        $forumUser->incrementPostCount();
        // increment topic posts
        $this->_topic->setLastPost($this->_post);
        $this->_topic->incrementRepliesCount();
        // update topic time to last post time
        $this->_topic->get()->setTopic_time($this->_post->getPost_time());
        // increment forum posts
        $managedForum = new Dizkus_Manager_Forum(null, $this->_topic->get()->getForum());
        $managedForum->incrementPostCount();
        $managedForum->setLastPost($this->_post);
        $this->_post->setPoster($forumUser);
        $this->entityManager->persist($this->_post);
        $this->entityManager->flush();
    }

    /**
     * delete a post
     *
     * @return boolean
     */
    public function delete()
    {
        // preserve post_id
        $id = $this->_post->getPost_id();
        $topicLastPostId = $this->_topic->get()->getLast_post()->getPost_id();
        $managedForum = new Dizkus_Manager_Forum($this->_topic->getForumId());
        $forumLastPostId = $managedForum->get()->getLast_post()->getPost_id();
        // decrement user posts
        $this->_post->getPoster()->decrementPostCount();
        // remove the post
        $this->entityManager->getRepository('Dizkus_Entity_Post')->manualDelete($id);
        // decrement forum post count
        $managedForum->decrementPostCount();
        // decrement replies count
        $this->_topic->decrementRepliesCount();
        $this->entityManager->flush();
        // resetLastPost in topic and forum if required
        if ($id == $topicLastPostId) {
            $this->_topic->resetLastPost(true);
        }
        if ($id == $forumLastPostId) {
            ModUtil::apiFunc('Dizkus', 'sync', 'forumLastPost', array('forum' => $managedForum->get(), 'flush' => true));
        }
    }

}
