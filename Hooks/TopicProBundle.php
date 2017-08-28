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
class TopicProBundle extends AbstractProBundle
{
    public function __construct($title = '')
    {
        $owner = 'ZikulaDizkusModule';
        $area = 'provider.dizkus.ui_hooks.topic';
        $category = 'ui_hooks';

        parent::__construct($owner, $area, $category, $title);

        $this->addServiceHandler('display_view', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'uiView', 'zikula_dizkus_module.hook_handler.topic');
        $this->addServiceHandler('form_edit', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'uiEdit', 'zikula_dizkus_module.hook_handler.topic');
        $this->addServiceHandler('form_delete', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'uiDelete', 'zikula_dizkus_module.hook_handler.topic');
        $this->addServiceHandler('validate_edit', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'validateEdit', 'zikula_dizkus_module.hook_handler.topic');
        $this->addServiceHandler('validate_delete', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'validateDelete', 'zikula_dizkus_module.hook_handler.topic');
        $this->addServiceHandler('process_edit', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'processEdit', 'zikula_dizkus_module.hook_handler.topic');
        $this->addServiceHandler('process_delete', 'Zikula\DizkusModule\HookHandler\TopicHookHandler', 'processDelete', 'zikula_dizkus_module.hook_handler.topic');
    }

    public function getSettingsForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . str_replace('ProBundle', 'Provider', str_replace('Zikula\\DizkusModule\\Hooks\\', '', get_class($this))) . 'SettingsType';
    }

    public function getBindingForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . str_replace('ProBundle', 'Provider', str_replace('Zikula\\DizkusModule\\Hooks\\', '', get_class($this))) . 'BindingType';
    }
}
