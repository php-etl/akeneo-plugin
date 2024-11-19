<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Capacity\Lookup;

use Kiboko\Plugin\Akeneo;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function Kiboko\Component\SatelliteToolbox\Configuration\compileValueWhenExpression;

final class Get implements Akeneo\Capacity\CapacityInterface
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
        'referenceEntityRecord',
        'referenceEntityAttribute',
        'referenceEntityAttributeOption',
        'referenceEntity',
        'assetManager',
    ];

    public function __construct(private readonly ExpressionLanguage $interpreter)
    {
    }

    public function applies(array $config): bool
    {
        return isset($config['type'], $config['method']) && \in_array($config['type'], self::$endpoints) && 'get' === $config['method'];
    }

    public function getBuilder(array $config): Builder
    {
        $builder = (new Akeneo\Builder\Capacity\Lookup\Get())
            ->withEndpoint(new Node\Identifier(\sprintf('get%sApi', ucfirst((string) $config['type']))))
            ->withType($config['type'])
        ;

        $builder->withIdentifier(compileValueWhenExpression($this->interpreter, $config['identifier']));

        if (\array_key_exists('code', $config)) {
            $builder->withCode(compileValueWhenExpression($this->interpreter, $config['code']));
        }

        return $builder;
    }
}
