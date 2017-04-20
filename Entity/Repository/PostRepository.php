<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
    /**
     * Delete a post via dql
     * avoids cascading deletion errors
     * but does not deleted associations
     *
     * @param integer $id
     */
    public function manualDelete($id)
    {
        $dql = 'DELETE Zikula\DizkusModule\Entity\PostEntity p
            WHERE p.post_id = :id';
        $this->_em->createQuery($dql)->setParameter('id', $id)->execute();
    }

    /**
     * gets the last $maxPosts postings of forum $forum_id.
     *
     * @param int limit number of posts to read, default = 5
     * @param int user last postings of user
     * @param array forums limit to forums in array
     *
     * @return array $lastposts
     */
    public function getLastPosts($limit = 5, $user = null, $forums = null)
    {
        $limit = (isset($limit) && is_numeric($limit) && $limit > 0) ? $limit : 5;
        // hard limit to 100 to be safe
        $limit = ($limit > 100) ? 100 : $limit;

        $qb = $this->_em->createQueryBuilder();
        $qb->select(['p', 'fu', 't', 'f'])
            ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
            ->leftJoin('p.topic', 't')
            ->leftJoin('t.forum', 'f')
            ->innerJoin('p.poster', 'fu');
        if (!empty($forums)) {
            $qb->andWhere('t.forum IN (:forums)')
                ->setParameter('forums', $forums);
        }

        if (!empty($user)) {
            $qb->andWhere('fu.user = :id')
                ->setParameter('id', $user);
        }
        $qb->orderBy('p.post_time', 'DESC');
        $qb->setMaxResults($limit);
        $posts = $qb->getQuery()->getResult();

        return $posts;
    }

}
