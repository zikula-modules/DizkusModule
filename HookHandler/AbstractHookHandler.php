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

namespace Zikula\DizkusModule\HookHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\DizkusModule\Security\Permission;
use Zikula\ExtensionsModule\Api\VariableApi;

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
     * @var VariableApi
     */
    protected $variableApi;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        EngineInterface $renderEngine,
        Permission $permission,
        VariableApi $variableApi,
        TranslatorInterface $translator
    ) {
        $this->name = 'ZikulaDizkusModule';
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->renderEngine = $renderEngine;
        $this->permission = $permission;
        $this->variableApi = $variableApi;
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
