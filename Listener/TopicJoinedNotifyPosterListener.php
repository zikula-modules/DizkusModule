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
use Zikula\DizkusModule\Entity\PostEntity;
use Zikula\DizkusModule\Events\DizkusEvents;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\MailerModule\Api\MailerApi;

/**
 * Moderator notification listener
 */
class TopicJoinedNotifyPosterListener implements EventSubscriberInterface
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
     * Respond to event DizkusEvents::POST_DELETE
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public static function getSubscribedEvents()
    {
        return [
            DizkusEvents::POST_MOVE => ['notifyPoster']
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
    ) {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->variableApi = $variableApi;
        $this->mailerApi = $mailerApi;
        $this->router = $router;
        $this->settings = $this->variableApi->getAll('ZikulaDizkusModule');
    }

    /**
     * Notify poster by email about deleted post
     *
     * @return void
     */
    public function notifyPoster(GenericEvent $event)
    {
        $post = $event->getSubject();
        if ($post instanceof PostEntity) {
            if ($event->hasArgument('reason') && $event->getArgument('reason')) {
                $this->sendPosterPostNotification($post, $event->getArgument('reason'));
            }
        }
    }

    /**
     * Send email to user
     *
     * @param PostEntity $post post object
     * @param string $message notification reason
     *
     * @return void
     */
    private function sendPosterPostNotification(PostEntity $post, $message)
    {
        $sitename = $this->variableApi->getSystemVar('sitename', $this->variableApi->getSystemVar('sitename_en'));
        $email = \Swift_Message::newInstance()
            ->setSubject($this->translator->__f('Your post in topic %topic on %website was moved', ['%topic'=> $post->getTopic()->getTitle(), '%website' => $sitename]))
            ->setFrom($this->settings['email_from'] ? [$this->settings['email_from'] => $sitename] : [$this->variableApi->getSystemVar('adminmail') => $sitename])
            ->setTo($post->getPoster()->getEmail())
            ->setBody(
                $this->twig->render(
                    '@ZikulaDizkusModule/Email/post.moved.notify.poster.html.twig',
                    ['post' => $post, 'message' => $message]
                ),
                'text/html'
            )
            ->addPart(
                $this->twig->render(
                    '@ZikulaDizkusModule/Email/post.moved.notify.poster.txt.twig',
                    ['post' => $post, 'message' => $message]
                ),
                'text/plain'
            );
        $this->mailerApi->sendMessage($email);
    }
}
