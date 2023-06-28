<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Capacity\Loader;

use Kiboko\Plugin\Akeneo;
use PhpParser\Builder;
use PhpParser\Node;
use function Kiboko\Component\SatelliteToolbox\Configuration\compileValueWhenExpression;

final class UpsertList implements Akeneo\Capacity\CapacityInterface
{
    private static array $endpoints = [
        // Core Endpoints
        'product',
        'category',
        'attribute',
        'attributeOption',
        'attributeGroup',
        'family',
        'productMediaFile',
        'locale',
        'channel',
        'currency',
        'measureFamily',
        'associationType',
        'familyVariant',
        'productModel',
        // Enterprise Endpoints
        'publishedProduct',
        'productModelDraft',
        'productDraft',
        'asset',
        'assetCategory',
        'assetTag',
        'assetReferenceFile',
        'assetVariationFile',
        'referenceEntityRecord',
        'referenceEntityMediaFile',
        'referenceEntityAttribute',
        'referenceEntityAttributeOption',
        'referenceEntity',
    ];

    public function applies(array $config): bool
    {
        return isset($config['type'])
            && \in_array($config['type'], self::$endpoints)
            && isset($config['method'])
            && 'upsertList' === $config['method'];
    }

    public function getBuilder(array $config): Builder
    {
        $builder = (new Akeneo\Builder\Capacity\Loader\UpsertList())
            ->withEndpoint(endpoint: new Node\Identifier(sprintf('get%sApi', ucfirst((string) $config['type']))))
            ->withData(data: new Node\Expr\Variable('line'))
        ;

        if (\array_key_exists('referenceEntity', $config)) {
            $builder->withReferenceEntity(referenceEntity: new Node\Scalar\String_($config['referenceEntity']));
        }

        if (\array_key_exists('attribute', $config)) {
            $builder->withAttributeCode(code: new Node\Scalar\String_($config['attribute']));
        }

        return $builder;
    }
}
