<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Component\SatelliteToolbox\Builder\IsolatedFuncCallAppendingBuilder;
use PhpParser\Builder;
use PhpParser\Node;

final class AlternativeLookup implements Builder
{
    private ?Builder $merge = null;

    public function __construct(
        private readonly Builder $capacity,
    ) {
    }

    public function withMerge(Builder $merge): self
    {
        $this->merge = $merge;

        return $this;
    }

    public function getNode(): Node
    {
        return (new IsolatedFuncCallAppendingBuilder(
            new Node\Expr\Variable('input'),
            array_filter([
                $this->capacity->getNode(),
                $this->merge?->getNode(),
                new Node\Stmt\Expression(
                    expr: new Node\Expr\MethodCall(
                        var: new Node\Expr\Variable('bucket'),
                        name: new Node\Name('accept'),
                        args: [
                            new Node\Arg(
                                value: new Node\Expr\Variable('output')
                            ),
                        ]
                    )
                ),
            ]),
            new Node\Expr\Variable('bucket')
        ))->getNode();
    }
}
