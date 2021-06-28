<?php declare(strict_types=1);

namespace Creative\AuthClientBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AuthClientExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('auth_client.client');
        $definition->setArgument('$oauthServerUrl', $config['client']['oauth_server_url'] ?? '');
        $definition->setArgument('$clientId', $config['client']['client_id'] ?? '');
        $definition->setArgument('$clientSecret', $config['client']['client_secret'] ?? '');
        $definition->setArgument('$redirectUri', $config['client']['redirect_uri'] ?? '');

        $definition = $container->getDefinition('auth_client.jwt_parser');
        $definition->setArgument('$publicKeyPath', $config['jwt_parser']['public_key_path'] ?? '');

        if (isset($config['user_provider']['class'])) {
            $definition = $container->getDefinition('auth_client.user_provider');
            $definition->setClass($config['user_provider']['class']);
        }

        if (isset($config['authenticator']['class'])) {
            $definition = $container->getDefinition('auth_client.authenticator');
            $definition->setClass($config['authenticator']['class']);
        }
    }
}
