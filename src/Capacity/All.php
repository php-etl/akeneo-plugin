<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Capacity;

use Kiboko\Plugin\Akeneo;
use PhpParser\Builder;
use PhpParser\Node;

final class All implements CapacityInterface
{
    private static $endpoints = [
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
    ];

    public function applies(array $config): bool
    {
        return isset($config['type'])
            && in_array($config['type'], self::$endpoints)
            && isset($config['method'])
            && $config['method'] === 'all';
    }

    private function compileFilters(array ...$filters): Node
    {
        $builder = new Akeneo\Builder\Search();
        foreach ($filters as $filter) {
            $builder->addFilter(...$filter);
        }

        return $builder->getNode();
    }

    public function getBuilder(array $config): Builder
    {
        $builder = (new Akeneo\Builder\Capacity\All())
            ->withEndpoint(new Node\Identifier(sprintf('get%sApi', ucfirst($config['type']))));

        if (isset($config['search']) && is_array($config['search'])) {
            $builder->withSearch($this->compileFilters(...$config['search']));
        }

        if (in_array($config['type'], ['attributeOption'])
            && array_key_exists('code', $config)
        ) {
            $builder->withCode($config['code']);
        }

        return $builder;
    }
}
