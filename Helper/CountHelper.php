<?php

declare(strict_types=1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Helper;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * CountHelper.
 *
 * @author Kaik
 */
class CountHelper
{
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

    private $cache = [];

    public function __construct(
            RequestStack $requestStack,
            EntityManager $entityManager,
            CurrentUserApi $userApi
         ) {
        $this->name = 'ZikulaDizkusModule';
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->userApi = $userApi;
    }

    /**
     * Count forums.
     *
     * @param $force boolean, default false, if true, do not use cached
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int (depending on type and id)
     */
    public function getAllForumsCount($force = false)
    {
        if ($force || !isset($this->cache['Forum']['all'])) {
            $this->cache['Forum']['all'] = $this->countEntity('Forum');
        }

        return $this->cache['Forum']['all'];
    }

    /**
     * Count forum topics.
     *
     * @param $force boolean, default false, if true, do not use cached
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int (depending on type and id)
     */
    public function getForumTopicsCount($forum, $force = false)
    {
        if ($force || !isset($this->cache['Forum'][$forum]['topics'])) {
            // we count topics with parent forum id equall to id
            $this->cache['Forum'][$forum]['topics'] = $this->countEntity('Topic', 'forum', $forum);
        }

        return $this->cache['Forum'][$forum]['topics'];
    }

    /**
     * Count forum posts.
     *
     * @param $force boolean, default false, if true, do not use cached
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int (depending on type and id)
     */
    public function getForumPostsCount($forum, $force = false)
    {
        if ($force || !isset($this->cache['Forum'][$forum]['posts'])) {
            $dql = 'SELECT count(p)
                FROM Zikula\DizkusModule\Entity\PostEntity p
                WHERE p.topic IN (
                    SELECT t.id
                    FROM Zikula\DizkusModule\Entity\TopicEntity t
                    WHERE t.forum = :forum)';
            $query = $this->entityManager->createQuery($dql)->setParameter('forum', $forum);
            $this->cache['Forum'][$forum]['posts'] = $query->getSingleScalarResult();
        }

        return $this->cache['Forum'][$forum]['posts'];
    }

    /**
     * Count all topics.
     *
     * @param $force boolean, default false, if true, do not use cached
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int (depending on type and id)
     */
    public function getAllTopicsCount($force = false)
    {
        if ($force || !isset($this->cache['Topic']['all'])) {
            $this->cache['Topic']['all'] = $this->countEntity('Topic');
        }

        return $this->cache['Topic']['all'];
    }

    /**
     * Count topic posts.
     *
     * @param $force boolean, default false, if true, do not use cached
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int (depending on type and id)
     */
    public function getTopicPostsCount($topic, $force = false)
    {
        if ($force || !isset($this->cache['Topic'][$topic]['posts'])) {
            // we count posts with parent topic
            $this->cache['Topic'][$topic]['posts'] = $this->countEntity('Post', 'topic', $topic);
        }

        return $this->cache['Topic'][$topic]['posts'];
    }

    /**
     * Counts posts.
     *
     * @param $force boolean, default false, if true, do not use cached
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return int (depending on type and id)
     */
    public function getAllPostsCount($force = false)
    {
        if ($force || !isset($this->cache['Post']['all'])) {
            $this->cache['Post']['all'] = $this->countEntity('Post');
        }

        return $this->cache['Post']['all'];
    }

    /**
     * Count the number of items in a provided entity.
     *
     * @param $entityname
     * @param null $where
     * @param null $parameter
     *
     * @return int
     */
    private function countEntity($entityname, $where = null, $parameter = null)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(a)')->from("Zikula\\DizkusModule\\Entity\\{$entityname}Entity", 'a');
        if (isset($where, $parameter)) {
            $qb->andWhere('a.' . $where . ' = :parameter')->setParameter('parameter', $parameter);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
