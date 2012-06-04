<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Favorites extends Zikula_AbstractApi {
    
    
    
    /**
     * Get forum subscription status
     *
     * @params $args['user_id'] int the users uid
     * @params $args['forum_id'] int the forums id
     * @returns bool true if the user is subscribed or false if not
     */
    public function getForumStatus($args)
    {
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(f.forum_id)')
           ->from('Dizkus_Entity_Favorites', 'f')
           ->where('f.user_id = :user')
           ->setParameter('user', $args['user_id'])
           ->andWhere('f.forum_id = :forum')
           ->setParameter('forum', $args['forum_id'])
           ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();
        return $count > 0;
        
    }
    
}

?>