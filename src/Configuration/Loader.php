<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Configuration;

use Symfony\Component\Config;

final class Loader implements Config\Definition\ConfigurationInterface
{
    private static array $endpoints = [
        // Core Endpoints
        'product' => [],
        'category' => [],
        'attribute' => [],
        'attributeOption' => [],
        'attributeGroup' => [],
        'family' => [],
        'productMediaFile' => [],
        'locale' => [],
        'channel' => [],
        'currency' => [],
        'measureFamily' => [],
        'associationType' => [],
        'familyVariant' => [],
        'productModel' => [],
        // Enterprise Endpoints
        'publishedProduct' => [],
        'productModelDraft' => [],
        'productDraft' => [],
        'asset' => [],
        'assetCategory' => [],
        'assetTag' => [],
        'assetReferenceFile' => [],
        'assetVariationFile' => [],
        'referenceEntityRecord' => [],
        'referenceEntityMediaFile' => [],
        'referenceEntityAttribute' => [],
        'referenceEntityAttributeOption' => [],
        'referenceEntity' => [],
    ];

    public function getConfigTreeBuilder()
    {
        $builder = new Config\Definition\Builder\TreeBuilder('loader');

        $builder->getRootNode()
            ->children()
                ->scalarNode('type')
                    ->isRequired()
                    ->validate()
                        ->ifNotInArray(array_keys(self::$endpoints))
                        ->thenInvalid(sprintf('the value should be one of [%s]', implode(', ', array_keys(self::$endpoints))))
                    ->end()
                ->end()
                ->scalarNode('method')->end()
            ->end();

        return $builder;
    }
}
