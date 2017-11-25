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

use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Bundle\HookBundle\ServiceIdTrait;

/**
 * BBCodeProBundle
 *
 * @author Kaik
 */
class BBCodeProBundle extends AbstractProBundle
{
    use ServiceIdTrait;

    private $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
        parent::__construct();
    }

    public function getOwner()
    {
        return 'ZikulaDizkusModule';
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
        ];
    }

    public function getSettingsForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . $this->getBaseName() . 'SettingsType';
    }

//    public function getBindingForm()
//    {
//        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . $this->getBaseName() . 'BindingType';
//    }

    public function view(DisplayHook $hook)
    {
    }
}
//    /**
//     * Display hook for view.
//     *
//     * @param DisplayHook $hook the hook
//     *
//     * @return string
//     */
//    public function uiView(DisplayHook $hook)
//    {
//        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:bbcode.view.html.twig', []);
//
//        $this->uiResponse($hook, $content);
//    }
//
//    /**
//     * Display hook for edit.
//     * Display a UI interface during the creation of the hooked object.
//     *
//     * @param DisplayHook $hook the hook
//     *
//     * @return string
//     */
//    public function uiEdit(DisplayHook $hook)
//    {
//        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:bbcode.edit.html.twig', []);
//        $this->uiResponse($hook, $content);
//    }
//
//    /**
//     * Process hook for edit.
//     *
//     * @param ProcessHook $hook the hook
//     *
//     * @return bool
//     */
//    public function processEdit(ProcessHook $hook)
//    {
//        //        return true;
//    }

//    public function __construct($title = '')
//    {
//        $owner = 'ZikulaDizkusModule';
//        $area = 'provider.dizkus.ui_hooks.bbcode';
//        $category = 'ui_hooks';
//
//        parent::__construct($owner, $area, $category, $title);
//
//        $this->addServiceHandler('display_view', 'Zikula\DizkusModule\HookHandler\BbcodeHookHandler', 'uiView', 'zikula_dizkus_module.hook_handler.bbcode');
//        $this->addServiceHandler('form_edit', 'Zikula\DizkusModule\HookHandler\BbcodeHookHandler', 'uiEdit', 'zikula_dizkus_module.hook_handler.bbcode');
//        $this->addServiceHandler('process_edit', 'Zikula\DizkusModule\HookHandler\BbcodeHookHandler', 'processEdit', 'zikula_dizkus_module.hook_handler.bbcode');
//    }
