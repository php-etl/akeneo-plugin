<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Capacity\Lookup;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

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
            $builder->addFilter(
                field: $this->compileValue($filter["field"]),
                operator: $this->compileValue($filter["operator"]),
                value: $this->compileValue($filter["value"]),
                scope: array_key_exists('scope', $filter) ? $this->compileValue($filter["scope"]) : null,
                locale: array_key_exists('locale', $filter) ? $this->compileValue($filter["locale"]) : null
            );
        }

        return $builder->getNode();
    }

    private function compileValue(null|bool|string|int|float|array|Expression $value): Node\Expr
    {
        if ($value === null) {
            return new Node\Expr\ConstFetch(
                name: new Node\Name(name: 'null'),
            );
        }
        if ($value === true) {
            return new Node\Expr\ConstFetch(
                name: new Node\Name(name: 'true'),
            );
        }
        if ($value === false) {
            return new Node\Expr\ConstFetch(
                name: new Node\Name(name: 'false'),
            );
        }
        if (is_string($value)) {
            return new Node\Scalar\String_(value: $value);
        }
        if (is_int($value)) {
            return new Node\Scalar\LNumber(value: $value);
        }
        if (is_double($value)) {
            return new Node\Scalar\DNumber(value: $value);
        }
        if (is_array($value)) {
            return $this->compileArray(values: $value);
        }
        if ($value instanceof Expression) {
            $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, null);
            return $parser->parse('<?php ' . $this->interpreter->compile($value, ['input']) . ';')[0]->expr;
        }

        throw new InvalidConfigurationException(
            message: 'Could not determine the correct way to compile the provided filter.',
        );
    }

    private function compileArray(array $values): Node\Expr
    {
        $items = [];
        foreach ($values as $key => $value) {
            $keyNode = null;
            if (is_string($key)) {
                $keyNode = new Node\Scalar\String_($key);
            }

            $items[] = new Node\Expr\ArrayItem(
                value: $this->compileValue($value),
                key: $keyNode,
            );
        }

        return new Node\Expr\Array_(
            $items,
            [
                'kind' => Node\Expr\Array_::KIND_SHORT,
            ]
        );
    }

    public function getBuilder(array $config): Builder
    {
        $builder = (new Akeneo\Builder\Capacity\Lookup\All())
            ->withEndpoint(new Node\Identifier(sprintf('get%sApi', ucfirst($config['type']))));

        if (isset($config['search']) && is_array($config['search'])) {
            $builder->withSearch($this->compileFilters(...$config['search']));
        }

        if (in_array($config['type'], ['attributeOption'])
            && array_key_exists('code', $config)
        ) {
            $builder->withCode($this->compileValue($config['code']));
        }

        return $builder;
    }
}
