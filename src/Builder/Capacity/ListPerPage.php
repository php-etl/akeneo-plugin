<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Builder\Capacity;

use PhpParser\Builder;
use PhpParser\Node;

final class ListPerPage implements Builder
{
    private null|Node\Expr|Node\Identifier $endpoint;
    private null|Node\Expr $search;

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

    public function withSearch(Node\Expr $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function getNode(): Node
    {
        return new Node\Stmt\Expression(
            expr: new Node\Expr\YieldFrom(
                expr: new Node\Expr\MethodCall(
                    new Node\Expr\MethodCall(
                        var: new Node\Expr\PropertyFetch(
                            var: new Node\Expr\Variable('this'),
                            name: new Node\Identifier('client')
                        ),
                        name: $this->endpoint
                    ),
                    new Node\Identifier('listPerPage'),
                    [
                        new Node\Arg(
                            value: new Node\Expr\Array_(
                                items: [
                                    new Node\Expr\ArrayItem(
                                        $this->search,
                                        new Node\Scalar\String_('search'),
                                    ),
                                ],
                                attributes: [
                                    'kind' => Node\Expr\Array_::KIND_SHORT,
                                ]
                            ),
                            name: new Node\Identifier('queryParameters')
                        ),
                    ],
                ),
            ),
        );
    }
}
