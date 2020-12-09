<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Configurator;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config;

final class Configuration implements ConfigurationInterface
{
    private static $endpoints = [
        // Core Endpoints
        'product' => [
            'listPerPage',
            'all',
            'get',
        ],
        'category' => [
            'listPerPage',
            'all',
            'get',
        ],
        'attribute' => [
            'listPerPage',
            'all',
            'get',
        ],
        'attributeOption' => [
            'listPerPage',
            'all',
            'get',
        ],
        'attributeGroup' => [
            'listPerPage',
            'all',
            'get',
        ],
        'family' => [
            'listPerPage',
            'all',
            'get',
        ],
        'productMediaFile' => [
            'listPerPage',
            'all',
            'get',
        ],
        'locale' => [
            'listPerPage',
            'all',
            'get',
        ],
        'channel' => [
            'listPerPage',
            'all',
            'get',
        ],
        'currency' => [
            'listPerPage',
            'all',
            'get',
        ],
        'measureFamily' => [
            'listPerPage',
            'all',
            'get',
        ],
        'associationType' => [
            'listPerPage',
            'all',
            'get',
        ],
        'familyVariant' => [
            'listPerPage',
            'all',
            'get',
        ],
        'productModel' => [
            'listPerPage',
            'all',
            'get',
        ],
        // Enterprise Endpoints
        'publishedProduct' => [
            'listPerPage',
            'all',
            'get',
        ],
        'productModelDraft' => [
            'get',
        ],
        'productDraft' => [
            'get',
        ],
        'asset' => [
            'listPerPage',
            'all',
            'get',
        ],
        'assetCategory' => [
            'listPerPage',
            'all',
            'get',
        ],
        'assetTag' => [
            'listPerPage',
            'all',
            'get',
        ],
//        'assetReferenceFile' => [], // no support
//        'assetVariationFile' => [], // no support
        'referenceEntityRecord' => [
            'all',
            'get',
        ],
//        'referenceEntityMediaFile' => [], // no support
        'referenceEntityAttribute' => [
            'all',
            'get',
        ],
        'referenceEntityAttributeOption' => [
            'all',
            'get',
        ],
        'referenceEntity' => [
            'all',
            'get',
        ],
    ];

    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('akeneo');
        $builder->getRootNode()
            ->children()
                ->booleanNode('enterprise')->defaultFalse()->end()
                ->arrayNode('endpoint')
                    ->children()
                        ->scalarNode('type')
                            ->isRequired()
                            ->validate()
                                ->ifNotInArray(array_keys(self::$endpoints))
                                ->thenInvalid(sprintf('the value should be one of [%s]', implode(', ', array_keys(self::$endpoints))))
                            ->end()
                        ->end()
                        ->scalarNode('method')->end()
                        ->arrayNode('filter')
                            ->children()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('client')
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
                            if (isset($value['username']) && isset($value['token'])) {
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
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
