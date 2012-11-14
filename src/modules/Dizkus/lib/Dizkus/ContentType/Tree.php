<?php
/**
 * Copyright Pages Team 2012
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Dizkus_ContentType_Tree extends Zikula_AbstractBase
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
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->select('f, c, l')
            ->orderBy('f.lft')
            ->from('Dizkus_Entity_Forums', 'f')
            ->leftJoin('f.children', 'c')
            ->leftJoin('c.last_post', 'l');

        // category
        if ($category > 0) {
            $qb->andWhere('f.forum_id = :category')->setParameter('category', $category);
        } else {
            $qb->andWhere('f.lvl = 0');
        }


        // favorites
        if (UserUtil::isLoggedIn() && $this->getVar('favorites_enabled') == 'yes') {
            if (ModUtil::apiFunc($this->name, 'Favorites', 'getStatus')) {
                $qb->join('c.favorites', 'fa')
                   ->andWhere('fa.user_id = :uid')
                   ->setParameter('uid', UserUtil::getVar('uid'));
            }
        }

        return $qb->getQuery()->getResult();
    }



    /**
     * getForumTree
     *
     * Determines the forum tree.
     *
     * @return array
     */
    public function get()
    {
        $repo = $this->entityManager->getRepository('Dizkus_Entity_Forums');

        return $repo->childrenHierarchy(null, false);
    }
}