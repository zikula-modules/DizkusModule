<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Description of ImportCompilerPass
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
