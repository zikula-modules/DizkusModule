<?php

declare(strict_types=1);

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
use Zikula\DizkusModule\Events\DizkusEvents;

/**
 * User Sync Listener
 *
 * not in use
 *
 * @author Kaik
 */
class UserSyncListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param EntityManager $entityManager EntityManager service instance
     */
    public static function getSubscribedEvents()
    {
        return [
            DizkusEvents::USER_SYNC => ['syncUser'],
        ];
    }

    /**
     * Sync forum user
     *
     * Respond to event DizkusEvents::USER_SYNC
     *
     * @param bollean $recursive
     *
     * @return void
     */
    public function syncUser(GenericEvent $event)
    {
    }
}
