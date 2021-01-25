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
use Zikula\DizkusModule\Entity\TopicSubscriptionEntity;
use Zikula\DizkusModule\Events\DizkusEvents;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\MailerModule\Api\MailerApi;

/**
 * Description of SpamListener
 *
 * @author Kaik
 */
class TopicSubscriptionListener implements EventSubscriberInterface
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
            DizkusEvents::POST_CREATE => ['notifyTopicSubscribers']
        ];
    }

    /**
     * @param VariableApi $variableApi VariableApi service instance
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
     * Check new topic against spamers
     * Respond to event DizkusEvents::POST_CREATE
     *
     * @return void
     */
    public function notifyTopicSubscribers(GenericEvent $event)
    {
        if (!$this->settings['topic_subscriptions_enabled']) {
            return;
        }
        $post = $event->getSubject();
        if ($post instanceof PostEntity) {
            if (!$post->isFirst()) {
                $topic = $post->getTopic();
                $subscriptions = $topic->getSubscriptions();
                if ($subscriptions->isEmpty()) {
                    return;
                }
                foreach ($subscriptions as $subscription) {
                    $this->sendNewPostNotification($post, $subscription);
                }
            }
        }
    }

    private function sendNewPostNotification(PostEntity $post, TopicSubscriptionEntity $subscription)
    {
        $sitename = $this->variableApi->getSystemVar('sitename', $this->variableApi->getSystemVar('sitename_en'));
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->__f('New post in %topic topic on %website', ['%topic'=> $post->getTopic()->getTitle(), '%website' => $sitename]))
            ->setFrom($this->settings['email_from'] ? [$this->settings['email_from'] => $sitename] : [$this->variableApi->getSystemVar('adminmail') => $sitename])
            ->setTo($subscription->getForumUser()->getEmail())
            ->setBody(
                $this->twig->render(
                    '@ZikulaDizkusModule/Email/topic.subscription.post.new.html.twig',
                    ['post' => $post]
                ),
                'text/html'
            )
            ->addPart(
                $this->twig->render(
                    '@ZikulaDizkusModule/Email/topic.subscription.post.new.txt.twig',
                    ['post' => $post]
                ),
                'text/plain'
            );
        $this->mailerApi->sendMessage($message);
    }
}
