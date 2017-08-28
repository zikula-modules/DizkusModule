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
class PostSubBundle extends AbstractSubBundle
{
    public function __construct($title = '')
    {
        $owner = 'ZikulaDizkusModule';
        $area = 'subscriber.dizkus.ui_hooks.post';
        $category = 'ui_hooks';

        parent::__construct($owner, $area, $category, $title);

        $this->addEvent('display_view', 'dizkus.ui_hooks.post.ui_view');
        $this->addEvent('form_edit', 'dizkus.ui_hooks.post.ui_edit');
        $this->addEvent('form_delete', 'dizkus.ui_hooks.post.ui_delete');
        $this->addEvent('validate_edit', 'dizkus.ui_hooks.post.validate_edit');
        $this->addEvent('validate_delete', 'dizkus.ui_hooks.post.validate_delete');
        $this->addEvent('process_edit', 'dizkus.ui_hooks.post.process_edit');
        $this->addEvent('process_delete', 'dizkus.ui_hooks.post.process_delete');
    }
}
