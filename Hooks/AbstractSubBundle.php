<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Hooks;

use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;

/**
 * Description of SubscirberBundle
 *
 * @author Kaik
 */
Abstract class AbstractSubBundle extends SubscriberBundle
{
    //put your code here
    public function getForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\' . implode('', array_map('ucfirst', explode('.', $this->getArea()))) . 'Type';
    }
}
