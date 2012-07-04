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
     * Check if this is the first post in a topic.
     *
     * @return boolean
     */
    public function getAll()
    {
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('c')
            ->from('Dizkus_Entity_Categories', 'c')
            ->orderBy('c.cat_order', 'ASC');
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



}
