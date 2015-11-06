<?php

namespace Zim\CertAuthBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zim\CertAuthBundle\DependencyInjection\Security\Factory\CertificateFactory;

class ZimCertAuthBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        if (!extension_loaded('openssl')) {
            throw new \RuntimeException('ZimCertAuthBundle requires openssl extension to be loaded');
        }

        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new CertificateFactory());
    }


}
