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
         * new topic is created in two cases
         * generic new topic and split
         * mark first post by date {"post_time" = "ASC"} as first and last as last by date
         * calculate reply count based on actual collection count
         *
         * Parent forums are updated with above data
         * because of split we cannot increment and we need to compare current last post.
         *
         */
        if ($entity instanceof TopicEntity && $entity->getSyncOnSave()) {
//            dump('New topic or split');
            // get data
            $topic = $entity;
            $forum = $topic->getForum();
            $firstPost = $topic->getPosts()->first();
            $lastPost = $topic->getPosts()->last();
            $totalPosts = $topic->getPosts()->count();

            // post sync
            $firstPost->setIsFirstPost(true);
            //topic sync
            $topic->setLast_Post($lastPost);
            $topic->setReplyCount($totalPosts - 1);

            // FORUMS sync
            //update forums info
            $parents = $forum->getParents();
            $parents[] = $forum;
            foreach ($parents as $forum) {
                if ($forum->getLast_post() instanceof PostEntity && $forum->getLast_post()->getPost_time() < $lastPost->getPost_time()) {
                    $forum->setLast_post($lastPost);
                }
                $forum->incrementTopicCount();
                // we count first post as well I guess
                // this should work even in case of split
                // split new topic posts are added here but will be deducted
                // if in same forum so posts count will be actuall down the tree...
                $forum->setPostCount($forum->getPostCount() + $totalPosts);
            }
        }

        /*
         * New post
         * this is called when there is new topic
         * or reply
         *
         */
        if ($entity instanceof PostEntity) {
            // this is hopefully on thing that is certain I hope...
            $user = $entity->getPoster();
            $user->incrementPostCount();

            $topic = $entity->getTopic();
            if ($entity->isFirst()) {
//                dump('New post in new topic see new topic');
            } else {
                /*
                 * Looks like a reply
                 */
//                dump('New post (Reply) in topic');

                $forum = $topic->getForum();
                $firstPost = $topic->getPosts()->first();
                $lastPost = $topic->getPosts()->last();

                // post sync
                $firstPost->setIsFirstPost(true);
                //topic sync
                $topic->setLast_Post($lastPost);
                $topic->incrementReplyCount();

                // FORUMS sync
                //update forums info
                $parents = $forum->getParents();
                $parents[] = $forum;
                foreach ($parents as $forum) {
                    if ($forum->getLast_post() instanceof PostEntity && $forum->getLast_post()->getPost_time() < $lastPost->getPost_time()) {
                        $forum->setLast_post($lastPost);
                    }
                    $forum->incrementTopicCount();
                    $forum->incrementPostCount();
                }
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
        if ($entity instanceof TopicEntity && $entity->getSyncOnSave()) {
        }

        /*
         * New post
         */
        if ($entity instanceof PostEntity) {
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
        // see what changed
//        $changes = $uow->getEntityChangeSet($entity);
//        dump($changes);
        /*
         * Forum changed
         */
        if ($entity instanceof ForumEntity) {
//            dump('preUpdate forum');
            // its a forum or root
            $parent = $entity->getParent();
            // lets work on parent here see what it is first
            // if its objetc then we can play with it
            if ($parent instanceof ForumEntity) {
//                // for example update last post
//                if ($event->hasChangedField('last_post')) {
//                    $new_last_post_date = $event->getNewValue('last_post') instanceof PostEntity
//                        ? $event->getNewValue('last_post')->getPost_time()
//                        : null ;
//                    $parent_last_post_date = $parent->getLast_post() instanceof PostEntity
//                        ? $parent->getLast_post()->getPost_time()
//                        : null;
//                    // now when we know all the dates we can do it
//                    if ($new_last_post_date > $parent_last_post_date) {
//                        $parent->setLast_post($event->getNewValue('last_post'));
//                    }
//
//                 // end of parent sync
//                    $em->persist($parent);
//                    $md = $em->getClassMetadata(get_class($parent));
//                    $uow->recomputeSingleEntityChangeSet($md, $parent);
////                    $em->flush($parent); // <-this is bad but it works only wonder how long :)
//                }
//                // now we update topic count
//                if ($event->hasChangedField('topicCount')) {
//
//                }
            }
        }

        /*
         * Topic changed
         */
        if ($entity instanceof TopicEntity && $entity->getSyncOnSave()) {
//            dump('preUpdate topic');
//            dump($entity->getSyncOnSave());
            /*
             * Move topic action
             * There should be no action
             * that changes topics forum apart from move action
             *
             */
            if ($event->hasChangedField('forum')) {
//                dump('topic move');
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
//                dump($entity);
//                dump($entity->getPosts()->getInsertDiff());
//                dump($entity->getPosts()->getDeleteDiff());
            }
        }

        /*
         * Any post manipulation
         */
        if ($entity instanceof PostEntity) {
            // in case of split changed things are
            // isfirstpost to true (for first post) + title..
            // +
            // topic changed split move post
            // this probably will not be helfull...
//            dump('preUpdate post');
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
        if ($entity instanceof TopicEntity && $entity->getSyncOnSave()) {
        }
        if ($entity instanceof PostEntity) {
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
        if ($entity instanceof TopicEntity && $entity->getSyncOnSave()) {
        }

        /*
         * Delete post
         */
        if ($entity instanceof PostEntity) {
            if ($entity->isFirst()) {
            }
        }
    }
}
//                $topic->incrementReplyCount();
//                /*
//                 * New topic first post not a reply
//                 */
//                // TOPIC
//                //update topic info
////                $topic->setLast_Post($entity); // duplicate
//                // this is a new topic indicator
////                $entity->isFirst() ?: $topic->incrementReplyCount();
//                // USER
////                !$entity->getTopic()->getSubscribe() ?: $user->addTopicSubscription($topic);
//                // user subscription @todo add subscription module settings check
////                $entity->isFirst() ?: $user->incrementPostCount();
//            $topic->setLast_Post($entity);

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
//            // if last post changed update direct parent
////                $new_last_post = ;
//                // lets see
//                dump('testing fucking with doctrine 1');
//                dump($entity->getParent()->getLast_post());
//                dump($event->getNewValue('last_post'));
//
//                    $parent_last_post =
//                        ? $event->getNewValue('last_post')
//                        : $parent_last_post_date ;
////
////                    && ($entity->getParent()->getLast_post() < )
////                    $parent_old_last_post = $parent->getLast_post();
////                    // should we accept any last post or only latest one?
////                       if ($new_last_post > $parent_old_last_post) {
////                       }
//

//                }
//            }
            // if topic count changed update direct parent
            // if post count changed update direct parent