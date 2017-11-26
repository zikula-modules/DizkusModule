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

namespace Zikula\DizkusModule\Hooks;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\HookBundle\HookSelfAllowedProviderInterface;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\ServiceIdTrait;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * BBCodeProBundle
 *
 * @author Kaik
 */
class BBCodeProBundle extends AbstractProBundle implements HookSelfAllowedProviderInterface
{
    use ServiceIdTrait;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EngineInterface
     */
    protected $renderEngine;

    /**
     * @var VariableApi
     */
    private $variableApi;

    private $area = 'provider.dizkus.ui_hooks.bbcode';

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        RequestStack $requestStack,
        EngineInterface $renderEngine,
        VariableApi $variableApi
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->renderEngine = $renderEngine;
        $this->variableApi = $variableApi;

        parent::__construct();
    }

    public function getCategory()
    {
        return UiHooksCategory::NAME;
    }

    public function getTitle()
    {
        return $this->translator->__('Dizkus BBCode Provider');
    }

    public function getProviderTypes()
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'view',
            UiHooksCategory::TYPE_FORM_EDIT => 'edit',
        ];
    }

    public function getSettingsForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\BBCodeProviderSettingsType';
    }

    /**
     * Display hook for view.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function view(DisplayHook $hook)
    {
        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:bbcode.view.html.twig', []);
        $hook->setResponse(new DisplayHookResponse('provider.dizkus.ui_hooks.bbcode', $content));
    }

    /**
     * Display hook for edit.
     * Display a UI interface during the creation of the hooked object.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function edit(DisplayHook $hook)
    {
        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:bbcode.edit.html.twig', []);
        $hook->setResponse(new DisplayHookResponse('provider.dizkus.ui_hooks.bbcode', $content));
    }

    /**
     * Process hook for edit.
     *
     * @param ProcessHook $hook the hook
     *
     * @return bool
     */
    public function processEdit(ProcessHook $hook)
    {
        //        return true;
    }
}
