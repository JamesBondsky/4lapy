<?php

namespace FourPaws\MobileApiBundle;

use FourPaws\MobileApiBundle\Security\ApiTokenSecurityFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FourPawsMobileApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /**
         * @var SecurityExtension $extension
         */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new ApiTokenSecurityFactory());
    }
}
