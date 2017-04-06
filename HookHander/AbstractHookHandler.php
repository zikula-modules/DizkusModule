<?php
/**
 * Copyright 2013 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\DizkusModule\HookHandler;

use Zikula\DizkusModule\Security\Permission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;

/**
 * Provides convenience methods for hook handling.
 */
abstract class AbstractHookHandler
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EngineInterface
     */
    protected $renderEngine;

    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
    EntityManagerInterface $entityManager, RequestStack $requestStack, EngineInterface $renderEngine, Permission $permission, TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->renderEngine = $renderEngine;
        $this->permission = $permission;
        $this->translator = $translator;
    }

    /**
     * Generates a Hook response using the given content.
     *
     * @param DisplayHook $hook
     * @param string      $content
     */
    public function uiResponse(DisplayHook $hook, $content)
    {
        $hook->setResponse(new DisplayHookResponse($this->getProvider(), $content));
    }

    /**
     * @return string
     */
    public function getType()
    {
        $class = get_class($this);

        return lcfirst(substr($class, strrpos($class, '\\') + 1, -strlen('HookHandler')));
    }

    /**
     * @return string
     */
    protected function getProvider()
    {
        return 'provider.dizkus.ui_hooks.' . $this->getType();
    }
}
