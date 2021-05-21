<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Capacity\Extractor;

use Kiboko\Plugin\Akeneo;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ListPerPage implements Akeneo\Capacity\CapacityInterface
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

    public function __construct(private ExpressionLanguage $interpreter)
    {
    }

    public function applies(array $config): bool
    {
        return isset($config['type'])
            && in_array($config['type'], self::$endpoints)
            && isset($config['method'])
            && $config['method'] === 'listPerPage';
    }

    private function compileFilters(array ...$filters): Node\Expr
    {
        $builder = new Akeneo\Builder\Search($this->interpreter);
        foreach ($filters as $filter) {
            $builder->addFilter(...$filter);
        }

        return $builder->getNode();
    }

    public function getBuilder(array $config): Builder
    {
        $builder = (new Akeneo\Builder\Capacity\Extractor\ListPerPage($this->interpreter))
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
