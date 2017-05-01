<?php

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
use Zikula\MailerModule\Api\MailerApi;

/**
 * Description of SpamListener
 *
 * @author Kaik
 */
class ForumSubscriptionListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var MailerApi
     */
    private $mailerApi;

    /**
     * @var RouterInterface
     */
    protected $router;

    public static function getSubscribedEvents()
    {
        return [
            DizkusEvents::TOPIC_CREATE => ['mailForumSubscribers'],
        ];
    }

    /**
     * @param VariableApi $variableApi VariableApi service instance
     * @param TranslatorInterface $translator
     * @param MailerApi $mailerApi
     * @param RouterInterface $router
     */
    public function __construct(
        TranslatorInterface $translator,
        \Twig_Environment $twig,
        VariableApi $variableApi,
        MailerApi $mailerApi,
        RouterInterface $router
    ) {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->variableApi = $variableApi;
        $this->mailerApi = $mailerApi;
        $this->router = $router;
    }

    /**
     * Mail forum subscribers about new topic
     * Respond to event DizkusEvents::TOPIC_CREATE
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function mailForumSubscribers(GenericEvent $event)
    {
    }
}
