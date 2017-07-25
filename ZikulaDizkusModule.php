<?php

namespace Zikula\DizkusModule;

use Zikula\DizkusModule\DependencyInjection\ImportCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Core\AbstractModule;

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
    }
}
