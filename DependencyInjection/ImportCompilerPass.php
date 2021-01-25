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

namespace Zikula\DizkusModule\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ImportCompilerPass
 *
 * @author Kaik
 */
class ImportCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('zikula_dizkus_module.import_helper')) {
            return;
        }
        $definition = $container->findDefinition('zikula_dizkus_module.import_helper');
        $taggedServices = $container->findTaggedServiceIds('zikula_dizkus_module.import');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addImportHandlder', [new Reference($id)]);
        }
    }
}
