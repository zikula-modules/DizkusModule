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
 * BBFilterProBundle
 *
 * @author Kaik
 */
class BBFilterProBundle extends AbstractProBundle
{
    public function __construct($title = '')
    {
        $owner = 'ZikulaDizkusModule';
        $area = 'provider.dizkus.filter_hooks.bbcode';
        $category = 'filter_hooks';

        parent::__construct($owner, $area, $category, $title);

        $this->addServiceHandler('filter', 'Zikula\DizkusModule\HookHandler\BbcodeFilterHookHandler', 'filter', 'zikula_dizkus_module.hook_handler.bbcode.filter');
    }

    public function getSettingsForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . $this->getBaseName() . 'SettingsType';
    }
}