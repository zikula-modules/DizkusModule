<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Container;

use Zikula\Bundle\HookBundle\AbstractHookContainer;
use Zikula\DizkusModule\Hooks\TopicProBundle;
use Zikula\DizkusModule\Hooks\BBCodeProBundle;
use Zikula\DizkusModule\Hooks\BBFilterProBundle;
use Zikula\DizkusModule\Hooks\ForumSubBundle;
use Zikula\DizkusModule\Hooks\TopicSubBundle;
use Zikula\DizkusModule\Hooks\PostSubBundle;
use Zikula\DizkusModule\Hooks\PostTextSubBundle;
use Zikula\DizkusModule\Hooks\PostFilterSubBundle;

class HookContainer extends AbstractHookContainer
{
    /**
     * Define the hook bundles supported by this module.
     *
     * @return void
     */
    protected function setupHookBundles()
    {
        // Topic Provider Hooks
        $this->registerHookProviderBundle(new TopicProBundle($this->__('Dizkus Topic provider')));
        // Dizkus BBCode Provider Hooks
        $this->registerHookProviderBundle(new BBCodeProBundle($this->__('Dizkus BBCode provider')));
        // Dizkus BBCode Provider Hooks
        $this->registerHookProviderBundle(new BBFilterProBundle($this->__('Dizkus BBFilter provider')));
        // Forum Subscriber Hooks
        $this->registerHookSubscriberBundle(new ForumSubBundle($this->__('Dizkus Forum subscriber')));
        // Topic Subscriber Hooks
        $this->registerHookSubscriberBundle(new TopicSubBundle($this->__('Dizkus Topic subscriber')));
        // Post Subscriber Hooks
        $this->registerHookSubscriberBundle(new PostSubBundle($this->__('Dizkus Post subscriber')));
        // Post Filter Hooks
        $this->registerHookSubscriberBundle(new PostFilterSubBundle($this->__('Dizkus PostFilter subscriber')));
        // Post textarea Subscriber Hooks
        $this->registerHookSubscriberBundle(new PostTextSubBundle($this->__('Dizkus PostText subscriber')));
    }
}
