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

/**
 * BBCodeProBundle
 *
 * @author Kaik
 */
class BBCodeProBundle extends AbstractProBundle
{
    public function __construct($title = '')
    {
        $owner = 'ZikulaDizkusModule';
        $area = 'provider.dizkus.ui_hooks.bbcode';
        $category = 'ui_hooks';

        parent::__construct($owner, $area, $category, $title);

        $this->addServiceHandler('display_view', 'Zikula\DizkusModule\HookHandler\BbcodeHookHandler', 'uiView', 'zikula_dizkus_module.hook_handler.bbcode');
        $this->addServiceHandler('form_edit', 'Zikula\DizkusModule\HookHandler\BbcodeHookHandler', 'uiEdit', 'zikula_dizkus_module.hook_handler.bbcode');
        $this->addServiceHandler('process_edit', 'Zikula\DizkusModule\HookHandler\BbcodeHookHandler', 'processEdit', 'zikula_dizkus_module.hook_handler.bbcode');
    }

    public function getSettingsForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . $this->getBaseName() . 'SettingsType';
    }
}
