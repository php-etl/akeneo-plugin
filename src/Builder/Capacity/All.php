<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Builder\Capacity;

use PhpParser\Builder;
use PhpParser\Node;

final class All implements Builder
{
    private null|Node\Expr|Node\Identifier $endpoint;
    private null|Node\Expr\Array_ $search;

    public function __construct()
    {
        $this->endpoint = null;
        $this->search = null;
    }

    public function withEndpoint(Node\Expr|Node\Identifier $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function withSearch(Node\Expr\Array_ $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function getNode(): Node
    {
        return new Node\Expr\MethodCall(
            new Node\Expr\MethodCall(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    new Node\Identifier('client')
                ),
                $this->endpoint
            ),
            new Node\Identifier('all'),
            [
                new Node\Arg(
                    new Node\Expr\Array_(
                        [
                            new Node\Expr\ArrayItem(
                                $this->search,
                                new Node\Scalar\String_('search'),
                            ),
                        ],
                    ),
                    false,
                    false,
                    [],
                    new Node\Identifier('queryParameters')
                ),
            ],
        );
    }
}
