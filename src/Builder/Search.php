<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class Search implements Builder
{
    private array $filters = [];

    public function addFilter(
        string $field,
        string $operator,
        null|string|int|array $value = null,
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

        if (null === $value && count($options) <= 0) {
            $arguments[] = new Node\Arg(
                new Node\Expr\ConstFetch(
                    name: new Node\Name('null'),
                ),
            );
            $arguments[] = new Node\Expr\Array_(
                items: $options,
                attributes: [
                    'kind' => Node\Expr\Array_::KIND_SHORT
                ]
            );
        } else if (is_string($value)) {
            $arguments[] = new Node\Arg(
                value: new Node\Scalar\String_($value),
            );
            if (count($options) > 0) {
                $arguments[] = new Node\Expr\Array_(items: $options, attributes: ['kind' => Node\Expr\Array_::KIND_SHORT]);
            }
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
