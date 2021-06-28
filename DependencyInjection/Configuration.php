<?php declare(strict_types=1);

namespace Creative\AuthClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private const TREE_BUILDER_ROOT = 'auth_client';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::TREE_BUILDER_ROOT);

        /** @psalm-suppress PossiblyNullReference */
        // TODO: where do I need to store default values?
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('client')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('oauth_server_url')->defaultNull()->end()
                        ->scalarNode('client_id')->defaultNull()->end()
                        ->scalarNode('client_secret')->defaultNull()->end()
                        ->scalarNode('redirect_uri')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('jwt_parser')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('public_key_path')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('user_provider')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('authenticator')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;

    }
}
