<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Hooks;

/**
 *
 * @author Kaik
 */
class BindingObject implements \ArrayAccess
{
    public $data;

    public $settings;

    public $forum;

    public function __construct($data) {

        $this->data = $data;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
        $this->data = array_merge($this->settings, $this->data);
    }

    public function offsetExists($offset) {

        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset) {

        return $this->offsetExists($offset) ? $this->data[$offset] : false;
    }

    public function offsetSet($offset, $value){

        return true;
    }

    public function offsetUnset($offset){

        return true;
    }

    public function getForum()
    {
        return $this->offsetGet('forum');
    }

    public function __toString()
    {
        return (string) $this->offsetGet('id');
    }
}
