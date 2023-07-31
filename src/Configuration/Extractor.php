<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Kiboko\Contract\Configurator\PluginConfigurationInterface;
use Symfony\Component\Config;

use function Kiboko\Component\SatelliteToolbox\Configuration\asExpression;
use function Kiboko\Component\SatelliteToolbox\Configuration\isExpression;
use function Kiboko\Component\SatelliteToolbox\Configuration\mutuallyDependentFields;

final class Extractor implements PluginConfigurationInterface
{
    private static array $endpoints = [
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
            'download',
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
            'download',
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
        'assetManager' => [
            'all',
            'get',
        ],
        'assetMediaFile' => [
            'download',
        ],
    ];

    public function getConfigTreeBuilder(): Config\Definition\Builder\TreeBuilder
    {
        $builder = new Config\Definition\Builder\TreeBuilder('extractor');

        /* @phpstan-ignore-next-line */
        $builder->getRootNode()
            ->validate()
                ->always(mutuallyDependentFields('asset_family_code', 'asset_code'))
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => (\array_key_exists('asset_family_code', $data) || \array_key_exists('asset_code', $data))
                        && \array_key_exists('type', $data)
                        && !\in_array($data['type'], ['assetManager'], true))
                ->thenInvalid('The asset_family_code and asset_code options should only be used with the "assetManager" endpoint.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => (!\array_key_exists('asset_family_code', $data) || !\array_key_exists('asset_code', $data))
                    && \array_key_exists('type', $data)
                    && \in_array($data['type'], ['assetManager'], true))
                ->thenInvalid('The asset_family_code option should be used with the "assetManager" endpoint.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => (\array_key_exists('attribute_code', $data) || \array_key_exists('code', $data))
                        && \array_key_exists('type', $data)
                        && !\in_array($data['type'], ['attributeOption'], true))
                ->thenInvalid('The attribute_code and code options should only be used with the "attributeOption" endpoint.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => (!\array_key_exists('attribute_code', $data) || !\array_key_exists('code', $data))
                    && \array_key_exists('type', $data)
                    && \in_array($data['type'], ['attributeOption'], true))
                ->thenInvalid('The attribute_code and code options should be used with the "attributeOption" endpoint.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('file', $data)
                        && \array_key_exists('type', $data)
                        && !\in_array($data['type'], ['productMediaFile', 'assetMediaFile'], true))
                ->thenInvalid('The file option should only be used with the "productMediaFile" and the "assetMediaFile" endpoints.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => !\array_key_exists('file', $data)
                    && \array_key_exists('type', $data)
                    && \in_array($data['type'], ['productMediaFile', 'assetMediaFile'], true))
                ->thenInvalid('The file option should be used with the "productMediaFile" and the "assetMediaFile" endpoints.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('identifier', $data) && \array_key_exists('method', $data) && 'get' !== $data['method'])
                ->thenInvalid('The identifier option should only be used with the "get" method.')
            ->end()
            ->children()
                ->scalarNode('type')
                    ->isRequired()
                    ->validate()
                        ->ifNotInArray(array_keys(self::$endpoints))
                        ->thenInvalid(
                            sprintf('the value should be one of [%s], got %%s', implode(', ', array_keys(self::$endpoints)))
                        )
                    ->end()
                ->end()
                ->scalarNode('method')->isRequired()->end()
                ->scalarNode('code')
                    ->validate()
                        ->ifTrue(isExpression())
                        ->then(asExpression())
                    ->end()
                ->end()
                ->scalarNode('file')
                    ->validate()
                        ->ifTrue(isExpression())
                        ->then(asExpression())
                    ->end()
                ->end()
                ->scalarNode('identifier')
                    ->validate()
                        ->ifTrue(isExpression())
                        ->then(asExpression())
                    ->end()
                ->end()
                ->scalarNode('attribute_code')
                    ->validate()
                        ->ifTrue(isExpression())
                        ->then(asExpression())
                    ->end()
                ->end()
                ->scalarNode('asset_family_code')
                    ->validate()
                        ->ifTrue(isExpression())
                        ->then(asExpression())
                    ->end()
                ->end()
                ->scalarNode('asset_code')
                    ->validate()
                        ->ifTrue(isExpression())
                        ->then(asExpression())
                    ->end()
                ->end()
                ->append((new Search())->getConfigTreeBuilder()->getRootNode())
            ->end()
        ;

        return $builder;
    }
}
