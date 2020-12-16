<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Builder;

use Kiboko\Contract\ETL\Configurator\InvalidConfigurationException;
use PhpParser\Builder;
use PhpParser\Node;

final class Search implements Builder
{
    private array $filters = [];

    private function compileValue(null|bool|string|int|float|array $value): Node\Expr
    {
        if ($value === null) {
            return new Node\Expr\ConstFetch(
                name: new Node\Name('null'),
            );
        }
        if ($value === true) {
            return new Node\Expr\ConstFetch(
                name: new Node\Name('true'),
            );
        }
        if ($value === false) {
            return new Node\Expr\ConstFetch(
                name: new Node\Name('false'),
            );
        }
        if (is_string($value)) {
            return new Node\Scalar\String_($value);
        }
        if (is_int($value)) {
            return new Node\Scalar\LNumber($value);
        }
        if (is_double($value)) {
            return new Node\Scalar\DNumber($value);
        }
        if (is_array($value)) {
            return $this->compileArray(values: $value);
        }

        throw new InvalidConfigurationException(
            message: 'Could not determine the correct way to compile the provided filter.'
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

    public function addFilter(
        string $field,
        string $operator,
        null|bool|string|int|array $value = null,
        null|string|array $scope = null,
        null|string|array $locale = null
    ): self {
        $arguments = [
            new Node\Arg(
                value: new Node\Scalar\String_($field),
            ),
            new Node\Arg(
                value: new Node\Scalar\String_($operator),
            ),
            new Node\Arg(
                value: $this->compileValue($value),
            ),
        ];

        $options = [];
        if (null !== $scope) {
            $options[] = new Node\Expr\ArrayItem(
                value: new Node\Scalar\String_($scope),
                key: new Node\Scalar\String_('scope'),
            );
        }
        if (null !== $locale) {
            $options[] = new Node\Expr\ArrayItem(
                value: new Node\Scalar\String_($locale),
                key: new Node\Scalar\String_('locale'),
            );
        }

        if (count($options) > 0) {
            $arguments[] = new Node\Expr\Array_(
                items: $options,
                attributes: [
                    'kind' => Node\Expr\Array_::KIND_SHORT
                ]
            );
        }

        $this->filters[] = $arguments;

        return $this;
    }

    public function getNode(): Node
    {
        $instance = new Node\Expr\New_(
            class: new Node\Name\FullyQualified('Akeneo\\Pim\\ApiClient\\Search\\SearchBuilder')
        );

        foreach ($this->filters as $filterSpec) {
            $instance = new Node\Expr\MethodCall(
                var: $instance,
                name: new Node\Identifier('addFilter'),
                args: $filterSpec,
            );
        }

        return new Node\Expr\MethodCall(
            var: $instance,
            name: new Node\Identifier('getFilters')
        );
    }
}
