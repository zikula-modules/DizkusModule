<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Hooks;

/**
 * Description of TopicProBundle
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
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . str_replace('ProBundle', 'Provider', str_replace('Zikula\\DizkusModule\\Hooks\\', '', get_class($this))) . 'SettingsType';
    }

    public function getBindingForm()
    {
        return false;//'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . $this->baseName . 'BindingType';
    }


}
