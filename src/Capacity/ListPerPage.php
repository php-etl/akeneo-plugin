<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Capacity;

use Kiboko\Component\ETL\Flow\Akeneo\Builder\Capacity;
use Kiboko\Component\ETL\Flow\Akeneo\Builder\Search;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\Node\Identifier;

final class ListPerPage implements CapacityInterface
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
            && $config['method'] === 'listPerPage';
    }

    private function compileFilters(array ...$filters): Node
    {
        $builder = new Search();
        foreach ($filters as $filter) {
            $builder->addFilter(...$filter);
        }

        return $builder->getNode();
    }

    public function getBuilder(array $config): Builder
    {
        $builder = (new Capacity\All())
            ->withEndpoint(new Node\Identifier(sprintf('get%sApi', ucfirst($config['extractor']['type']))));

        if (isset($config['search']) && is_array($config['search'])) {
            $builder->withSearch($this->compileFilters(...$config['search']));
        }

        return $builder;
    }
}
