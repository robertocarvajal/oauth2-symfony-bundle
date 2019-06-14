<?php

/**
 * This file is part of the authbucket/oauth2-symfony-bundle package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AuthBucket\Bundle\OAuth2Bundle\DependencyInjection;

use AuthBucket\OAuth2\GrantType\GrantTypeHandlerFactory;
use AuthBucket\OAuth2\Symfony\Component\Security\Core\Authentication\Provider\ResourceProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AuthBucketOAuth2Extension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(
            new Configuration(),
            $configs
        );

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');

        $driver = $config['driver'] ?: 'in_memory';
        if (in_array($driver, ['in_memory', 'orm'])) {
            $loader->load(sprintf('%s.yml', $driver));
        }
        unset($config['driver']);

        $userProvider = $config['user_provider'] ?: null;
        if ($userProvider) {
            $userProviderReference = new Reference($userProvider);
            $container->getDefinition(GrantTypeHandlerFactory::class)
                ->replaceArgument(5, $userProviderReference);
            $container->getDefinition(ResourceProvider::class)
                ->replaceArgument(5, $userProviderReference);
        }
        unset($config['user_provider']);

        $encoderFactory = $config['encoder_factory'] ?: null;
        if ($encoderFactory) {
        $encoderFactoryReference = new Reference($encoderFactory);
        $container->getDefinition(GrantTypeHandlerFactory::class)
                ->replaceArgument(1, $encoderFactoryReference);
        }
        unset($config['encoder_factory']);

        foreach (array_filter($config) as $key => $value) {
            $container->setParameter('authbucket_oauth2.'.$key, $value);
        }
    }

    public function getAlias()
    {
        return 'authbucket_oauth2';
    }
}
