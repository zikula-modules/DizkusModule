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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\Component\HookDispatcher\Hook;

class TopicRepository extends EntityRepository
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
        $dql = 'DELETE Zikula\DizkusModule\Entity\TopicEntity t
            WHERE t.topic_id = :id';
        $this->_em->createQuery($dql)->setParameter('id', $id)->execute();
    }

    public function manualDeletePosts($id)
    {
        $dql = 'DELETE Zikula\DizkusModule\Entity\PostEntity p
            WHERE p.topic = :topic';
        $this->_em->createQuery($dql)->setParameter('topic', $id)->execute();
    }

    /**
     * Delete all subscriptions for a topic
     * $id can be the integer topic id or the Zikula\Module\DizkusModule\Entity\TopicEntity object
     *
     * @param mixed int/obj $id
     */
    public function deleteTopicSubscriptions($id)
    {
        $dql = 'DELETE from Zikula\DizkusModule\Entity\TopicSubscriptionEntity ts
            WHERE ts.topic = :topic';
        $this->_em->createQuery($dql)->setParameter('topic', $id)->execute();
    }

    /**
     * Find last post by post_time and set as topic last post
     */
    public function resetLastPost($topic, $flush = false)
    {
        //        $query = $this->_em->createQueryBuilder();
//        $post = $query->select('p')
//            ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
//            ->where('p.topic =:topic')
//            ->setParameter('topic', $topic->getId())
//            ->orderBy('p.post_time', 'DESC')
//            ->setMaxResults(1)
//            ->getFirstResult();
//        // set
//        dump($post);
//        $topic->setLast_post($post);
        // update topic time ?? @todo consider remove
        //$topic->setTopic_time($post->getPost_time());

        if ($flush) {
            $this->_em->flush();
        }

        return $this;
    }

    /**
     * Get Topics
     *
     * @param $since int topics since (hours)
     * @param $unanswered bool true topics with no answers
     * @param $unsolved bool true unsolved topics
     * @param $page int limit listing page (default=1)
     * @param $limit int limit listing (default=25)
     *
     *
     * @return array (postings, mail2forumpostings, rsspostings, text_to_display)
     */
    public function getTopics($since = null, $unanswered = false, $unsolved = false, $page = 1, $limit = 25)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('t', 'l')
            ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
            ->leftJoin('t.last_post', 'l')
            ->orderBy('l.post_time', 'DESC');
        // sql part per selected time frame
        switch ($since) {
            case null:
                break;
            case '24':
                // today
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', new \DateTime('today'));
                break;
            case '48':
                // since yesterday
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', new \DateTime('yesterday'));
                break;
            case '168':
                // lastweek
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', new \DateTime('-1 week'));
                break;
            default:
                // since
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', (new \DateTime())->modify('-'. $since .' hours'));
        }

        if ($unanswered) {
            $qb->andWhere('t.replyCount = 0');
        }

        if ($unsolved) {
            $qb->andWhere('t.solved = :status')->setParameter('status', -1);
        }

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)->setMaxResults($limit);
        $topics = new Paginator($qb);
        $pager = [
            'numitems'     => $topics->count(),
            'itemsperpage' => $limit, ];

        return [$topics, $pager];
    }

    /**
     * Retrieve a topic from hook parameters
     *
     * @param  Hook $hook
     * @return TopicEntity/NULL
     */
    public function getHookedTopic(Hook $hook)
    {
        $dql = 'SELECT a FROM Zikula\DizkusModule\Entity\TopicEntity a
            WHERE a.hookedModule = :modulename
            AND a.hookedObjectId = :objectid
            AND a.hookedAreaId = :area ';
        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'modulename' => $hook->getCaller(),
            'objectid' => $hook->getId(),
            'area' => $hook->getAreaId()]);
        try {
            $result = $query->getOneOrNullResult();
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
}
