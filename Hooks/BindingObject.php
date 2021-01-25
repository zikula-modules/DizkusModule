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

/**
 * BindingObject
 *
 * @author Kaik
 */
class BindingObject implements \ArrayAccess
{
    public $enabled = false;

    public $provider = [];

    public $subscriber = [];

    public $settings = [];

    public $form;

    public function __construct()
    {
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getSubscriber()
    {
        return $this->subscriber;
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->settings);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->settings[$offset] : false;
    }

    public function offsetSet($offset, $value)
    {
        return $this->offsetExists($offset) ? $this->settings[$offset] = $value : false;
    }

    public function offsetUnset($offset)
    {
        return true;
    }

    public function __toString()
    {
        return (string) $this->offsetGet('id');
    }
}
