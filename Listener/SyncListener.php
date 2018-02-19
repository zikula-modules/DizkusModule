<?php

/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Entity\PostEntity;
use Zikula\DizkusModule\Entity\TopicEntity;

/**
 * Sync Listener
 *
 * @author Kaik
 */
class SyncListener
{
    /*
     * New item
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $upgrading = $em
            ->getRepository('ZikulaExtensionsModule:ExtensionVarEntity')
            ->findBy(['modname' => 'ZikulaDizkusModule', 'name' => 'upgrading']);
        if ($upgrading) {
            return;
        }

        $entity = $args->getEntity();

        /*
         * New topic
         */
        if (($entity instanceof TopicEntity)) {
            $entity->setLast_Post($entity->getPosts()->first());
        }

        /*
         * New post
         */
        if (($entity instanceof PostEntity)) {
            $topic = $entity->getTopic();
            $user = $entity->getPoster();
            $forum = $topic->getForum();
            // TOPIC
            //update topic info
            $topic->setLast_Post($entity);
            // this is a new topic indicator
            $entity->isFirst() ?: $topic->incrementReplyCount();
            // USER
            !$entity->getTopic()->getSubscribe() ?: $user->addTopicSubscription($topic);
            // user subscription @todo add subscription module settings check
            $entity->isFirst() ?: $user->incrementPostCount();
            // FORUMS
            //update forums info
            $parents = $forum->getParents();
            $parents[] = $forum;
            foreach ($parents as $forum) {
                !$entity->isFirst() ?: $forum->incrementTopicCount();
                $forum->incrementPostCount();
                $forum->setLast_post($entity);
            }
        }
    }

    /*
     * New item
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        /*
         * New topic
         */
        if (($entity instanceof TopicEntity)) {
        }

        /*
         * New post
         */
        if (($entity instanceof PostEntity)) {
        }
    }

    /*
     * Update item
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $em = $event->getEntityManager();
        $upgrading = $em
            ->getRepository('ZikulaExtensionsModule:ExtensionVarEntity')
            ->findBy(['modname' => 'ZikulaDizkusModule', 'name' => 'upgrading']);
        if ($upgrading) {
            return;
        }

        $entity = $event->getEntity();
        $uow = $em->getUnitOfWork();

        /*
         * Forum changed
         */
        if (($entity instanceof ForumEntity)) {

        }

        /*
         * Topic changed
         */
        if (($entity instanceof TopicEntity)) {

            /*
             * Move topic action
             */
            if ($event->hasChangedField('forum')) {
                /*
                 *  Topic old forum sync
                 *  All informations here are "old"
                 *  we just have information what WILL! change
                 */
                $fromForum = $event->getOldValue('forum');
                // posts count - moved topic post count
                $fromForum->setPostCount($fromForum->recalculatePostCount() - $entity->getReplyCount());
                // topic count - moved topic ie 1
                $fromForum->setTopicCount($fromForum->recalculateTopicCount() - 1);
                // last post from topics !not including moved topic and children forums
                $fromDql = 'SELECT p FROM Zikula\DizkusModule\Entity\PostEntity p
                    LEFT JOIN p.topic t
                    WHERE t.forum = :forum
                    AND t.id != :movedTopic
                    OR p.id IN (
                        SELECT p2.id
                        FROM Zikula\DizkusModule\Entity\ForumEntity f
                        JOIN f.last_post p2
                        WHERE f.parent = :forum
                    )
                    ORDER BY p.post_time DESC';
                $fromQuery = $em->createQuery($fromDql);
                $fromQuery->setParameter('forum', $fromForum)
                    ->setParameter('movedTopic', $entity)
                    ->setMaxResults(1);
                $fromForum->setLast_Post($fromQuery->getOneOrNullResult());
                // persist and recalculate changes
                $em->persist($fromForum);
                $md = $em->getClassMetadata(get_class($fromForum));
                $uow->recomputeSingleEntityChangeSet($md, $fromForum);

                /*
                 *  Topic new forum sync
                 */
                $toForum = $event->getNewValue('forum');
                //posts
                $toForum->setPostCount($toForum->recalculatePostCount() + $entity->getReplyCount());
                $toForum->setTopicCount($toForum->recalculateTopicCount() + 1);
                // last post from topics !including moved topic and children forums
                $toDql = 'SELECT p FROM Zikula\DizkusModule\Entity\PostEntity p
                    LEFT JOIN p.topic t
                    WHERE t.forum = :forum
                    OR t.id = :movedTopic
                    OR p.id IN (
                        SELECT p2.id
                        FROM Zikula\DizkusModule\Entity\ForumEntity f
                        JOIN f.last_post p2
                        WHERE f.parent = :forum
                    )
                    ORDER BY p.post_time DESC';
                $toQuery = $em->createQuery($toDql);
                $toQuery->setParameter('forum', $toForum)
                    ->setParameter('movedTopic', $entity)
                    ->setMaxResults(1);
                $toForum->setLast_Post($toQuery->getOneOrNullResult());
                // persist and recalculate changes
                $event->setNewValue('forum', $toForum);
                $meta = $em->getClassMetadata(get_class($toForum));
                $uow->recomputeSingleEntityChangeSet($meta, $toForum);
            }
        }

        /*
         * Any post manipulation
         */
        if (($entity instanceof PostEntity)) {
            // topic changed? split? move post?
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();
        /*
         * Any manipulation on topic
         */
        if (($entity instanceof TopicEntity)) {
        }
        if (($entity instanceof PostEntity)) {
            if ($entity->isFirst()) {
            }
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        /*
         * Delete topic
         */
        if (($entity instanceof TopicEntity)) {
        }
        /*
         * Delete post
         */
        if (($entity instanceof PostEntity)) {
            if ($entity->isFirst()) {
            }
        }
    }
}




//              Pre update topic sync?... to check
//            if ($entity->getSyncOnSave()) {
//                $postsCount = $entity->getPosts()->count();
//                if ($postsCount > 1) {
//                    $this->replyCount = $postsCount - 1;
//
//                    $posts = $entity->getPosts()
//                                ->matching(
//                                    Criteria::create()
//                                    ->orderBy(['post_time' => Criteria::DESC])
//                                    ->setMaxResults(1)
//                                );
//                    $entity->setLast_Post($posts->first());
//                }
//            }
//                $queryTopics = $em->createQueryBuilder()
//                    ->select('p')
//                    ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
//                    ->leftJoin('p.topic', 't')
//                    ->where('t.forum = :forumId')
//                    ->andWhere('t.id != :movedTopic')
//                    ->setParameter('forumId', $fromForum)
//                    ->setParameter('movedTopic', $entity)
//                    ->orderBy('p.post_time', 'DESC')
//                    ->setMaxResults(1)
//                    ->getQuery();
//
//                $fromForumTopicsLastPost = $queryTopics->getOneOrNullResult();
//    private function getForumTopicsLastPost($em, $forum, $topic)
//    {
//        // get the most recent topic posts in the forum
//        $queryTopics = $em
//            ->createQueryBuilder()
//            ->select('p')
//            ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
//            ->leftJoin('p.topic', 't')
//            ->where('t.forum = :forumId')
//            ->setParameter('forumId', $forum)
//            ->addOrderBy('p.post_time', 'DESC')
//            ->getQuery();
//
//        $queryTopics->setMaxResults(1);
//        $forumTopicsLastPost = $queryTopics->getOneOrNullResult();
//
//        dump($forumTopicsLastPost);
//
////        $queryForums = $em
////            ->createQueryBuilder()
////            ->select('p')
////            ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
////            ->leftJoin('p.topic', 't')
////            ->where('t.forum = :forumId')
////            ->setParameter('forumId', $forum)
////            ->addOrderBy('p.post_time', 'DESC')
////            ->getQuery();
////
////        $queryForums->setMaxResults(1);
////        $forumChildrenLastPost = $queryForums->getOneOrNullResult();
////
////        if (!is_null($forumChildrenLastPost) && !is_null($forumTopicsLastPost)) {
////            $forumLastPost =
////        }
//
//
//
////        $forumLastPost =
////            ?
////            :
////            ;
//
//        return $forumTopicsLastPost;
//    }
//              dump($fromForumChildrenLastPost);
//                if (is_null($fromForumChildrenLastPost)) {
//
//
//                }
//                $query->setParameters([
//                    'modulename' => $hook->getCaller(),
//                    'objectid' => $hook->getId(),
//                    'area' => $hook->getAreaId()]);
//                $dql = 'SELECT p FROM Zikula\DizkusModule\Entity\PostEntity p
//                    JOIN Zikula\DizkusModule\Entity\ForumEntity f
//                    WITH 1 = 1
//                    JOIN f.
//                    WHERE a.hookedModule = :modulename
//                    AND a.hookedObjectId = :objectid
//                    AND a.hookedAreaId = :area ';
//                $query = $this->_em->createQuery($dql);
//                $query->setParameters([
//                    'modulename' => $hook->getCaller(),
//                    'objectid' => $hook->getId(),
//                    'area' => $hook->getAreaId()]);
//
//                $result = $query->getOneOrNullResult();

                // last post from direct children
//                $em->createQueryBuilder()
//                    ->select('p')
//                    ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
//                    ->innerJoin('c.registrations', 'r')
//                    ->where('r.player = :player')
//                    ->setParameter('player', $playerId)
//                $queryTopics = $em->createQueryBuilder()
//                    ->select('p')
//                    ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
//                    ->from('Zikula\DizkusModule\Entity\ForumEntity', 'f')
////                    ->leftJoin('p.topic', 't')
//                    ->where('f.forum = :forumId')
//                    ->setParameter('forumId', $fromForum)
//                    ->setParameter('movedTopic', $entity)
//                    ->addOrderBy('p.post_time', 'DESC')
//                    ->setMaxResults(1)
//                    ->getQuery();