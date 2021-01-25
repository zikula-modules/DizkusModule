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

namespace Zikula\DizkusModule;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Core\AbstractModule;
use Zikula\DizkusModule\DependencyInjection\ImportCompilerPass;
use Zikula\DizkusModule\DependencyInjection\TwigCompilerPass;

class ZikulaDizkusModule extends AbstractModule
{
    const NAME = 'ZikulaDizkusModule';

    /**
     * {@inheritdoc}
     *
     * Adds compiler passes to the container.
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ImportCompilerPass());
        $container->addCompilerPass(new TwigCompilerPass());
    }
}
