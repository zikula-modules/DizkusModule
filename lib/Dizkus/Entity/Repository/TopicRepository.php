<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
use Doctrine\ORM\EntityRepository;

class Dizkus_Entity_Repository_TopicRepository extends EntityRepository
{
    /**
     * Delete a topic via dql
     * avoids cascading deletion errors
     * but does not deleted associations
     * 
     * @param integer $id
     */
    public function manualDelete($id)
    {
        $dql = "DELETE Dizkus_Entity_Topic t
            WHERE t.topic_id = :id";
        $this->_em->createQuery($dql)
                ->setParameter('id', $id)
                ->execute();
    }
    
    public function manualDeletePosts($id)
    {
        $dql = "DELETE Dizkus_Entity_Post p
            WHERE p.topic = :topic";
        $this->_em->createQuery($dql)
                ->setParameter('topic', $id)
                ->execute();        
    }

    /**
     * Delete all subscriptions for a topic
     * $id can be the integer topic id or the Dizkus_Entity_Topic object
     * 
     * @param mixed int/obj $id
     */
    public function deleteTopicSubscriptions($id)
    {
        $dql = "DELETE from Dizkus_Entity_TopicSubscription ts
            WHERE ts.topic = :topic";
        $this->_em->createQuery($dql)
                ->setParameter('topic', $id)
                ->execute();
    }

}