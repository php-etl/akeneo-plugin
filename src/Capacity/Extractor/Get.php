<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Capacity\Extractor;

use Kiboko\Plugin\Akeneo;
use PhpParser\Builder;
use PhpParser\Node;

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

    public function __construct(
        private readonly Akeneo\Handler\EndpointHandlerFactoryInterface $factory,
    ) {
    }

    public function applies(array $config): bool
    {
        return isset($config['type'])
            && \in_array($config['type'], self::$endpoints)
            && isset($config['method'])
            && 'get' === $config['method'];
    }

    public function getBuilder(array $config): Builder
    {
        return (new Akeneo\Builder\Capacity\Extractor\Get($this->factory->create($config['type'], $config)))
            ->withEndpoint(
                new Node\Identifier(sprintf('get%sApi', ucfirst((string) $config['type']))),
            )
        ;
    }
}
