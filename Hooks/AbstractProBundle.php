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

use Doctrine\Common\Collections\ArrayCollection;
use Zikula\Bundle\HookBundle\HookSelfAllowedProviderInterface;

/**
 * AbstractProBundle
 *
 * @author Kaik
 */
abstract class AbstractProBundle implements HookSelfAllowedProviderInterface, \ArrayAccess
{
    private $baseName;

    private $areaData;

    private $modules;

    private $settings = [];

    public function __construct()
    {
        $this->baseName= str_replace('ProBundle', 'Provider', str_replace('Zikula\DizkusModule\Hooks\\', '', get_class($this)));
        $this->modules = new ArrayCollection();
    }

    public function getSettingsForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\ProviderSettingsType';
    }

    public function getBindingForm()
    {
        return false;
    }

    public function getBaseName()
    {
        return $this->baseName;
    }

    public function setAreaObject($area)
    {
        $this->areaData = $area;
    }

    public function setSettings($hooked)
    {
        $this->settings = $hooked;
    }

    public function setModules($modules)
    {
        $this->modules = $modules;

        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function offsetExists($offset)
    {
        switch ($offset) {
            case 'modules':

                return true;
            default:
                if (true === strpos($offset, 'Module')) {
                    return $this->modules->offsetExists($offset);
                } else {
                    return array_key_exists($offset, $this->settings);
                }
        }
    }

    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'modules':

                return $this->getModules();
            default:
                if (true === strpos($offset, 'Module')) {
                    return $this->modules->offsetGet($offset);
                } else {
                    return $this->offsetExists($offset) ? $this->settings[$offset] : false;
                }
        }
    }

    public function offsetSet($offset, $value)
    {
        switch ($offset) {
            case 'modules':

                return $this->setModules($value);

            default:
                if (true === strpos($offset, 'Module')) {
                    return $this->modules->offsetSet($offset, $value);
                } else {
                    return $this->offsetExists($offset) ? $this->settings[$offset] = $value : false;
                }
        }
    }

    public function offsetUnset($offset)
    {
        switch ($offset) {
            case 'modules':

                return $this->getModules()->clear();

            default:
                if (true === strpos($offset, 'Module')) {
                    return $this->modules->offsetUnset($offset);
                } else {
                    return true;
                }
        }
    }
}
