<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\EventHandlers;

use Zikula\Core\Event\GenericEvent;
use Zikula\Module\DizkusModule\Entity\ForumUserEntity;

class SystemListeners
{
    /**
     * Event: 'user.account.delete'.
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function deleteUser(GenericEvent $event)
    {
        $em = \ServiceUtil::get('doctrine.entitymanager');
        $user = $event->getSubject(); // user is an array formed by UserUtil::getVars();
        // remove subscriptions - topic
        $dql = 'DELETE Zikula\Module\DizkusModule\Entity\TopicSubscriptionEntity u
            WHERE u.forumUser = :uid';
        $em->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove subscriptions - forum
        $dql = 'DELETE Zikula\Module\DizkusModule\Entity\ForumSubscriptionEntity u
            WHERE u.forumUser = :uid';
        $em->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove favorites
        $dql = 'DELETE Zikula\Module\DizkusModule\Entity\ForumUserFavoriteEntity u
            WHERE u.forumUser = :uid';
        $em->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove moderators
        $dql = 'DELETE Zikula\Module\DizkusModule\Entity\ModeratorUserEntity u
            WHERE u.forumUser = :uid';
        $em->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // change user level - unused at the moment
        $dql = 'UPDATE Zikula\Module\DizkusModule\Entity\ForumUserEntity u
            SET u.level = :level
            WHERE u.forumUser = :uid';
        $em->createQuery($dql)
            ->setParameter('uid', $user['uid'])
            ->setParameter('level', ForumUserEntity::USER_LEVEL_DELETED)
            ->execute();
    }
}