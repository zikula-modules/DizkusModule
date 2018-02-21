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
 * @todo use doctrine tracking policy and handle the behaviour explicitly https://stackoverflow.com/a/48372531
 *
 * At the moment this is a little bit in a mess because of doctrine default listener
 * behaviour and this is a default doctrine listener.
 *
 * Using doctrine tracking policy could help to DRY this code
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
//        dump('pre persist');
//        dump($args);
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
            dump('New topic');
//            $entity->setLast_Post($entity->getPosts()->first());
            dump($entity->getSyncOnSave());
        }

        /*
         * New post
         */
        if (($entity instanceof PostEntity)) {
            $topic = $entity->getTopic();
            $user = $entity->getPoster();
            $forum = $topic->getForum();
            if ($entity->isFirst()) {
                dump('New post in new topic');
                /*
                 * New topic first post not a reply
                 */
                // TOPIC
                //update topic info
//                $topic->setLast_Post($entity); // duplicate
                // this is a new topic indicator
//                $entity->isFirst() ?: $topic->incrementReplyCount();
                // USER
                !$entity->getTopic()->getSubscribe() ?: $user->addTopicSubscription($topic);
                // user subscription @todo add subscription module settings check
//                $entity->isFirst() ?: $user->incrementPostCount();
                $user->incrementPostCount();

            } else {
                /*
                 * Looks like a reply
                 */
                dump('New post (Reply) in topic');
                $topic->incrementReplyCount();
            }

            $topic->setLast_Post($entity);
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
//        dump('postPersist');
//        dump($args);
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
//        dump('preUpdate');
//        dump($event);
        $em = $event->getEntityManager();
        $upgrading = $em
            ->getRepository('ZikulaExtensionsModule:ExtensionVarEntity')
            ->findBy(['modname' => 'ZikulaDizkusModule', 'name' => 'upgrading']);
        if ($upgrading) {
            return;
        }

        $entity = $event->getEntity();
        $uow = $em->getUnitOfWork();
        dump($uow->getEntityChangeSet($entity)); // see what changed

        /*
         * Forum changed
         */
        if (($entity instanceof ForumEntity)) {
            dump('preUpdate forum');
            // if last post changed update direct parent
            // if topic count changed update direct parent
            // if post count changed update direct parent

        }

        /*
         * Topic changed
         */
        if (($entity instanceof TopicEntity)) {
            dump('preUpdate topic');
            dump($entity->getSyncOnSave());
            /*
             * Move topic action
             */
            if ($event->hasChangedField('forum')) {
                dump('topic move');
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

            if ($entity->getPosts()->isDirty()) {
                dump($entity);
                dump($entity->getPosts()->getInsertDiff());
            }

//              Pre update topic sync force on update
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
        }

        /*
         * Any post manipulation
         */
        if (($entity instanceof PostEntity)) {
            // topic changed? split? move post?
            dump('preUpdate post');
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
//        dump('postUpdate');
//        dump($args);
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
//        dump('postRemove');
//        dump($args);
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
