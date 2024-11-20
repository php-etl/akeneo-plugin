<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Kiboko\Contract\Configurator\PluginConfigurationInterface;
use Symfony\Component\Config;

use function Kiboko\Component\SatelliteToolbox\Configuration\asExpression;
use function Kiboko\Component\SatelliteToolbox\Configuration\isExpression;

final class Loader implements PluginConfigurationInterface
{
    private static array $endpoints = [
        // Core Endpoints
        'product' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'category' => [
            'create',
            'upsert',
            'upsertList',
        ],
        'attribute' => [
            'create',
            'upsert',
            'upsertList',
        ],
        'attributeOption' => [
            'create',
            'upsert',
            'upsertList',
        ],
        'attributeGroup' => [
            'create',
            'upsert',
            'upsertList',
        ],
        'family' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'productMediaFile' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'locale' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'channel' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'currency' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'measureFamily' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'associationType' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'familyVariant' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'productModel' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        // Enterprise Endpoints
        'publishedProduct' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'productModelDraft' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'productDraft' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'asset' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'assetCategory' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'assetTag' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'assetReferenceFile' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'assetVariationFile' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'referenceEntityRecord' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'referenceEntityMediaFile' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'referenceEntityAttribute' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'referenceEntityAttributeOption' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
        'referenceEntity' => [
            'create',
            'upsert',
            'upsertList',
            'delete',
        ],
    ];

    public function getConfigTreeBuilder(): Config\Definition\Builder\TreeBuilder
    {
        $builder = new Config\Definition\Builder\TreeBuilder('loader');

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
                ->always(function (array $item) {
                    if ('upsert' === $item['method'] && empty($item['code'])) {
                        throw new \InvalidArgumentException('Your configuration should contain the "code" field if the "upsert" method is present.');
                    }

                    return $item;
                })
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('reference_entity', $data)
                    && \array_key_exists('type', $data)
                    && !\in_array($data['type'], ['referenceEntityRecord', 'referenceEntityAttributeOption'], true))
                ->thenInvalid('The reference_entity option should only be used with the "referenceEntityRecord" and "referenceEntityAttributeOption" endpoints.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => !\array_key_exists('reference_entity', $data)
                    && \array_key_exists('type', $data)
                    && \in_array($data['type'], ['referenceEntityRecord', 'referenceEntityAttributeOption'], true))
                ->thenInvalid('The reference_entity option should be used with the "referenceEntityRecord" and "referenceEntityAttributeOption" endpoints.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => \array_key_exists('attribute_code', $data)
                    && \array_key_exists('type', $data)
                    && !\in_array($data['type'], ['attributeOption'], true))
                ->thenInvalid('The attribute_code option should only be used with the "attributeOption" endpoint.')
            ->end()
            ->validate()
                ->ifTrue(fn ($data) => !\array_key_exists('attribute_code', $data)
                    && \array_key_exists('type', $data)
                    && \in_array($data['type'], ['attributeOption'], true))
                ->thenInvalid('The attribute_code option should be used with the "attributeOption" endpoint.')
            ->end()
            ->children()
                ->scalarNode('type')
                    ->isRequired()
                    ->validate()
                        ->ifNotInArray(array_keys(self::$endpoints))
                        ->thenInvalid(
                            \sprintf('the value should be one of [%s]', implode(', ', array_keys(self::$endpoints)))
                        )
                    ->end()
                ->end()
                ->scalarNode('method')->end()
                ->scalarNode('code')
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
                ->scalarNode('reference_entity_attribute')
                    ->validate()
                        ->ifTrue(isExpression())
                        ->then(asExpression())
                    ->end()
                ->end()
                ->scalarNode('reference_entity_attribute_option')
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
            ->end()
        ;

        return $builder;
    }
}
