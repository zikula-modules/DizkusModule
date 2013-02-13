<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
class Dizkus_Api_Sync extends Zikula_AbstractApi
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
        $forums = $this->entityManager->getRepository('Dizkus_Entity_Forum')->findAll();
        foreach ($forums as $forum) {
            $this->forum(array('forum' => $forum, 'type' => 'forum'));
        }
        // flush?
        return true;
    }

    /**
     * recalculate forum_topics and forum_posts counts
     * 
     * @param Dizkus_Entity_Forum $args['forum']
     * @param Boolean $args['flush']
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
        $flush = isset($args['flush']) ? $args['flush'] : true;

        // count topics of a forum
        $qb = $this->entityManager->createQueryBuilder();
        $data['forum_topics'] = $qb->select('COUNT(t)')
                ->from('Dizkus_Entity_Topic', 't')
                ->where('t.forum = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleScalarResult();
        // count posts of a forum
        $qb = $this->entityManager->createQueryBuilder();
        $data['forum_posts'] = $qb->select('COUNT(p)')
                ->from('Dizkus_Entity_Post', 'p')
                ->where('p.forum_id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleScalarResult();
        $args['forum']->merge($data, false);
        $this->entityManager->persist($args['forum']);
        if ($flush) {
            $this->entityManager->flush();
        }
        
        return true;
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
     * @param Boolean $args['flush']
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
        $replies = $qb->select('COUNT(p)')
                ->from('Dizkus_Entity_Post', 'p')
                ->where('p.topic = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleScalarResult();
        $replies = (int)$replies - 1;
        $args['topic']->setTopic_replies($replies);
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
        $posts = $qb->select('count(p)', 'd.user_id')
                ->from('Dizkus_Entity_Post', 'p')
                ->leftJoin('p.poster', 'd')
                ->groupBy('d.user_id')
                ->getQuery()
                ->getArrayResult();

        foreach ($posts as $post) {
            $forumUser = $this->entityManager->find('Dizkus_Entity_ForumUser', $post['user_id']);
            if (!$forumUser) {
                $forumUser = new Dizkus_Entity_ForumUser();
                $coreUser = $this->entityManager->find('Users\Entity\UserEntity', $post['user_id']);
                $forumUser->setUser($coreUser);
            }
            $forumUser->setUser_posts($post[1]);
        }
        $this->entityManager->flush();
        return true;
    }

    /**
     * reset the last post in a forum due to movement
     * @param Dizkus_Entity_Forum $args['forum']
     * @param Boolean $args['flush']
     * 
     * @return void
     */
    public function forumLastPost($args)
    {
        if (!isset($args['forum']) || !($args['forum'] instanceof Dizkus_Entity_Forum)) {
            return LogUtil::registerArgsError();
        }
        $flush = isset($args['flush']) ? $args['flush'] : true;
        
        // get the most recent post in the forum
        $dql = "SELECT p FROM Dizkus_Entity_Post p
            WHERE p.forum_id = :forumid
            ORDER BY p.post_time DESC";
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('forumid', $args['forum']->getForum_id());
        $query->setMaxResults(1);

        $post = $query->getSingleResult();
        $args['forum']->setLast_post($post);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * reset the last post in a topic due to movement
     * @param Dizkus_Entity_Topic $args['topic']
     * @param Boolean $args['flush']
     * 
     * @return void
     */
    public function topicLastPost($args)
    {
        if (!isset($args['topic']) || !($args['topic'] instanceof Dizkus_Entity_Topic)) {
            return LogUtil::registerArgsError();
        }
        $flush = isset($args['flush']) ? $args['flush'] : true;
        
        // get the most recent post in the topic
        $dql = "SELECT p FROM Dizkus_Entity_Post p
            WHERE p.topic = :topic
            ORDER BY p.post_time DESC";
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