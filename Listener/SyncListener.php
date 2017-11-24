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

//use Doctrine\Common\Collections\Criteria;
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
    public function prePersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $upgrading = $em
            ->getRepository('ZikulaExtensionsModule:ExtensionVarEntity')
            ->findBy(['modname' => 'ZikulaDizkusModule', 'name' => 'upgrading']);
//        dump($upgrading);
        if($upgrading) {
            return;
        }

        $entity = $args->getEntity();
        /*
         * New topic we can assume post event will handle everything
         */
        if (($entity instanceof TopicEntity)) {
            $entity->setLast_Post($entity->getPosts()->first());
        }

        /*
         * New post comes with updates
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
         * New post comes with new topic
         */
        if (($entity instanceof PostEntity)) {
        }
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $em = $event->getEntityManager();
        $upgrading = $em
            ->getRepository('ZikulaExtensionsModule:ExtensionVarEntity')
            ->findBy(['modname' => 'ZikulaDizkusModule', 'name' => 'upgrading']);
//        dump($upgrading);
        if($upgrading) {
            return;
        }
        $entity = $event->getEntity();
        $uow = $em->getUnitOfWork();
        /*
         * Forum changed
         */
        if (($entity instanceof ForumEntity)) {
            if ($event->hasChangedField('last_post')) {
            }
        }
        /*
         * Any manipulation on topic
         */
        if (($entity instanceof TopicEntity)) {
            if ($event->hasChangedField('forum')) {
                $fromForum = $event->getOldValue('forum');
                $fromForum->setLast_Post(null);
                $fromForum->setPostCount($fromForum->recalculatePostCount());
                $fromForum->setTopicCount($fromForum->recalculateTopicCount() - 1);
                $em->persist($fromForum);
                $md = $em->getClassMetadata(get_class($fromForum));
                $uow->recomputeSingleEntityChangeSet($md, $fromForum);

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
