<?php

namespace Zim\CertAuthBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class CertificateFactory implements SecurityFactoryInterface
{

    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.dao.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('security.authentication.provider.dao'))
            ->replaceArgument(0, new Reference($userProvider))
            ->replaceArgument(2, $id);

        $listenerId = 'security.authentication.listener.zim_cert.'.$id;
        $container
            ->setDefinition($listenerId, new DefinitionDecorator('zim_cert_auth.security.authentication.listener'))
            ->replaceArgument(1, new Reference($userProvider))
            ->replaceArgument(3, $id);

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    /**
     * Defines the position at which the provider is called.
     * Possible values: pre_auth, form, http, and remember_me.
     *
     * @return string
     */
    public function getPosition()
    {
        return 'http';
    }

    public function getKey()
    {
        return 'zim-cert';
    }

    public function addConfiguration(NodeDefinition $builder)
    {

    }
}