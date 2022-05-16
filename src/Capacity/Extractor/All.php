<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Capacity\Extractor;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function Kiboko\Component\SatelliteToolbox\Configuration\compileValue;
use function Kiboko\Component\SatelliteToolbox\Configuration\compileValueWhenExpression;

final class All implements Akeneo\Capacity\CapacityInterface
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
        'assetManager',
    ];

    public function __construct(private ExpressionLanguage $interpreter)
    {
    }

    public function applies(array $config): bool
    {
        return isset($config['type'])
            && in_array($config['type'], self::$endpoints)
            && isset($config['method'])
            && $config['method'] === 'all';
    }

    private function compileFilters(array ...$filters): Node\Expr
    {
        $builder = new Akeneo\Builder\Search();
        foreach ($filters as $filter) {
            if ($filter["operator"] === 'EMPTY' && array_key_exists('value', $filter)) {
                throw new InvalidConfigurationException('You should not provide a value for the EMPTY operator');
            }
            if ($filter["operator"] !== 'EMPTY' && !array_key_exists('value', $filter)) {
                throw new InvalidConfigurationException(
                    sprintf('You should provide a value for the %s operator', $filter['operator'])
                );
            }

            $builder->addFilter(
                field: compileValue($this->interpreter, $filter["field"]),
                operator: compileValue($this->interpreter, $filter["operator"]),
                value: array_key_exists('value', $filter) ? compileValue($this->interpreter, $filter["value"]) : null,
                scope: array_key_exists('scope', $filter) ? compileValue($this->interpreter, $filter["scope"]) : null,
                locale: array_key_exists('locale', $filter) ? compileValue($this->interpreter, $filter["locale"]) : null
            );
        }

        return $builder->getNode();
    }

    public function getBuilder(array $config): Builder
    {
        $builder = (new Akeneo\Builder\Capacity\Extractor\All())
                ->withEndpoint(new Node\Identifier(sprintf('get%sApi', ucfirst($config['type']))))
                ->withType($config['type']);

        if (isset($config['search']) && is_array($config['search'])) {
            $builder->withSearch($this->compileFilters(...$config['search']));
        }

        if (in_array($config['type'], ['attributeOption', 'assetManager']) && array_key_exists('code', $config)) {
            $builder->withCode(compileValueWhenExpression($this->interpreter, $config['code']));
        }

        return $builder;
    }
}
