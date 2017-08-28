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
class PostFilterSubBundle extends AbstractSubBundle
{
    public function __construct($title = '')
    {
        $owner = 'ZikulaDizkusModule';
        $area = 'subscriber.dizkus.filter_hooks.post';
        $category = 'filter_hooks';

        parent::__construct($owner, $area, $category, $title);

        $this->addEvent('filter', 'dizkus.filter_hooks.post.filter');
    }
}