<?php

declare(strict_types=1);

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

/**
 * AbstractHookBundle
 *
 * @author Kaik
 */
abstract class AbstractHookBundle implements \ArrayAccess
{
    private $baseName;

    private $modules;

    private $settings = [];

    public function __construct()
    {
        $this->modules = new ArrayCollection();
    }

    public function getOwner()
    {
        return 'ZikulaDizkusModule';
    }

    public function getBaseName()
    {
        return $this->baseName;
    }

    public function setSettings($hooked)
    {
        $this->settings = $hooked;

        return $this;
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
                if (true === mb_strpos($offset, 'Module')) {
                    return $this->modules->offsetExists($offset);
                }

                    return array_key_exists($offset, $this->settings);
        }
    }

    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'modules':
                return $this->getModules();
            default:
                if (true === mb_strpos($offset, 'Module')) {
                    return $this->modules->offsetGet($offset);
                }

                    return $this->offsetExists($offset) ? $this->settings[$offset] : false;
        }
    }

    public function offsetSet($offset, $value)
    {
        switch ($offset) {
            case 'modules':
                return $this->setModules($value);

            default:
                if (true === mb_strpos($offset, 'Module')) {
                    return $this->modules->offsetSet($offset, $value);
                }

                    return $this->offsetExists($offset) ? $this->settings[$offset] = $value : false;
        }
    }

    public function offsetUnset($offset)
    {
        switch ($offset) {
            case 'modules':
                return $this->getModules()->clear();

            default:
                if (true === mb_strpos($offset, 'Module')) {
                    return $this->modules->offsetUnset($offset);
                }

                    return true;
        }
    }
}
