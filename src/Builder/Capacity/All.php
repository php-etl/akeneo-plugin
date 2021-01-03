<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Builder\Capacity;

use Kiboko\Component\ETL\Flow\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class All implements Builder
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
        if ($this->endpoint === null) {
            throw new MissingEndpointException(
                message: 'Please check your capacity builder, you should have selected an endpoint.'
            );
        }

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
                    new Node\Identifier('all'),
                    [
                        new Node\Arg(
                            value: new Node\Expr\Array_(
                                items: $this->compileSearch(),
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

    private function compileSearch(): array
    {
        if ($this->search === null) {
            return [];
        }

        return [
            new Node\Expr\ArrayItem(
                $this->search,
                new Node\Scalar\String_('search'),
            ),
        ];
    }
}
