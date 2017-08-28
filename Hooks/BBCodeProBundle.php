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
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . str_replace('ProBundle', 'Provider', str_replace('Zikula\\DizkusModule\\Hooks\\', '', get_class($this))) . 'SettingsType';
    }

//    public function getBindingForm()
//    {
//        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . str_replace('ProBundle', 'Provider', str_replace('Zikula\\DizkusModule\\Hooks\\', '', get_class($this))) . 'BindingType';
//    }
}
