<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Registers all Transactors with the TransactorFactory
 */
class RegisterTransactorsPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return mixed
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('orkestra.transactor_factory')) {
            return;
        }

        $definition = $container->getDefinition('orkestra.transactor_factory');

        foreach ($container->findTaggedServiceIds('orkestra.transactor') as $service => $tags) {
            $definition->addMethodCall('registerTransactor', array(new Reference($service)));
        }
    }
}
