<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Capacity\Loader;

use Kiboko\Plugin\Akeneo;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function Kiboko\Component\SatelliteToolbox\Configuration\compileValueWhenExpression;

final class Upsert implements Akeneo\Capacity\CapacityInterface
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

    public function __construct(private readonly ExpressionLanguage $interpreter)
    {
    }

    public function applies(array $config): bool
    {
        return isset($config['type'])
            && \in_array($config['type'], self::$endpoints)
            && isset($config['method'])
            && 'upsert' === $config['method'];
    }

    public function getBuilder(array $config): Builder
    {
        $builder = (new Akeneo\Builder\Capacity\Loader\Upsert())
            ->withEndpoint(endpoint: new Node\Identifier(\sprintf('get%sApi', ucfirst((string) $config['type']))))
            ->withCode(code: compileValueWhenExpression($this->interpreter, $config['code'], 'line'))
            ->withData(line: new Node\Expr\Variable('line'))
        ;

        if (\array_key_exists('reference_entity', $config)) {
            $builder->withReferenceEntity(referenceEntity: new Node\Scalar\String_($config['reference_entity']));
        }

        if (\array_key_exists('reference_entity_attribute', $config)) {
            $builder->withReferenceEntityAttribute(referenceEntityAttribute: compileValueWhenExpression($this->interpreter, $config['reference_entity_attribute'], 'line'));
        }

        return $builder;
    }
}
