<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Dizkus\Api;

use LogUtil;
use Dizkus_Entity_Forum;
use ModUtil;
use Dizkus_Entity_Topic;
use Dizkus_Entity_ForumUser;

class SyncApi extends \Zikula_AbstractApi
{

    /**
     * perform sync on all forums, topics and posters
     *
     * @param Boolean $silentMode (unused)
     */
    public function all($silentMode = false)
    {
        $this->forums();
        $this->topics();
        $this->posters();
    }

    /**
     * perform sync on all forums
     *
     * @return Boolean
     */
    public function forums()
    {
        // reset count to zero
        $dql = 'UPDATE Dizkus_Entity_Forum f SET f.topicCount = 0, f.postCount = 0';
        $this->entityManager->createQuery($dql)->execute();
        // order by level asc in order to do the parents first, down to children. This SHOULD keep the count accurate.
        $forums = $this->entityManager->getRepository('Dizkus_Entity_Forum')->findBy(array(), array('lvl' => 'ASC'));
        foreach ($forums as $forum) {
            $this->forum(array('forum' => $forum));
        }

        return true;
    }

    /**
     * recalculate topicCount and postCount counts
     *
     * @param Dizkus_Entity_Forum $args['forum']
     * @param Boolean             $args['flush']
     *
     * @return boolean
     */
    public function forum($args)
    {
        if (!isset($args['forum'])) {
            return LogUtil::registerArgsError();
        }
        if ($args['forum'] instanceof Dizkus_Entity_Forum) {
            $id = $args['forum']->getForum_id();
        } else {
            $id = $args['forum'];
            $args['forum'] = $this->entityManager->find('Dizkus_Entity_Forum', $id);
        }
        // count topics of a forum
        $topicCount = ModUtil::apiFunc('Dizkus', 'user', 'countstats', array('type' => 'forumtopics', 'id' => $id, 'force' => true));
        $args['forum']->setTopicCount($topicCount);
        // count posts of a forum
        $postCount = ModUtil::apiFunc('Dizkus', 'user', 'countstats', array('type' => 'forumposts', 'id' => $id, 'force' => true));
        $args['forum']->setPostCount($postCount);
        $this->entityManager->flush();
        $this->addToParentForumCount($args['forum'], 'Post');
        $this->addToParentForumCount($args['forum'], 'Topic');

        return true;
    }

    /**
     * recursive function to add counts to parents
     * @param Dizkus_Entity_Forum $forum
     * @param string              $entity
     */
    private function addToParentForumCount(Dizkus_Entity_Forum $forum, $entity = 'Post')
    {
        $parent = $forum->getParent();
        if (!isset($parent)) {
            return;
        }
        $entity = in_array($entity, array('Post', 'Topic')) ? $entity : 'Post';
        $getMethod = "get{$entity}Count";
        $currentParentCount = $parent->{$getMethod}();
        $forumCount = $forum->{$getMethod}();
        $setMethod = "set{$entity}Count";
        $parent->{$setMethod}($currentParentCount + $forumCount);
        $this->entityManager->flush();
        $grandParent = $parent->getParent();
        if (isset($grandParent)) {
            $this->addToParentForumCount($parent, $entity);
        }
    }

    /**
     * perform sync on all topics
     *
     * @return boolean
     */
    public function topics()
    {
        $topics = $this->entityManager->getRepository('Dizkus_Entity_Topic')->findAll();
        foreach ($topics as $topic) {
            $this->topic(array('topic' => $topic, 'type' => 'forum'));
        }
        // flush?
        return true;
    }

    /**
     * recalcluate Topic replies for one topic
     *
     * @param Dizkus_Entity_Topic $args['topic']
     * @param Boolean             $args['flush']
     *
     * @return boolean
     */
    public function topic($args)
    {
        if (!isset($args['topic'])) {
            return LogUtil::registerArgsError();
        }
        if ($args['topic'] instanceof Dizkus_Entity_Topic) {
            $id = $args['topic']->getTopic_id();
        } else {
            $id = $args['topic'];
            $args['topic'] = $this->entityManager->find('Dizkus_Entity_Topic', $id);
        }
        $flush = isset($args['flush']) ? $args['flush'] : true;
        // count posts of a topic
        $qb = $this->entityManager->createQueryBuilder();
        $replies = $qb->select('COUNT(p)')->from('Dizkus_Entity_Post', 'p')->where('p.topic = :id')->setParameter('id', $id)->getQuery()->getSingleScalarResult();
        $replies = (int) $replies - 1;
        $args['topic']->setReplyCount($replies);
        if ($flush) {
            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * recalculate user posts for all users
     *
     * @return boolean
     */
    public function posters()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $posts = $qb->select('count(p)', 'IDENTITY(d.user) as user_id')->from('Dizkus_Entity_Post', 'p')->leftJoin('p.poster', 'd')->groupBy('d.user')->getQuery()->getArrayResult();
        foreach ($posts as $post) {
            $forumUser = $this->entityManager->find('Dizkus_Entity_ForumUser', $post['user_id']);
            if (!$forumUser) {
                $forumUser = new Dizkus_Entity_ForumUser();
                $coreUser = $this->entityManager->find('Zikula\\Module\\UsersModule\\Entity\\UserEntity', $post['user_id']);
                $forumUser->setUser($coreUser);
            }
            $forumUser->setPostCount($post[1]);
        }
        $this->entityManager->flush();

        return true;
    }

    /**
     * reset the last post in a forum due to movement
     * @param Dizkus_Entity_Forum $args['forum']
     * @param Boolean             $args['flush'] default: true
     *
     * @return void
     */
    public function forumLastPost($args)
    {
        if (!isset($args['forum']) || !$args['forum'] instanceof Dizkus_Entity_Forum) {
            return LogUtil::registerArgsError();
        }
        $flush = isset($args['flush']) ? $args['flush'] : true;
        // get the most recent post in the forum
        $dql = 'SELECT t FROM Dizkus_Entity_Topic t
            WHERE t.forum = :forum
            ORDER BY t.topic_time DESC';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('forum', $args['forum']);
        $query->setMaxResults(1);
        $topic = $query->getOneOrNullResult();
        if (isset($topic)) {
            $args['forum']->setLast_post($topic->getLast_post());
        }
        // recurse up the tree
        $parent = $args['forum']->getParent();
        if (isset($parent)) {
            $this->forumLastPost(array('forum' => $parent, 'flush' => false));
        }
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * reset the last post in a topic due to movement
     * @param Dizkus_Entity_Topic $args['topic']
     * @param Boolean             $args['flush']
     *
     * @return void
     */
    public function topicLastPost($args)
    {
        if (!isset($args['topic']) || !$args['topic'] instanceof Dizkus_Entity_Topic) {
            return LogUtil::registerArgsError();
        }
        $flush = isset($args['flush']) ? $args['flush'] : true;
        // get the most recent post in the topic
        $dql = 'SELECT p FROM Dizkus_Entity_Post p
            WHERE p.topic = :topic
            ORDER BY p.post_time DESC';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('topic', $args['topic']);
        $query->setMaxResults(1);
        $post = $query->getSingleResult();
        $args['topic']->setLast_post($post);
        $args['topic']->setTopic_time($post->getPost_time());
        if ($flush) {
            $this->entityManager->flush();
        }
    }

}
