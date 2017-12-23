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

/**
 * AbstractProBundle
 *
 * @author Kaik
 */
abstract class AbstractSubBundle extends AbstractHookBundle
{
    public function __construct()
    {
        $this->baseName= str_replace('SubBundle', 'Subscriber', str_replace('Zikula\DizkusModule\Hooks\\', '', get_class($this)));
        parent::__construct();
    }

    public function getSettingsForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\SubscriberSettingsType';
    }

    public function getBindingForm()
    {
        return false;
    }
}
