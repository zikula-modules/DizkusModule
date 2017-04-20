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

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zikula\DizkusModule\Entity\ForumUserEntity;

class UserListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'user.account.delete' => ['deleteUser'],
        ];
    }

    /**
     * respond to event 'user.account.delete'.
     *
     * on User delete, handle associated information in Dizkus
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function deleteUser(GenericEvent $event)
    {
        $user = $event->getSubject(); // user is an array formed by UserUtil::getVars();
        // remove subscriptions - topic
        $dql = 'DELETE Zikula\DizkusModule\Entity\TopicSubscriptionEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove subscriptions - forum
        $dql = 'DELETE Zikula\DizkusModule\Entity\ForumSubscriptionEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove favorites
        $dql = 'DELETE Zikula\DizkusModule\Entity\ForumUserFavoriteEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove moderators
        $dql = 'DELETE Zikula\DizkusModule\Entity\ModeratorUserEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // change user level - unused at the moment
        $dql = 'UPDATE Zikula\DizkusModule\Entity\ForumUserEntity u
            SET u.level = :level
            WHERE u.user_id = :uid';
        $this->entityManager->createQuery($dql)
        ->setParameter('uid', $user['uid'])
        ->setParameter('level', ForumUserEntity::USER_LEVEL_DELETED)
        ->execute();
    }
}
