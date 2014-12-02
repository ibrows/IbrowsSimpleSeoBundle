<?php

namespace Ibrows\SimpleSeoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;


class RoutingPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {

        if (false === $container->hasDefinition('router.default')) {
            return;
        }

        $definition = $container->getDefinition('router.default');
        $definition->addMethodCall('setOption', array('generator_class', 'Ibrows\\SimpleSeoBundle\\Routing\\UrlGenerator'));
        $definition->addMethodCall('setOption', array('generator_base_class', 'Ibrows\\SimpleSeoBundle\\Routing\\UrlGenerator'));
    }

}
