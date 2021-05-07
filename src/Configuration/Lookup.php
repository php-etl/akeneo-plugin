<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Symfony\Component\Config;

final class Lookup implements Config\Definition\ConfigurationInterface
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

    public function getLookupTreeBuilder(): Config\Definition\Builder\TreeBuilder
    {
        $filters = new Search();

        $builder = new Config\Definition\Builder\TreeBuilder('lookup');

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
                            sprintf('the value should be one of [%s], got %%s', implode(', ', array_keys(self::$endpoints)))
                        )
                    ->end()
                ->end()
                ->scalarNode('field')->end()
                ->scalarNode('method')->isRequired()->end()
                ->append($filters->getConfigTreeBuilder())
            ->end();

        return $builder;
    }

    public function getConfigTreeBuilder(): Config\Definition\Builder\TreeBuilder
    {
        $builder = new Config\Definition\Builder\TreeBuilder('conditional');

        /** @phpstan-ignore-next-line */
        $builder->getRootNode()
            ->children()
                ->scalarNode('condition')->end()
                ->append($this->getLookupTreeBuilder()->getRootNode())
                ->variableNode('merge')
                    ->validate()
                        ->ifTrue(function ($element) {
                            return !is_array($element);
                        })
                        ->thenInvalid('The children element must be an array.')
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
