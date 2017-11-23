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

use Doctrine\Common\Collections\Criteria;

/**
 * Sync Listener
 *
 * @author Kaik
 */
class SyncListener //implements EventSubscriberInterface
{
    public function prePersist(LifecycleEventArgs $args)
    {
//        dump('pre persist');
        $em = $args->getEntityManager();
        $entity = $args->getEntity();
//        dump($entity);
        /*
         * New topic we can assume post event will handle everything
         */
        if (($entity instanceof TopicEntity)) {
            $entity->setLast_Post($entity->getPosts()->first());
        }

        /*
         * New post comes with new topic
         */
        if (($entity instanceof PostEntity)) {
            $topic = $entity->getTopic();
            $user = $entity->getPoster();
            $forum = $topic->getForum();

            // TOPIC
            //update topic info
            $topic->setLast_Post($entity);
            $entity->isFirst() ?: $topic->incrementReplyCount();
//            $em->persist($topic);

            // USER
            // this is new topic indicator user subscription @todo add subscription settings check
            !$entity->getTopic()->getSubscribe() ?: $user->addTopicSubscription($topic);
            $entity->isFirst() ?: $user->incrementPostCount();
//            $em->persist($user);
//            dump($entity->getTopic()->getSubscribe());
            // FORUMS
            //update forums info
            $parents = $forum->getParents();
            $parents[] = $forum;
            foreach ($parents as $forum) {
                // this is new topic indicator
                !$entity->isFirst() ?: $forum->incrementTopicCount();
                $forum->incrementPostCount();
                $forum->setLast_post($entity);
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();
//        dump('post persist');
//        dump($entity);
        /*
         * New topic
         */
        if (($entity instanceof TopicEntity)) {
//
        }
        /*
         * New post comes with new topic
         */
        if (($entity instanceof PostEntity)) {
        }
    }


    public function preUpdate(PreUpdateEventArgs $event)
    {
        $em = $event->getEntityManager();
        $entity = $event->getEntity();
        $uow = $em->getUnitOfWork();

        /*
         * Forum changed
         */
        if (($entity instanceof ForumEntity)) {
//            dump('pre update forum');
            //last post mark recalculate
            if ($event->hasChangedField('last_post')) {
//                dump('pre update forum last post changed');
//                //old forum mark for recalculation
//                $fromForum = $event->getOldValue('forum');
//                $fromForum->setLast_Post(null);
//                $em->persist($fromForum);
//                $md = $em->getClassMetadata(get_class($fromForum));
//                $uow->recomputeSingleEntityChangeSet($md, $fromForum);
//
//                //new forum mark for recalculation
//                $toForum = $event->getNewValue('forum');
//                $toForum->setLast_Post(null);
//                $event->setNewValue('forum', $toForum);
//                $meta = $em->getClassMetadata(get_class($toForum));
//                $uow->recomputeSingleEntityChangeSet($meta, $toForum);

            }

        }

        /*
         * Any manipulation on topic
         */
        if (($entity instanceof TopicEntity)) {
            if ($event->hasChangedField('forum')) {
//                dump('pre update topic move');
                //old forum mark for recalculation
                $fromForum = $event->getOldValue('forum');
                $fromForum->setLast_Post(null);
                $fromForum->setPostCount($fromForum->recalculatePostCount());
                $fromForum->setTopicCount($fromForum->recalculateTopicCount() - 1);
                $em->persist($fromForum);
                $md = $em->getClassMetadata(get_class($fromForum));
                $uow->recomputeSingleEntityChangeSet($md, $fromForum);

                //new forum mark for recalculation
                $toForum = $event->getNewValue('forum');
                $toForum->setLast_Post(null);
                $toForum->setPostCount($toForum->recalculatePostCount());
                $toForum->setTopicCount($toForum->recalculateTopicCount() + 1);
                $event->setNewValue('forum', $toForum);
                $meta = $em->getClassMetadata(get_class($toForum));
                $uow->recomputeSingleEntityChangeSet($meta, $toForum);
            }

//            dump($entity->getPosts()->isDirty());

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
//            dump('post update post');
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();
//        dump($entity);
        /*
         * Any manipulation on topic
         */
        if (($entity instanceof TopicEntity)) {
//            dump('post update topic');
            //check if posts collection changed
//            dump($entity->getPosts()->isDirty());
        }
        if (($entity instanceof PostEntity)) {
//                dump('post update post');
                // this is new topic indicator
                if ($entity->isFirst()) {
                    //editing first post considered as topic manipulation
                }
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
//        dump('post remove');
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
                // this is topic indicator
                if ($entity->isFirst()) {
                    //deleting first post considered as topic manipulation
                }
        }
    }
}
