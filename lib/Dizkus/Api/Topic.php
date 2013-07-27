<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * This class provides the topic api functions
 */
class Dizkus_Api_Topic extends Zikula_AbstractApi
{

    function changeStatus($args)
    {
        $managedTopic = new Dizkus_Manager_Topic($args['topic_id']);
        if ($args['action'] == 'subscribe') {
            $this->subscribe(array('topic' => $managedTopic->get()));
        } else if ($args['action'] == 'unsubscribe') {
            $this->unsubscribe(array('topic' => $managedTopic->get()));
        } else {
            switch ($args['action']) {
                case 'sticky':
                    $managedTopic->sticky();
                    break;
                case 'unsticky':
                    $managedTopic->unsticky();
                    break;
                case 'lock':
                    $managedTopic->lock();
                    break;
                case 'unlock':
                    $managedTopic->unlock();
                    break;
                case 'solve':
                    $managedTopic->solve();
                    break;
                case 'unsolve':
                    $managedTopic->unsolve();
                    break;
                case 'setTitle':
                    $managedTopic->setTitle($args['title']);
                    break;
            }
        }
    }

    /**
     * Subscribe a topic.
     *
     * @param array $args Arguments array.
     *        int $args['topic'] Topic
     *        int $args['user_id'] User id (optional: needs ACCESS_ADMIN).
     *
     * @return void
     */
    public function subscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }

        // TODO: Permission check

        $managedForumUser = new Dizkus_Manager_ForumUser($args['user_id']);
        $managedForumUser->get()->addTopicSubscription($args['topic']);
        $this->entityManager->flush();
    }

    /**
     * Unsubscribe a topic.
     *
     * @param array $args Arguments array.
     *        int $args['topic'] Topic, if not set we unsubscribe all topics.
     *        int $args['user_id'] Users id (optional: needs ACCESS_ADMIN).
     *
     * @return void|bool
     */
    public function unsubscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }

        // TODO: Permission check

        $managedForumUser = new Dizkus_Manager_ForumUser($args['user_id']);
        if (isset($args['topic'])) {
            $topicSubscription = $this->entityManager->getRepository('Dizkus_Entity_TopicSubscription')->findOneBy(array(
                'topic' => $args['topic'],
                'forumUser' => $managedForumUser->get()
            ));
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
     * @params $args['uid'] User id (optional)
     *
     * @returns Dizkus_Entity_TopicSubscription collection, may be empty
     */
    public function getSubscriptions($args)
    {
        if (empty($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
        }
        $managedForumUser = new Dizkus_Manager_ForumUser($args['uid']);
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
     */
    public function getIdByReference($reference)
    {
        if (empty($reference)) {
            return LogUtil::registerArgsError();
        }

        return $this->entityManager->getRepository('Dizkus_Entity_Topic')
                        ->findOneBy(array('topic_reference' => $reference))
                        ->toArray();
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
            $sender_email = ModUtil::getVar('Dizkus', 'email_from');
        }

        $params = array(
            'fromname' => $sender_name,
            'fromaddress' => $sender_email,
            'toname' => $args['sendto_email'],
            'toaddress' => $args['sendto_email'],
            'subject' => $args['subject'],
            'body' => $args['message'],
        );
        return ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $params);
    }

    /**
     * delete a topic
     *
     * This function deletes a topic given by id.
     *
     * @param int $topic_id The topics id.
     *
     * @return int the forums id for redirecting
     */
    public function delete($topic)
    {
        if (is_numeric($topic)) {
            $topic = $this->entityManager->getRepository('Dizkus_Entity_Topic')->find($topic);
        } else {
            LogUtil::registerArgsError();
        }

        $params = array('forum_id' => $topic->getForum()->getForum_id());
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $params)) {
            return LogUtil::registerPermissionError();
        }

        $posts = $topic->getPosts();
        foreach ($posts as $post) {
            $post->getPoster()->decrementPostCount();
        }

        // decrement topicCount
        $topic->getForum()->decrementTopicCount();
        
        // update the db
        $this->entityManager->flush();

        // remove all posts in topic
        $this->entityManager->getRepository('Dizkus_Entity_Topic')->manualDeletePosts($topic->getTopic_id());

        // remove topic subscriptions
        $this->entityManager->getRepository('Dizkus_Entity_Topic')->deleteTopicSubscriptions($topic->getTopic_id());

        // delete the topic (manual dql to avoid cascading deletion errors)
        $this->entityManager->getRepository('Dizkus_Entity_Topic')->manualDelete($topic->getTopic_id());

        // sync the forum up with the changes
        ModUtil::apiFunc('Dizkus', 'sync', 'forum', array('forum' => $topic->getForum(), 'flush' => false));
        ModUtil::apiFunc('Dizkus', 'sync', 'forumLastPost', array('forum' => $topic->getForum(), 'flush' => true));
        return $topic->getForum()->getForum_id();
    }
    
    /**
     * Move topic
     *
     * This function moves a given topic to another forum
     *
     * @params $args['topic_id'] int the topics id
     * @params $args['forum_id'] int the destination forums id
     * @params $args['createshadowtopic']   boolean true = create shadow topic
     * @params $args['topicObj'] Dizkus_Entity_Topic
     *
     * @returns void
     */
    public function move($args)
    {
        if (!isset($args['topicObj']) || !($args['topicObj'] instanceof Dizkus_Entity_Topic)) {
            if (!isset($args['topic_id'])) {
                return LogUtil::registerArgsError();
            }
            $args['topicObj'] = $this->entityManager->find('Dizkus_Entity_Topic', $args['topic_id']); //->toArray();
        }
        $managedTopic = new Dizkus_Manager_Topic(null, $args['topicObj']);
        
        if ($managedTopic->getForumId() <> $args['forum_id']) {
            // set new forum
            $oldForumId = $managedTopic->getForumId();
            $forum = $this->entityManager->find('Dizkus_Entity_Forum', $args['forum_id']);
            $managedTopic->get()->setForum($forum);

            if ($args['createshadowtopic'] == true) {
                // create shadow topic
                $managedShadowTopic = new Dizkus_Manager_Topic();
                $topicData = array(
                    'topic_title' => $this->__f("*** The original posting '%s' has been moved", $managedTopic->getTitle()),
                    'message' => $this->__('The original posting has been moved') . ' <a title="' . $this->__('moved') . '" href="'. ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $managedTopic->getId())) .'">' . $this->__('here') . '</a>.',
                    'forum_id' => $oldForumId,
                    'topic_time' => $managedTopic->get()->getTopic_time(),
                    'post_attach_signature' => false,
                    'subscribe_topic' => false);
                $managedShadowTopic->prepare($topicData);
                $managedShadowTopic->lock();
                $this->entityManager->persist($managedShadowTopic->get());
            }

            $this->entityManager->flush();
            // re-sync all forum counts and last posts
            $previousForumLocation = $this->entityManager->find('Dizkus_Entity_Forum', $oldForumId);
            ModUtil::apiFunc('Dizkus', 'sync', 'forumLastPost', array('forum' => $previousForumLocation, 'flush' => false));
            ModUtil::apiFunc('Dizkus', 'sync', 'forumLastPost', array('forum' => $forum, 'flush' => false));
            ModUtil::apiFunc('Dizkus', 'sync', 'forum', array('forum' => $oldForumId, 'flush' => false));
            ModUtil::apiFunc('Dizkus', 'sync', 'forum', array('forum' => $forum, 'flush' => true));
        }

        return;
    }
    
    /**
     * split the topic at the provided post
     *
     * @params Dizkus_Manager_Post $args['post']
     * @params Array $args['data']
     *
     * @return Integer id of the new topic
     */
    public function split($args)
    {
        if (!isset($args['post']) || !($args['post'] instanceof Dizkus_Manager_Post) || !isset($args['data']['newsubject'])) {
            return LogUtil::registerArgsError();
        }
        $managedTopic = new Dizkus_Manager_Topic(null, $args['post']->get()->getTopic());

        // create new topic
        $newTopic = new Dizkus_Entity_Topic();
        $newTopic->setTopic_poster($args['post']->get()->getPoster());
        $newTopic->setTopic_title($args['data']['newsubject']);
        $newTopic->setForum($managedTopic->get()->getForum());
        $args['post']->get()->setPost_first(true);
        $args['post']->get()->setTitle($args['data']['newsubject']);
        $this->entityManager->persist($newTopic);
        $this->entityManager->flush();

        // update posts
        $dql = "SELECT p from Dizkus_Entity_Post p
            WHERE p.topic = :topic
            AND p.post_id >= :post
            ORDER BY p.post_id";
        $query = $this->entityManager->createQuery($dql)
            ->setParameter('topic', $managedTopic->get())
            ->setParameter('post', $args['post']->get()->getPost_id());
        /* @var $posts Array of Dizkus_Entity_Post */
        $posts = $query->getResult();
        // update the topic_id in the postings
        foreach($posts as $post) {
            $post->setTopic($newTopic);
        }
        // must flush here so sync gets correct information
        $this->entityManager->flush();
        // last iteration of `$post` used below
        
        // update old topic
        ModUtil::apiFunc('Dizkus', 'sync', 'topicLastPost', array('topic' => $managedTopic->get(), 'flush' => true));
        $oldReplyCount = $managedTopic->get()->getTopic_replies();
        $managedTopic->get()->setTopic_replies($oldReplyCount - count($posts));

        // update new topic with post data
        $newTopic->setLast_post($post);
        $newTopic->setTopic_replies(count($posts) - 1);
        $newTopic->setTopic_time($post->getPost_time());

        // resync topic totals, etc
        ModUtil::apiFunc('Dizkus', 'sync', 'forum', array('forum' => $newTopic->getForum(), 'flush' => false));
        $this->entityManager->flush();

        return $newTopic->getTopic_id();
    }

    /**
     * joins two topics together
     *
     * @params $args['to_topic_id'] int the target topic that will contain the post from from_topic (destination)
     * @params $args['from_topic_id'] int this topic get integrated into to_topic (origin)
     * @params $args['topicObj'] Dizkus_Entity_Topic The (origin) topic as object
     *              must have *either* topicObj or from_topic_id
     * 
     * @return Integer Destination topic ID
     */
    public function join($args)
    {
        if (!($args['topicObj'] instanceof Dizkus_Entity_Topic) && !isset($args['from_topic_id'])) {
            LogUtil::registerError($this->__f('Either "%1$s" or "%2$s" must be set.', array('topicObj', 'from_topic_id')));
            return LogUtil::registerArgsError();            
        }
        if (!isset($args['to_topic_id'])) {
            return LogUtil::registerArgsError();
        }
        if (isset($args['topicObj']) && isset($args['from_topic_id'])) {
            // unset the id and use the Object
            $args['from_topic_id'] = null;
        }
        $managedOriginTopic = new Dizkus_Manager_Topic($args['from_topic_id'], $args['topicObj']); // one param will be null
        $managedDestinationTopic = new Dizkus_Manager_Topic($args['to_topic_id']);
        
        if ($managedDestinationTopic->get() === null) { // can't use isset() and ->get() at the same time
            LogUtil::registerError($this->__('Destination topic does not exist.'));
            return LogUtil::registerArgsError();
        }

        // move posts from Origin to Destination topic
        $posts = $this->entityManager->getRepository('Dizkus_Entity_Post')->findBy(array('topic' => $managedOriginTopic->get()));
        $previousPostTime = $managedDestinationTopic->get()->getLast_post()->getPost_time();
        foreach ($posts as $post) {
            $post->setTopic($managedDestinationTopic->get());
            $post->setForum_id($managedDestinationTopic->getForumId());
            if ($post->getPost_time() <= $previousPostTime) {
                $post->setPost_time($previousPostTime->modify("+1 minute"));
            }
            $previousPostTime = $post->getPost_time();
        }
        $this->entityManager->flush();

        // remove the originTopic from the DB (manual dql to avoid cascading deletion errors)
        $this->entityManager->getRepository('Dizkus_Entity_Topic')->manualDelete($managedOriginTopic->getId());

        $managedDestinationTopic->setLastPost($post);
        $managedDestinationTopic->get()->setTopic_time($previousPostTime);

        // resync destination topic and all forums
        ModUtil::apiFunc('Dizkus', 'sync', 'topic', array('topic' => $managedDestinationTopic->get(), 'flush' => true));
        ModUtil::apiFunc('Dizkus', 'sync', 'forum', array('forum' => $managedOriginTopic->get()->getForum(), 'flush' => false));
        ModUtil::apiFunc('Dizkus', 'sync', 'forumLastPost', array('forum' => $managedOriginTopic->get()->getForum(), 'flush' => true));
        ModUtil::apiFunc('Dizkus', 'sync', 'forum', array('forum' => $managedDestinationTopic->get()->getForum(), 'flush' => false));
        ModUtil::apiFunc('Dizkus', 'sync', 'forumLastPost', array('forum' => $managedDestinationTopic->get()->getForum(), 'flush' => true));

        return $managedDestinationTopic->getId();
    }

}