<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Entity\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Zikula\DizkusModule\Entity\ForumEntity;

class ForumRepository extends NestedTreeRepository
{
    public function getRssForums()
    {
        $dql = 'SELECT f FROM Zikula\DizkusModule\Entity\ForumEntity f
                WHERE f.pop3Connection IS NOT NULL';
        $query = $this->_em->createQuery($dql);
        try {
            $result = $query->getResult();
        } catch (\Exception $e) {
            echo '<pre>';
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }

        return $result;
    }

    // hmm free topics?
    public function countForumTopics($forum = 0)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $query = $this->_em->createQuery('SELECT COUNT(t.id) FROM Zikula\DizkusModule\Entity\TopicEntity t WHERE t.forum=:forum');
        $query->setParameter('forum', $forum);
        $count = $query->getSingleScalarResult();

        return $count;
    }

    /**
     * Reset the last post in a forum due to movement
     *
     * @param ForumEntity $forum
     * @param bool        $flush default: true
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool|void
     */
    public function resetLastPost($forum, $flush = true)
    {
        //        if (!isset($forum) || !$forum instanceof ForumEntity) {
//            throw new \InvalidArgumentException();
//        }

        // get the most recent topic in the forum
        $dql = 'SELECT t FROM Zikula\DizkusModule\Entity\TopicEntity t
            WHERE t.forum = :forum
            ORDER BY t.topic_time DESC';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('forum', $forum);
        $query->setMaxResults(1);
        $topic = $query->getOneOrNullResult();

        if (isset($topic)) {
            // set last topic post as forum last post
            // note that this relays on topic last post is in sync
            $forum->setLast_post($topic->getLast_post());
        }
        // recurse up the tree
        $parent = $forum->getParent();
        if (isset($parent)) {
            // each parent have its own "last post"
            // no flush
            $this->resetLastPost($parent, false);
        }
        if ($flush) {
            $this->_em->flush();
        }

        return $this;
    }

    /**
     * gets the last $maxforums forums.
     *
     * @param mixed[] $params {
     * @var int maxforums    number of forums to read, default = 5
     *                        }
     *
     * @return array $topForums
     *
     * @todo fix
     */
    public function getTopForums($params)
    {
        $forumMax = (!empty($params['maxforums'])) ? $params['maxforums'] : 5;

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('f')
            ->from('Zikula\DizkusModule\Entity\ForumEntity', 'f')
            ->orderBy('f.lvl', 'DESC')
            ->addOrderBy('f.postCount', 'DESC');
        $qb->setMaxResults($forumMax);
        $forums = $qb->getQuery()->getResult();

        $topForums = [];
        if (!empty($forums)) {
            foreach ($forums as $forum) {
                if ($this->permission->canRead($forum)) {
                    $topforum = $forum->toArray();
                    $topforum['name'] = $forum->getName();
                    $parent = $forum->getParent();
                    $parentName = isset($parent) ? $parent->getName() : $this->translator->__('Root');
                    $topforum['cat_title'] = $parentName;
                    array_push($topForums, $topforum);
                }
            }
        }

        return $topForums;
    }
}
