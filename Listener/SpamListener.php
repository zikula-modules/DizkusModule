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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\Events\DizkusEvents;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Description of SpamListener
 *
 * @author Kaik
 */
class SpamListener implements EventSubscriberInterface
{
    /**
     * @var VariableApi
     */
    protected $variableApi;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var RouterInterface
     */
    protected $router;

    public static function getSubscribedEvents()
    {
        return [
            DizkusEvents::TOPIC_PREPARE => ['newTopicCheck'],
            DizkusEvents::POST_PREPARE => ['newPostCheck']
        ];
    }

    /**
     * @param VariableApi $variableApi VariableApi service instance
     * @param MailerApi $mailerApi
     */
    public function __construct(VariableApi $variableApi, TranslatorInterface $translator, RouterInterface $router)
    {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * Check new topic against spamers
     * Respond to event DizkusEvents::TOPIC_PREPARE
     *
     * @return void
     */
    public function newTopicCheck(GenericEvent $event)
    {
    }

    /**
     * Check new topic against spamers
     * Respond to event DizkusEvents::TOPIC_PREPARE
     *
     * @return void
     */
    public function newPostCheck(GenericEvent $event)
    {
    }
}
