<?php

namespace Zim\CertAuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ZimCertAuthExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $def = $container->getDefinition('zim_cert_auth.certificate_generator');
        $def->replaceArgument(0, $config['ca_path']);
        $def->replaceArgument(1, $config['ca_key_path']);

        if (!empty($config['ca_key_password'])) {
            $def->addMethodCall('setCaKeyPassword', [$config['ca_key_password']]);
        }

        // additional csr options
        $def->addMethodCall('setClientCSROptions', [$config['client_csr_options']]);

        // bit strength
        $def->addMethodCall('setBits', [$config['bit_strength']]);

        if (isset($config['cert_validation_expression'])) {
            $container->getDefinition('zim_cert_auth.security.certificate_expression_validator')
                ->addMethodCall('setExpression', [$config['cert_validation_expression']]);
        }

        $listenerDef = $container->getDefinition('zim_cert_auth.security.authentication.listener');
        $listenerDef->addMethodCall('setEnvCertificateContent', [$config['cert_content_server_var']]);
        $listenerDef->addMethodCall('setCustomOIDs', [$config['custom_oids']]);

        $storageId = $container->getDefinition('zim_cert_auth.certificate_storage');

        // formatter
        $serviceId = $config['cert_storage_formatter']['id'];
        $serviceDef = $container->getDefinition($serviceId);
        $serviceDef->replaceArgument(0, $config['cert_storage_formatter']['options']);
        $storageId->replaceArgument(0, new Reference($serviceId));

        // persister
        $serviceId = $config['cert_storage_persister']['id'];
        $serviceDef = $container->getDefinition($serviceId);
        $serviceDef->replaceArgument(0, $config['cert_storage_persister']['options']);
        $storageId->replaceArgument(1, new Reference($serviceId));
    }
}
