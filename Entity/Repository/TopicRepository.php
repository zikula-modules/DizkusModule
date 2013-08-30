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

namespace Dizkus\Entity\Repository;

class TopicRepository extends \EntityRepository
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
        $dql = 'DELETE Dizkus\Entity\TopicEntity t
            WHERE t.topic_id = :id';
        $this->_em->createQuery($dql)->setParameter('id', $id)->execute();
    }

    public function manualDeletePosts($id)
    {
        $dql = 'DELETE Dizkus\Entity\PostEntity p
            WHERE p.topic = :topic';
        $this->_em->createQuery($dql)->setParameter('topic', $id)->execute();
    }

    /**
     * Delete all subscriptions for a topic
     * $id can be the integer topic id or the Dizkus\Entity\TopicEntity object
     *
     * @param mixed int/obj $id
     */
    public function deleteTopicSubscriptions($id)
    {
        $dql = 'DELETE from Dizkus\Entity\TopicSubscriptionEntity ts
            WHERE ts.topic = :topic';
        $this->_em->createQuery($dql)->setParameter('topic', $id)->execute();
    }

    /**
     * retrieve a topic from hook parameters
     *
     * @param  Zikula\Component\HookDispatcher\Hook $hook
     * @return Dizkus\Entity\TopicEntity/NULL
     */
    public function getHookedTopic(Zikula\Component\HookDispatcher\Hook $hook)
    {
        $dql = 'SELECT a FROM Dizkus\Entity\TopicEntity a ' . 'WHERE a.hookedModule = :modulename ' . 'AND a.hookedObjectId = :objectid ' . 'AND a.hookedAreaId = :area ';
        $query = $this->_em->createQuery($dql);
        $query->setParameters(array(
            'modulename' => $hook->getCaller(),
            'objectid' => $hook->getId(),
            'area' => $hook->getAreaId()));
        try {
            $result = $query->getOneOrNullResult();
        } catch (Exception $e) {
            echo '<pre>';
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }

        return $result;
    }

}
