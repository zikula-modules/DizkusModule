<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\DizkusModule\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zikula\DizkusModule\Events\DizkusEvents;

/**
 * Sync Topic Listener
 *
 * not in use
 *
 * @author Kaik
 */
class TopicSyncListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public static function getSubscribedEvents()
    {
        return [
            DizkusEvents::TOPIC_SYNC => ['syncTopic']
        ];
    }

    /**
     * @param EntityManager $entityManager EntityManager service instance
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Sync forum topic
     *
     * Respond to event DizkusEvents::TOPIC_SYNC
     *
     * @param bollean $recursive
     *
     * @return void
     */
    public function syncTopic(GenericEvent $event)
    {
    }
}
