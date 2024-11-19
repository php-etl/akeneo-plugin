<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class Search implements Builder
{
    public function __construct(
        private array $filters = [],
    ) {
    }

    public function addFilter(
        Node\Expr $field,
        Node\Expr $operator,
        ?Node\Expr $value = null,
        ?Node\Expr $scope = null,
        ?Node\Expr $locale = null,
    ): self {
        $arguments = [
            new Node\Arg(
                value: $field,
            ),
            new Node\Arg(
                value: $operator,
            ),
            new Node\Arg(
                value: $value ?? new Node\Expr\ConstFetch(new Node\Name('null')),
            ),
        ];

        $options = [];
        if (null !== $scope) {
            $options[] = new Node\Expr\ArrayItem(
                value: $scope,
                key: new Node\Scalar\String_('scope'),
            );
        }
        if (null !== $locale) {
            $options[] = new Node\Expr\ArrayItem(
                value: $locale,
                key: new Node\Scalar\String_('locale'),
            );
        }

        if (\count($options) > 0) {
            $arguments[] = new Node\Expr\Array_(
                items: $options,
                attributes: [
                    'kind' => Node\Expr\Array_::KIND_SHORT,
                ]
            );
        }

        $this->filters[] = $arguments;

        return $this;
    }

    public function getNode(): Node\Expr
    {
        $instance = new Node\Expr\New_(
            class: new Node\Name\FullyQualified(\Akeneo\Pim\ApiClient\Search\SearchBuilder::class)
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
