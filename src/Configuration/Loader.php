<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Kiboko\Contract\Configurator\PluginConfigurationInterface;
use Symfony\Component\Config;

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

    public function getConfigTreeBuilder()
    {
        $builder = new Config\Definition\Builder\TreeBuilder('loader');

        /** @phpstan-ignore-next-line */
        $builder->getRootNode()
            ->validate()
                ->ifArray()
                ->then(function (array $item) {
                    if (!in_array($item['method'], self::$endpoints[$item['type']])) {
                        throw new \InvalidArgumentException(
                            sprintf('the value should be one of [%s], got %s', implode(', ', self::$endpoints[$item['type']]), \json_encode($item['method']))
                        );
                    }

                    return $item;
                })
            ->end()
            ->children()
                ->scalarNode('type')
                    ->isRequired()
                    ->validate()
                        ->ifNotInArray(array_keys(self::$endpoints))
                        ->thenInvalid(
                            sprintf('the value should be one of [%s]', implode(', ', array_keys(self::$endpoints)))
                        )
                    ->end()
                ->end()
                ->scalarNode('method')->end()
            ->end();

        return $builder;
    }
}
