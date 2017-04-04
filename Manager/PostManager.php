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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\Entity\PostEntity;
use Zikula\DizkusModule\Helper\SynchronizationHelper;
use Zikula\DizkusModule\Security\Permission;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Api\CurrentUserApi;

class PostManager
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
     * @var VariableApi
     */
    private $forumManagerService;

    /**
     * @var VariableApi
     */
    private $topicManagerService;

    /**
     * @var synchronizationHelper
     */
    private $synchronizationHelper;

    /**
     * Managed post
     *
     * @var PostEntity
     */
    private $_post;

    /**
     * Post topic
     *
     * @var TopicManager
     */
    private $_topic;

    public function __construct(
    TranslatorInterface $translator, RouterInterface $router, RequestStack $requestStack, EntityManager $entityManager, CurrentUserApi $userApi, Permission $permission, VariableApi $variableApi, ForumUserManager $forumUserManagerService, ForumManager $forumManagerService, TopicManager $topicManagerService, SynchronizationHelper $synchronizationHelper
    )
    {
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
        $this->topicManagerService = $topicManagerService;
        $this->synchronizationHelper = $synchronizationHelper;
    }

    /**
     * Start managing
     *
     * @return PostManager
     */
    public function getManager($id = null, PostEntity $post = null)
    {
        if ($post instanceof PostEntity){
            $this->_post = $post;
            $this->_topic = $this->topicManagerService->getManager(null, $this->_post->getTopic());

            return $this;
        }

        if ($id > 0) {
            $this->_post = $this->entityManager->find('Zikula\DizkusModule\Entity\PostEntity', $id);
            if($this->exists()){
                $this->_topic = $this->topicManagerService->getManager(null, $this->_post->getTopic());
            }
        } else {
            $this->_post = new PostEntity();
        }

        return $this;
    }

    /**
     * Check if topic exists
     *
     * @return bool
     */
    public function exists()
    {
        return $this->_post ? true : false;
    }

    /**
     * Get the Post entity
     *
     * @return PostEntity
     */
    public function get()
    {
        return $this->_post;
    }

    /**
     * Get post as array
     *
     * @return mixed array or false
     */
    public function toArray()
    {
        if (!$this->_post) {
            return [];
        }

        $post = $this->_post->toArray();
        $post['topic_subject'] = $this->_topic->getTitle();

        return $post;
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
     * Get topic as managedObject
     *
     * @return TopicManager
     */
    public function getManagedTopic()
    {
        return $this->topicManagerService->getManager($this->_post->getTopicId());
    }

    /**
     * Get the Poster as managedObject
     *
     * @return ForumUserManager
     */
    public function getManagedPoster()
    {
        return $this->forumUserManagerService->getManager($this->_post->getPosterId());
    }

    /**
     * Set preview data to current post for preview
     *
     * @todo Add preview validation handling
     * @return true
     */
    public function getPreview($data)
    {
        $this->_post->setPost_text($data['message']);

        return true;
    }

    /**
     * Update post
     *
     * @todo event
     * @return bool
     */
    public function update($data = null)
    {
        if (!is_null($data)) {
            $this->_post->merge($data);
        }
        // update topic
        $this->entityManager->persist($this->_post);
        $this->entityManager->flush();
    }

    /**
     * Create a post from provided data but do not yet persist
     *
     * @todo Add create validation
     * @todo event
     *
     * @return bool
     */
    public function create($data = null)
    {
        if (!is_null($data)) {
            $this->_topic = $this->topicManagerService->getManager($data['topic_id']);
            $this->_post->setTopic($this->_topic->get());
            unset($data['topic_id']);
            $this->_post->merge($data);
        } else {
            throw new \InvalidArgumentException('Cannot create Post, no data provided.');
        }
        $managedForumUser = $this->forumUserManagerService->getManager();
        $this->_post->setPoster($managedForumUser->get());

        return true;
    }

    /**
     * Persist the post and update related entities to reflect new post
     *
     * @todo Add validation ?
     * @todo event
     *
     * @return ...
     */
    public function persist()
    {
        $this->_post->getPoster()->incrementPostCount();
        // increment topic posts
        $this->_topic->setLastPost($this->_post);
        $this->_topic->incrementRepliesCount();
        // update topic time to last post time
        $this->_topic->get()->setTopic_time($this->_post->getPost_time());
        // increment forum posts
        $managedForum = $this->forumManagerService->getManager(null, $this->_topic->get()->getForum());
        $managedForum->incrementPostCount();
        $managedForum->setLastPost($this->_post);
        $this->entityManager->persist($this->_post);
        $this->entityManager->flush();
    }

    /**
     * Delete post
     *
     * @todo event
     *
     * @return bool
     */
    public function delete()
    {
        // preserve post_id
        $id = $this->_post->getPost_id();
        $topicLastPostId = $this->_topic->get()->getLast_post()->getPost_id();
        $managedForum = $this->forumManagerService->getManager($this->_topic->getForumId());
        $forumLastPostId = $managedForum->get()->getLast_post()->getPost_id();
        // decrement user posts
        $this->_post->getPoster()->decrementPostCount();
        // remove the post
        $this->entityManager->getRepository('Zikula\DizkusModule\Entity\PostEntity')->manualDelete($id);
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
            $this->synchronizationHelper->forumLastPost($managedForum->get(), true);
        }
        return true;
    }

    /**
     * Move post
     *
     * @todo clean
     * @todo event
     *
     * @param $args['post_id']
     * @param $args['old_topic_id']
     * @param $args['to_topic_id']
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int count of posts in destination topic
     */
    public function move($args)
    {
        $old_topic_id = isset($args['old_topic_id']) ? $args['old_topic_id'] : null;
        $to_topic_id = isset($args['to_topic_id']) ? $args['to_topic_id'] : null;
        $post_id = isset($args['post_id']) ? $args['post_id'] : null;
        if (!isset($old_topic_id) || !isset($to_topic_id) || !isset($post_id)) {
            throw new \InvalidArgumentException();
        }
        $managedOriginTopic = $this->topicManagerService->getManager($old_topic_id);
        $managedDestinationTopic = $this->topicManagerService->getManager($to_topic_id);
        $managedPost = $this->getManager($post_id);
        $managedOriginTopic->get()->getPosts()->removeElement($managedPost->get());
        $managedPost->get()->setTopic($managedDestinationTopic->get());
        $managedDestinationTopic->get()->addPost($managedPost->get());
        $managedOriginTopic->decrementRepliesCount();
        $managedDestinationTopic->incrementRepliesCount();
        $managedPost->get()->updatePost_time();
        $this->entityManager->flush();

        $this->synchronizationHelper->topicLastPost($managedOriginTopic->get(), false);
        $this->synchronizationHelper->topicLastPost($managedDestinationTopic->get(), true);

        return $managedDestinationTopic->getPostCount();
    }

}
