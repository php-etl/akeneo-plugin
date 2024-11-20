<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Kiboko\Contract\Configurator\PluginConfigurationInterface;
use Symfony\Component\Config;

use function Kiboko\Component\SatelliteToolbox\Configuration\asExpression;
use function Kiboko\Component\SatelliteToolbox\Configuration\isExpression;

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
                ->ifArray()
                ->then(function (array $item) {
                    if (!\in_array($item['method'], self::$endpoints[$item['type']])) {
                        throw new \InvalidArgumentException(\sprintf('the value should be one of [%s], got %s', implode(', ', self::$endpoints[$item['type']]), json_encode($item['method'], \JSON_THROW_ON_ERROR)));
                    }

                    return $item;
                })
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('code', $data)
                        && \array_key_exists('type', $data)
                        && !\in_array($data['type'], ['attributeOption', 'assetManager'], true))
                ->thenInvalid('The code option should only be used with the "attributeOption" and "assetManager" endpoints.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('file', $data)
                        && \array_key_exists('type', $data)
                        && !\in_array($data['type'], ['productMediaFile', 'assetMediaFile'], true))
                ->thenInvalid('The file option should only be used with the "productMediaFile" endpoint.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('identifier', $data) && \array_key_exists('method', $data) && 'get' !== $data['method'])
                ->thenInvalid('The identifier option should only be used with the "get" method.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('reference_entity', $data)
                    && \array_key_exists('type', $data)
                    && !\in_array($data['type'], ['referenceEntityRecord', 'referenceEntityAttributeOption'], true))
                ->thenInvalid('The reference_entity option should only be used with the "referenceEntity" and "referenceEntityAttributeOption" endpoints.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('reference_entity_option', $data)
                    && \array_key_exists('type', $data)
                    && !\in_array($data['type'], ['referenceEntityAttributeOption'], true))
                ->thenInvalid('The reference_entity_option option should only be used with the "referenceEntityAttributeOption" endpoint.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => !\array_key_exists('reference_entity', $data)
                    && \array_key_exists('type', $data)
                    && \in_array($data['type'], ['referenceEntityRecord', 'referenceEntityAttributeOption'], true))
                ->thenInvalid('The reference_entity option should be used with the "referenceEntityRecord" and "referenceEntityAttributeOption" endpoints.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => !\array_key_exists('reference_entity_option', $data)
                    && \array_key_exists('type', $data)
                    && \in_array($data['type'], ['referenceEntityAttributeOption'], true))
                ->thenInvalid('The reference_entity option should be used with the "referenceEntityAttributeOption" endpoint.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('with_enriched_attributes', $data) && \array_key_exists('type', $data) && !\in_array($data['type'], ['category'], true))
                ->thenInvalid('The with_enriched_attributes option should only be used with the "category" endpoint.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('with_enriched_attributes', $data) && \array_key_exists('method', $data) && 'all' !== $data['method'])
                ->thenInvalid('The with_enriched_attributes option should only be used with the "all" method.')
            ->end()
            ->children()
                ->scalarNode('type')
                    ->isRequired()
                    ->validate()
                        ->ifNotInArray(array_keys(self::$endpoints))
                        ->thenInvalid(
                            \sprintf('the value should be one of [%s], got %%s', implode(', ', array_keys(self::$endpoints)))
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
                ->scalarNode('reference_entity')
                    ->validate()
                        ->ifTrue(isExpression())
                        ->then(asExpression())
                    ->end()
                ->end()
                ->scalarNode('reference_entity_option')
                    ->validate()
                        ->ifTrue(isExpression())
                        ->then(asExpression())
                    ->end()
                ->end()
                ->booleanNode('with_enriched_attributes')
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
