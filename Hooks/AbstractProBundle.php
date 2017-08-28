<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Hooks;

use Zikula\Bundle\HookBundle\Bundle\ProviderBundle;
use Zikula\DizkusModule\Hooks\BindingsCollection;
use Zikula\DizkusModule\Hooks\BindingObject;

/**
 * Description of ProviderBundle
 *
 * @author Kaik
 */
abstract class AbstractProBundle extends ProviderBundle implements \ArrayAccess
{
    private $baseName;

    private $areaData;

    private $bindings;

    private $hooked;

    private $modules;

    private $settings;

    public function __construct($owner, $area, $category, $title)
    {
        parent::__construct($owner, $area, $category, $title);
        $this->getBaseName();

        $this->modules = new BindingsCollection();
    }

    public function getSettingsForm()
    {
        return false;
    }

    public function getBindingForm()
    {
        return false;
    }

    public function getBaseName()
    {
        $this->baseName = str_replace('ProBundle', 'Provider', str_replace('Zikula\DizkusModule\Hooks\\', '', get_class($this)));
    }

    public function setAreaData($area)
    {
        $this->areaData = $area;
    }

    public function setHooked($hooked)
    {
        $this->hooked[] = $hooked;
    }

    public function setBindings($hooked)
    {
        $this->bindings = $hooked;
    }

    public function setSettings($hooked)
    {
        $this->settings = $hooked;
    }

    public function getHookedModules()
    {
        $this->modules->clear();
        foreach ($this->bindings as $key => $value) {
            $bindingObj = new BindingObject($value);
            $settings = array_key_exists($value['sowner'], $this->settings) && array_key_exists($value['sareaid'], $this->settings[$value['sowner']])
            ? $this->settings[$value['sowner']][$value['sareaid']]
            : false ;
            $bindingObj->setSettings($settings);

            $this->modules->add($bindingObj);
        }

    }

    public function getHooked()
    {
        $this->getHookedModules();
        return $this->modules;
    }
    public function getModules()
    {
        $this->getHookedModules();
        return $this->modules;
    }


    public function offsetExists($offset){
        switch ($offset) {
            case 'title':

                return true;
            case 'modules':

                return true;
            default:

                return $this->modules->offsetExists($offset);
        }
    }

    public function offsetGet($offset){
        switch ($offset) {
            case 'title':

                return $this->getTitle();
            case 'modules':

                return $this->getModules();

            default:

                return $this->modules->offsetGet($offset);
        }
    }

    public function offsetSet($offset, $value){

        return true;
    }

    public function offsetUnset($offset){

        return true;
    }
}
