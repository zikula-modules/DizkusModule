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
 * AbstractProBundle
 *
 * @author Kaik
 */
abstract class AbstractProBundle extends AbstractHookBundle
{
    public function __construct()
    {
        $this->baseName = str_replace('ProBundle', 'Provider', str_replace('Zikula\DizkusModule\Hooks\\', '', static::class));

        parent::__construct();
    }

    public function getSettingsForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\ProviderSettingsType';
    }

    public function getBindingForm()
    {
        return false;
    }
}
