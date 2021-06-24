<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Component\SatelliteToolbox\Builder\IsolatedValueAppendingBuilder;
use PhpParser\Builder;
use PhpParser\Node;

final class AlternativeLookup implements Builder
{
    private bool $withEnterpriseSupport;
    private ?Node\Expr $client;
    private ?Builder $capacity;
    private ?Builder $merge;

    public function __construct()
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
        return (new IsolatedValueAppendingBuilder(
            new Node\Expr\Variable('input'),
            new Node\Expr\Variable('output'),
            array_filter([
                $this->capacity->getNode(),
                $this->merge?->getNode(),
                new Node\Stmt\Return_(
                    new Node\Expr\Variable('output')
                ),
            ]),
            new Node\Expr\Variable('bucket')
        ))->getNode();
    }
}
