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
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;
use Zikula\Bundle\HookBundle\Bundle\ProviderBundle;

class HookContainer extends AbstractHookContainer
{
    const PROVIDER_UIAREANAME = 'provider.dizkus.ui_hooks.topic';

    /**
     * Define the hook bundles supported by this module.
     *
     * @return void
     */
    protected function setupHookBundles()
    {
        // Post Subscriber Hooks
        $bundle1 = new SubscriberBundle('ZikulaDizkusModule', 'subscriber.dizkus.ui_hooks.post', 'ui_hooks', $this->__('Dizkus post hook'));
        $bundle1->addEvent('display_view', 'dizkus.ui_hooks.post.ui_view');
        $bundle1->addEvent('form_edit', 'dizkus.ui_hooks.post.ui_edit');
        $bundle1->addEvent('form_delete', 'dizkus.ui_hooks.post.ui_delete');
        $bundle1->addEvent('validate_edit', 'dizkus.ui_hooks.post.validate_edit');
        $bundle1->addEvent('validate_delete', 'dizkus.ui_hooks.post.validate_delete');
        $bundle1->addEvent('process_edit', 'dizkus.ui_hooks.post.process_edit');
        $bundle1->addEvent('process_delete', 'dizkus.ui_hooks.post.process_delete');
        $this->registerHookSubscriberBundle($bundle1);
        // Post Filter Hooks
        $bundle4 = new SubscriberBundle('ZikulaDizkusModule', 'subscriber.dizkus.filter_hooks.post', 'filter_hooks', $this->__('Dizkus post filter'));
        $bundle4->addEvent('filter', 'dizkus.filter_hooks.post.filter');
        $this->registerHookSubscriberBundle($bundle4);
        // Topic Subscriber Hooks
        $bundle2 = new SubscriberBundle('ZikulaDizkusModule', 'subscriber.dizkus.ui_hooks.topic', 'ui_hooks', $this->__('Dizkus topic hook'));
        $bundle2->addEvent('display_view', 'dizkus.ui_hooks.topic.ui_view');
        $bundle2->addEvent('form_edit', 'dizkus.ui_hooks.topic.ui_edit');
        $bundle2->addEvent('form_delete', 'dizkus.ui_hooks.topic.ui_delete');
        $bundle2->addEvent('validate_edit', 'dizkus.ui_hooks.topic.validate_edit');
        $bundle2->addEvent('validate_delete', 'dizkus.ui_hooks.topic.validate_delete');
        $bundle2->addEvent('process_edit', 'dizkus.ui_hooks.topic.process_edit');
        $bundle2->addEvent('process_delete', 'dizkus.ui_hooks.topic.process_delete');
        $this->registerHookSubscriberBundle($bundle2);
        // Forum Subscriber Hooks
        $bundle3 = new SubscriberBundle('ZikulaDizkusModule', 'subscriber.dizkus.ui_hooks.forum', 'ui_hooks', $this->__('Dizkus forum hook'));
        $bundle3->addEvent('display_view', 'dizkus.ui_hooks.forum.ui_view');
        $bundle3->addEvent('form_edit', 'dizkus.ui_hooks.forum.ui_edit');
        $bundle3->addEvent('form_delete', 'dizkus.ui_hooks.forum.ui_delete');
        $bundle3->addEvent('validate_edit', 'dizkus.ui_hooks.forum.validate_edit');
        $bundle3->addEvent('validate_delete', 'dizkus.ui_hooks.forum.validate_delete');
        $bundle3->addEvent('process_edit', 'dizkus.ui_hooks.forum.process_edit');
        $bundle3->addEvent('process_delete', 'dizkus.ui_hooks.forum.process_delete');
        $this->registerHookSubscriberBundle($bundle3);
        // Topic Provider Hooks
        $bundle5 = new ProviderBundle('ZikulaDizkusModule', self::PROVIDER_UIAREANAME, 'ui_hooks', $this->__('Dizkus topic provider hook'));
        $bundle5->addServiceHandler('display_view', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'uiView', 'dizkus.hooks.topic');
        $bundle5->addServiceHandler('form_edit', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'uiEdit', 'dizkus.hooks.topic');
        $bundle5->addServiceHandler('form_delete', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'uiDelete', 'dizkus.hooks.topic');
        $bundle5->addServiceHandler('validate_edit', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'validateEdit', 'dizkus.hooks.topic');
        $bundle5->addServiceHandler('validate_delete', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'validateDelete', 'dizkus.hooks.topic');
        $bundle5->addServiceHandler('process_edit', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'processEdit', 'dizkus.hooks.topic');
        $bundle5->addServiceHandler('process_delete', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'processDelete', 'dizkus.hooks.topic');
        $this->registerHookProviderBundle($bundle5);
    }
}
