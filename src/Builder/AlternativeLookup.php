<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Component\FastMap\Compiler\Builder\IsolatedCodeBuilder;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class AlternativeLookup implements Builder
{
    private bool $withEnterpriseSupport;
    private ?Node\Expr $client;
    private ?Builder $capacity;
    private ?Builder $merge;

    public function __construct(private ExpressionLanguage $interpreter)
    {
        $this->withEnterpriseSupport = false;
        $this->client = null;
        $this->capacity = null;
        $this->merge = null;
    }

    public function withCapacity(Builder $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function withMerge(Builder $merge): self
    {
        $this->merge = $merge;

        return $this;
    }

    public function getNode(): Node
    {
        return (new IsolatedCodeBuilder(
            new Node\Expr\Variable('input'),
            new Node\Expr\Variable('output'),
            array_filter([
                new Node\Stmt\Expression(
                    new Node\Expr\Assign(
                        var: new Node\Expr\Variable('lookup'),
                        expr: $this->capacity->getNode(),
                    ),
                ),
                $this->merge?->getNode(),
            ]),
        ))->getNode();
    }
}
