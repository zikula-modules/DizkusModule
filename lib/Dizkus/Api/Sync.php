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

    public function all($silendMode = false)
    {
        $this->forums();
        $this->topics();
        $this->posters();
    }

    /**
     * This function should receive $id, $type
     * synchronizes forums/topics/users
     *
     * $id, $type
     */
    public function forums()
    {
        $forums = $this->entityManager->getRepository('Dizkus_Entity_Forum')->findAll();
        foreach ($forums as $forum) {
            $this->forum(array('forum' => $forum, 'type' => 'forum'));
        }
        return true;
    }

    public function forum($args)
    {
        if ($args['forum'] instanceof Dizkus_Entity_Forum) {
            $id = $args['forum']->getForum_id();
        } else {
            $id = $args['forum'];
            $args['forum'] = $this->entityManager->find('Dizkus_Entity_Forum', $id);
        }

        // count topics of a forum
        $qb = $this->entityManager->createQueryBuilder();
        $data['forum_topics'] = $qb->select('COUNT(t)')
                ->from('Dizkus_Entity_Topic', 't')
                ->where('t.forum_id = :id')
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
        $this->entityManager->flush();

        return true;
    }

    public function topics()
    {
        $topics = $this->entityManager->getRepository('Dizkus_Entity_Topic')->findAll();
        foreach ($topics as $topic) {
            $this->topic(array('topic' => $topic, 'type' => 'forum'));
        }
        return true;
    }

    public function topic($args)
    {


        if ($args['topic'] instanceof Dizkus_Entity_Topic) {
            $id = $args['topic']->getTopic_id();
        } else {
            $id = $args['topic'];
            $args['topic'] = $this->entityManager->find('Dizkus_Entity_Topic', $id);
        }


        // count posts of a topic
        $qb = $this->entityManager->createQueryBuilder();
        $replies = $qb->select('COUNT(p)')
                ->from('Dizkus_Entity_Post', 'p')
                ->where('p.topic_id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleScalarResult();
        $replies = (int)$replies - 1;
        $args['topic']->setTopic_replies($replies);
        $this->entityManager->flush();

        return true;
    }

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
            $poster = $this->entityManager->find('Dizkus_Entity_Poster', $post['user_id']);
            if (!$poster) {
                $poster = new Dizkus_Entity_Poster();
                $poster->setuser_id($post['user_id']);
            }
            $poster->setuser_posts($post[1]);
        }
        $this->entityManager->flush();



        return true;
    }

}