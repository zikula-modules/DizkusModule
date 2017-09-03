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

use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;

class BbcodeHookHandler extends AbstractHookHandler
{
    /**
     * Display hook for view.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function uiView(DisplayHook $hook)
    {
        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:bbcode.view.html.twig', []);

        $this->uiResponse($hook, $content);
    }

    /**
     * Display hook for edit.
     * Display a UI interface during the creation of the hooked object.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function uiEdit(DisplayHook $hook)
    {
        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:bbcode.edit.html.twig', []);
        $this->uiResponse($hook, $content);
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

    /**
     * get the modvar settings for hook
     * generates value if not yet set.
     *
     * @param $hook
     *
     * @return array
     */
    private function getHookConfig($hook)
    {
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
        return 'provider.dizkus.ui_hooks.bbcode';
    }
}
