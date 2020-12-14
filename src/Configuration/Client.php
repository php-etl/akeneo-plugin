<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Configuration;

use Symfony\Component\Config;

final class Client implements Config\Definition\ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new Config\Definition\Builder\TreeBuilder('client');

        $builder->getRootNode()
            ->validate()
                ->ifArray()
                ->then(function (array $value) {
                    if (isset($value['username']) && !isset($value['password'])) {
                        throw new Config\Definition\Exception\InvalidConfigurationException(
                            'The configuration option "password" should be defined if you use the username authentication method for Akeneo API.'
                        );
                    }
                    if (isset($value['token']) && !isset($value['refresh_token'])) {
                        throw new Config\Definition\Exception\InvalidConfigurationException(
                            'The configuration option "refreshToken" should be defined if you use the token authentication method for Akeneo API.'
                        );
                    }
                    if (isset($value['username']) && isset($value['token']) ||
                        !isset($value['username']) && !isset($value['token'])
                    ) {
                        throw new Config\Definition\Exception\InvalidConfigurationException(
                            'You must choose between "username" and "token" as authentication method for Akeneo API, both are mutually exclusive.'
                        );
                    }
                    return $value;
                })
            ->end()
            ->children()
                ->arrayNode('context')
                    ->children()
                        ->scalarNode('http_client')->end()
                        ->scalarNode('http_request_factory')->end()
                        ->scalarNode('http_stream_factory')->end()
                        ->scalarNode('filesystem')->end()
                    ->end()
                ->end()
                ->scalarNode('api_url')->isRequired()->end()
                ->scalarNode('client_id')->isRequired()->end()
                ->scalarNode('secret')->isRequired()->end()
                ->scalarNode('username')->end()
                ->scalarNode('password')->end()
                ->scalarNode('token')->end()
                ->scalarNode('refresh_token')->end()
            ->end();

        return $builder;
    }
}
