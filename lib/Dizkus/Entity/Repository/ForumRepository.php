<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class Dizkus_Entity_Repository_ForumRepository extends NestedTreeRepository
{

    /**
     * getOneLevel
     *
     * Get the first level of the tree.
     *
     * @param int $category Category id
     *
     * @return object
     */
    public function getOneLevel($category = null)
    {
        $qb = $this->_em
                ->createQueryBuilder()
                ->select('f, c, l')
                ->orderBy('f.lft')
                ->from('Dizkus_Entity_Forum', 'f')
                ->leftJoin('f.children', 'c')
                ->leftJoin('c.last_post', 'l');

        // category
        if ($category > 0) {
            $qb->andWhere('f.forum_id = :category')->setParameter('category', $category);
        } else {
            $qb->andWhere('f.lvl = 1');
        }


        // favorites
        if (UserUtil::isLoggedIn() && ModUtil::getVar('Dizkus', 'favorites_enabled') == 'yes') {
            if (ModUtil::apiFunc('Dizkus', 'Favorites', 'getStatus')) {
                $qb->join('c.favorites', 'fa')
                    ->andWhere('fa.forumUser = :uid')
                    ->setParameter('uid', UserUtil::getVar('uid'));
            }
        }

        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * getForumTree
     *
     * Determines the forum tree.
     *
     * @return array
     */
    public function getTree()
    {
        return $this->childrenHierarchy(null, false);
    }

    public function getRssForums()
    {
        $dql = "SELECT f FROM Dizkus_Entity_Forum f
                WHERE f.pop3Connection IS NOT NULL";
        $query = $this->_em->createQuery($dql);
        try {
            $result = $query->getResult();
        } catch (Exception $e) {
            echo "<pre>";
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }
        return $result;
    }
}