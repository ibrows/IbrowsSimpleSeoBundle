<?php

namespace Ibrows\SimpleSeoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbrowsSimpleSeoBundle extends Bundle
{
    
    public function build(\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        parent::build($container);

       $container->addCompilerPass(new DependencyInjection\Compiler\RoutingPass());
    }    
}
