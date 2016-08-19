<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Api;

use UserUtil;
use SecurityUtil;
use ModUtil;
use Zikula\DizkusModule\Entity\TopicEntity;
use Zikula\DizkusModule\Manager\TopicManager;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\PostManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This class provides the topic api functions
 */
class TopicApi extends \Zikula_AbstractApi
{

    /**
     * @param $args
     *  'topic'
     *  'action'
     *  'post'
     *  'title'
     *
     * @throws \InvalidArgumentException
     */
    public function changeStatus($args)
    {
        if (empty($args['topic'])) {
            throw new \InvalidArgumentException();
        }
        $managedTopic = new TopicManager($args['topic']);
        $perms = $managedTopic->getPermissions();
        switch ($args['action']) {
            case 'subscribe':
                if (UserUtil::isLoggedIn()) {
                    $this->subscribe(array('topic' => $managedTopic->get()));
                }
                break;
            case 'unsubscribe':
                if (UserUtil::isLoggedIn()) {
                    $this->unsubscribe(array('topic' => $managedTopic->get()));
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
                    $managedTopic->solve($args['post']);
                }
                break;
            case 'unsolve':
                if ($perms['edit'] || $managedTopic->get()->userAllowedToEdit()) {
                    $managedTopic->unsolve();
                }
                break;
            case 'setTitle':
                if ($perms['edit'] || $managedTopic->get()->userAllowedToEdit()) {
                    $managedTopic->setTitle($args['title']);
                }
                break;
        }
    }

    /**
     * Subscribe a topic.
     *
     * @param array $args Arguments array.
     *        int|object $args['topic'] Topic id or object.
     *        int $args['user_id'] User id (optional: needs ACCESS_ADMIN).
     *
     * @return boolean|void
     */
    public function subscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
        if (!is_object($args['topic'])) {
            $args['topic'] = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->findOneBy(array('topic_id' => $args['topic']));
        }
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $args['topic']->getForum())) {
            throw new AccessDeniedException();
        }
        $managedForumUser = new ForumUserManager($args['user_id']);
        $searchParams = array(
            'topic' => $args['topic'],
            'forumUser' => $managedForumUser->get());
        $topicSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')->findOneBy($searchParams);
        if (!$topicSubscription) {
            $managedForumUser->get()->addTopicSubscription($args['topic']);
            $this->entityManager->flush();
        }
    }

    /**
     * Unsubscribe a topic.
     *
     * @param array $args Arguments array.
     *        int|obect $args['topic'] Topic id or object, if not set we unsubscribe all topics.
     *        int $args['user_id'] Users id (optional: needs ACCESS_ADMIN).
     *
     * @return void|bool
     */
    public function unsubscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
        if (!is_object($args['topic'])) {
            $args['topic'] = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->findOneBy(array('topic_id' => $args['topic']));
        }
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $args['topic']->getForum())) {
            throw new AccessDeniedException();
        }
        $managedForumUser = new ForumUserManager($args['user_id']);
        if (isset($args['topic'])) {
            $topicSubscription = $this->entityManager
                ->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')
                ->findOneBy(array('topic' => $args['topic'], 'forumUser' => $managedForumUser->get()));
            $managedForumUser->get()->removeTopicSubscription($topicSubscription);
        } else {
            // not used in the code...
            $managedForumUser->get()->clearTopicSubscriptions();
        }
        $this->entityManager->flush();
    }

    /**
     * Get topic subscriptions
     *
     * @param $args['uid'] User id (optional)
     *
     * @return \Zikula\Module\DizkusModule\Entity\TopicSubscriptionEntity collection, may be empty
     */
    public function getSubscriptions($args)
    {
        if (empty($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
        }
        $managedForumUser = new ForumUserManager($args['uid']);

        return $managedForumUser->get()->getTopicSubscriptions();
    }

    /**
     * getIdByReference
     *
     * Gets a topic reference as parameter and delivers the internal topic id used for Dizkus as comment module
     *
     * @param string $reference The reference.
     *
     * @return array Topic data as array
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function getIdByReference($reference)
    {
        if (empty($reference)) {
            throw new \InvalidArgumentException();
        }

        return $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->findOneBy(array('reference' => $reference))->toArray();
    }

    /**
     * email
     *
     * This functions emails a topic to a given email address.
     *
     * @param array $args Arguments array.
     *        string $args['sendto_email'] The recipients email address.
     *        string $args['message'] The text.
     *        string $args['subject'] The subject.
     *
     * @return boolean
     */
    public function email($args)
    {
        $sender_name = UserUtil::getVar('uname');
        $sender_email = UserUtil::getVar('email');
        if (!UserUtil::isLoggedIn()) {
            $sender_name = ModUtil::getVar('Users', 'anonymous');
            $sender_email = ModUtil::getVar($this->name, 'email_from');
        }
        $params = array(
            'fromname' => $sender_name,
            'fromaddress' => $sender_email,
            'toname' => $args['sendto_email'],
            'toaddress' => $args['sendto_email'],
            'subject' => $args['subject'],
            'body' => $args['message']);

        return ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $params);
    }

    /**
     * delete a topic
     *
     * This function deletes a topic given by id or object
     *
     * @param $args[topic] The topic's id or object
     *
     * @return int the forum's id for redirecting
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function delete($args)
    {
        if (is_numeric($args['topic'])) {
            $topic = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->find($args['topic']);
        } elseif ($args['topic'] instanceof TopicEntity) {
            $topic = $args['topic'];
        } else {
            throw new \InvalidArgumentException();
        }
        $forum = $topic->getForum();
        $params = array('forum_id' => $forum->getForum_id());
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $params)) {
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
        ModUtil::apiFunc($this->name, 'sync', 'forum', array('forum' => $forum, 'flush' => false));
        ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', array('forum' => $forum, 'flush' => true));

        return $forum->getForum_id();
    }

    /**
     * Move topic
     *
     * This function moves a given topic to another forum
     *
     * @param $args['topic_id'] int the topics id
     * @param $args['forum_id'] int the destination forums id
     * @param $args['createshadowtopic']   boolean true = create shadow topic
     * @param $args['topicObj'] TopicEntity
     *
     * @return void
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function move($args)
    {
        if (!isset($args['topicObj']) || !$args['topicObj'] instanceof TopicEntity) {
            if (!isset($args['topic_id'])) {
                throw new \InvalidArgumentException();
            }
            $args['topicObj'] = $this->entityManager->find('Zikula\DizkusModule\Entity\TopicEntity', $args['topic_id']);
        }
        $managedTopic = new TopicManager(null, $args['topicObj']);
        if ($managedTopic->getForumId() != $args['forum_id']) {
            // set new forum
            $oldForumId = $managedTopic->getForumId();
            $forum = $this->entityManager->getReference('Zikula\DizkusModule\Entity\ForumEntity', $args['forum_id']);
            $managedTopic->get()->setForum($forum);
            if ($args['createshadowtopic'] == true) {
                // create shadow topic
                $managedShadowTopic = new TopicManager();
                $newUrl = $this->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $managedTopic->getId()));
                $topicData = array(
                    'title' => $this->__f('*** The original posting \'%s\' has been moved', $managedTopic->getTitle()),
                    'message' => $this->__('The original posting has been moved') . ' <a title="' . $this->__('moved') . '" href="' . $newUrl . '">' . $this->__('here') . '</a>.',
                    'forum_id' => $oldForumId,
                    'topic_time' => $managedTopic->get()->getTopic_time(),
                    'attachSignature' => false,
                    'subscribe_topic' => false);
                $managedShadowTopic->prepare($topicData);
                $managedShadowTopic->lock();
                $this->entityManager->persist($managedShadowTopic->get());
            }
            $this->entityManager->flush();
            // re-sync all forum counts and last posts
            $previousForumLocation = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $oldForumId);
            ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', array(
                'forum' => $previousForumLocation,
                'flush' => false));
            ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', array(
                'forum' => $forum,
                'flush' => false));
            ModUtil::apiFunc($this->name, 'sync', 'forum', array(
                'forum' => $oldForumId,
                'flush' => false));
            ModUtil::apiFunc($this->name, 'sync', 'forum', array(
                'forum' => $forum,
                'flush' => true));
        }

        return;
    }

    /**
     * split the topic at the provided post
     *
     * @param PostManager $args['post']
     * @param Array $args['data']
     *
     * @return Integer id of the new topic
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function split($args)
    {
        if (!isset($args['post']) || !$args['post'] instanceof PostManager || !isset($args['data']['newsubject'])) {
            throw new \InvalidArgumentException();
        }
        $managedTopic = new TopicManager(null, $args['post']->get()->getTopic());
        // create new topic
        $newTopic = new TopicEntity();
        $newTopic->setPoster($args['post']->get()->getPoster());
        $newTopic->setTitle($args['data']['newsubject']);
        $newTopic->setForum($managedTopic->get()->getForum());
        $args['post']->get()->setIsFirstPost(true);
        $args['post']->get()->setTitle($args['data']['newsubject']);
        $this->entityManager->persist($newTopic);
        $this->entityManager->flush();
        // update posts
        $dql = 'SELECT p from Zikula\DizkusModule\Entity\PostEntity p
            WHERE p.topic = :topic
            AND p.post_id >= :post
            ORDER BY p.post_id';
        $query = $this->entityManager->createQuery($dql)->setParameter('topic', $managedTopic->get())->setParameter('post', $args['post']->get()->getPost_id());
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
        ModUtil::apiFunc($this->name, 'sync', 'topicLastPost', array('topic' => $managedTopic->get(), 'flush' => true));
        $oldReplyCount = $managedTopic->get()->getReplyCount();
        $managedTopic->get()->setReplyCount($oldReplyCount - count($posts));
        // update new topic with post data
        $newTopic->setLast_post($post);
        $newTopic->setReplyCount(count($posts) - 1);
        $newTopic->setTopic_time($post->getPost_time());
        // resync topic totals, etc
        ModUtil::apiFunc($this->name, 'sync', 'forum', array('forum' => $newTopic->getForum(), 'flush' => false));
        $this->entityManager->flush();

        return $newTopic->getTopic_id();
    }

    /**
     * joins two topics together
     *
     * @param $args['to_topic_id'] int the target topic that will contain the post from from_topic (destination)
     * @param $args['from_topic_id'] int this topic get integrated into to_topic (origin)
     * @param $args['topicObj'] TopicEntity The (origin) topic as object
     *              must have *either* topicObj or from_topic_id
     *
     * @return Integer Destination topic ID
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function join($args)
    {
        if (!$args['topicObj'] instanceof TopicEntity && !isset($args['from_topic_id'])) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__f('Either "%1$s" or "%2$s" must be set.', array('topicObj', 'from_topic_id')));

            throw new \InvalidArgumentException();
        }
        if (!isset($args['to_topic_id'])) {
            throw new \InvalidArgumentException();
        }
        if (isset($args['topicObj']) && isset($args['from_topic_id'])) {
            // unset the id and use the Object
            $args['from_topic_id'] = null;
        }
        $managedOriginTopic = new TopicManager($args['from_topic_id'], $args['topicObj']);
        // one param will be null
        $managedDestinationTopic = new TopicManager($args['to_topic_id']);
        if ($managedDestinationTopic->get() === null) {
            // can't use isset() and ->get() at the same time
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Destination topic does not exist.'));

            throw new \InvalidArgumentException();
        }
        // move posts from Origin to Destination topic
        $posts = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\PostEntity')->findBy(array('topic' => $managedOriginTopic->get()));
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
        ModUtil::apiFunc($this->name, 'sync', 'topic', array('topic' => $managedDestinationTopic->get(), 'flush' => true));
        ModUtil::apiFunc($this->name, 'sync', 'forum', array('forum' => $managedOriginTopic->get()->getForum(), 'flush' => false));
        ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', array('forum' => $managedOriginTopic->get()->getForum(), 'flush' => true));
        ModUtil::apiFunc($this->name, 'sync', 'forum', array('forum' => $managedDestinationTopic->get()->getForum(), 'flush' => false));
        ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', array('forum' => $managedDestinationTopic->get()->getForum(), 'flush' => true));

        return $managedDestinationTopic->getId();
    }

}
