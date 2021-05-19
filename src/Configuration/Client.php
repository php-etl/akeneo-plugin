<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Symfony\Component\Config;
use Symfony\Component\ExpressionLanguage\Expression;

final class Client implements Config\Definition\ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new Config\Definition\Builder\TreeBuilder('client');

        /** @phpstan-ignore-next-line */
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
                        ->scalarNode('http_client')
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                                ->then(fn ($data) => new Expression(substr($data, 2)))
                            ->end()
                        ->end()
                        ->scalarNode('http_request_factory')
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                                ->then(fn ($data) => new Expression(substr($data, 2)))
                            ->end()
                        ->end()
                        ->scalarNode('http_stream_factory')
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                                ->then(fn ($data) => new Expression(substr($data, 2)))
                            ->end()
                        ->end()
                        ->scalarNode('filesystem')
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                                ->then(fn ($data) => new Expression(substr($data, 2)))
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('api_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                        ->then(fn ($data) => new Expression(substr($data, 2)))
                    ->end()
                ->end()
                ->scalarNode('client_id')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                        ->then(fn ($data) => new Expression(substr($data, 2)))
                    ->end()
                ->end()
                ->scalarNode('secret')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                        ->then(fn ($data) => new Expression(substr($data, 2)))
                    ->end()
                ->end()
                ->scalarNode('username')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                        ->then(fn ($data) => new Expression(substr($data, 2)))
                    ->end()
                ->end()
                ->scalarNode('password')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                        ->then(fn ($data) => new Expression(substr($data, 2)))
                    ->end()
                ->end()
                ->scalarNode('token')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                        ->then(fn ($data) => new Expression(substr($data, 2)))
                    ->end()
                ->end()
                ->scalarNode('refresh_token')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                        ->then(fn ($data) => new Expression(substr($data, 2)))
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
