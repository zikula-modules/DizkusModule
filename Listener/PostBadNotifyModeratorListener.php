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
use Zikula\DizkusModule\Entity\PostEntity;
use Zikula\DizkusModule\Events\DizkusEvents;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\MailerModule\Api\MailerApi;

/**
 * Moderator notification listener
 */
class PostBadNotifyModeratorListener implements EventSubscriberInterface
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
    private $router;

    /**
     * Check new topic against spamers
     * Respond to event DizkusEvents::POST_NOTIFY_MODERATOR
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public static function getSubscribedEvents()
    {
        return [
            DizkusEvents::POST_NOTIFY_MODERATOR => ['notifyModerators']
        ];
    }

    /**
     * Listener constuctor
     *
     * @param TranslatorInterface $translator translator
     * @param Twig_Enviroment $twig template engine
     * @param VariableApi $variableApi VariableApi service
     * @param MailerApi $mailerApi mailer api
     * @param RouterInterface $router router
     */
    public function __construct(
        TranslatorInterface $translator,
        \Twig_Environment $twig,
        VariableApi $variableApi,
        MailerApi $mailerApi,
        RouterInterface $router
    )
    {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->variableApi = $variableApi;
        $this->mailerApi = $mailerApi;
        $this->router = $router;
        $this->settings = $this->variableApi->getAll('ZikulaDizkusModule');
    }

    /**
     * Notify moderators by email
     * Respond to event DizkusEvents::POST_NOTIFY_MODERATOR
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function notifyModerators(GenericEvent $event)
    {
        $post = $event->getSubject();
        if ($post instanceof PostEntity) {
            if($event->hasArgument('message') && $event->hasArgument('notifier')) {
                $nodeModerators = $post->getTopic()->getForum()->getAllNodeModeratorsAsUsers();
                foreach ($nodeModerators as $moderator) {
                  $this->sendModeratorPostNotification($post, $moderator, $event->getArgument('message'), $event->getArgument('notifier'));
                }

            }
        }
    }

    /**
     * Send email to moderator
     *
     * @param PostEntity $post post object
     * @param UserEntity $moderator moderator to notify
     * @param string $message notification reason
     * @param UserEntity $notifier notifier object
     * @return void
     */
    private function sendModeratorPostNotification(PostEntity $post, $moderator, $message, $notifier)
    {
        $sitename = $this->variableApi->getSystemVar('sitename', $this->variableApi->getSystemVar('sitename_en'));
        $email = \Swift_Message::newInstance()
            ->setSubject($this->translator->__f('Bad post in %topic topic on %website', ['%topic'=> $post->getTopic()->getTitle(), '%website' => $sitename]))
            ->setFrom($this->settings['email_from'] ? [$this->settings['email_from'] => $sitename] : [$this->variableApi->getSystemVar('adminmail') => $sitename])
            ->setTo($moderator->getEmail())
            ->setBody(
                $this->twig->render(
                    '@ZikulaDizkusModule/Email/post.notify.moderator.html.twig',
                    ['post' => $post, 'message' => $message, 'notifier' => $notifier]
                ),
                'text/html'
            )
            ->addPart(
                $this->twig->render(
                    '@ZikulaDizkusModule/Email/post.notify.moderator.txt.twig',
                    ['post' => $post, 'message' => $message, 'notifier' => $notifier]
                ),
                'text/plain'
            );
        $this->mailerApi->sendMessage($email);
    }
}