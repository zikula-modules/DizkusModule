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
 * This class provides the post api functions
 */
class Dizkus_Api_Category extends Zikula_AbstractApi {


    /**
     * get category
     *
     * Return category entity as an array
     *
     * @param int $cat_id The category id to find the category of.
     *
     * @return array|boolean
     */
    public function get($cat_id)
    {
        if (!is_numeric($cat_id)) {
            return false;
        }
        //return $this->entityManager->find('Dizkus_Entity_Categories', $cat_id)->toArray();
    }


    /**
     * Check if this is the first post in a topic.
     *
     * @return array
     */
    public function getAll($exclude = array())
    {
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('c')
            ->from('Dizkus_Entity_Categories', 'c')
            ->orderBy('c.cat_order', 'ASC');


        if (!is_array($exclude)) {
            $qb->where('c.cat_id != :exclude')
               ->setParameter('exclude', $exclude);
        }

        return $qb->getQuery()->getArrayResult();
        //return $this->entityManager->getRepository('Dizkus_Entity_Categories')->findAll();
    }



    /**
     * get highest order
     *
     * Determines the ordner number for new categories
     *
     * @return array
     */
    public function getHighestOrder()
    {
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('MAX(c.cat_order)')
            ->from('Dizkus_Entity_Categories', 'c');
        $highestOrder = $qb->getQuery()->getArrayResult();
        if (!$highestOrder) {
            return 1;
        } else {
            return $highestOrder[0][1]+1;
        }

    }


    /**
     * get forums of a category
     *
     * @param int $catId Category ID.
     *
     * @return array
     */
    public function getForums($catId)
    {
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('f')
            ->from('Dizkus_Entity_Forums', 'f')
            ->where('f.parent_id = 0 and f.cat_id = :catId')
            ->setParameter('catId', $catId);
        return $qb->getQuery()->getArrayResult();

    }


    /**
     * delete child forums
     *
     * @return array
     */
    public function deleteChildForums($cat_id)
    {
        $find = array('parent_id' => 0, 'cat_id' => $cat_id);
        $forums = $this->entityManager->getRepository('Dizkus_Entity_Forums')->findBy($find);
        foreach ($forums as $forum) {
            ModUtil::apiFunc($this->name, 'Forum', 'delete', $forum);
        }
    }



}
