<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class ListPerPage implements Builder
{
    private Node\Expr|Node\Identifier|null $endpoint = null;
    private ?Node\Expr $search = null;
    private ?Node\Expr $code = null;

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

    public function withCode(Node\Expr $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getNode(): Node
    {
        if (null === $this->endpoint) {
            throw new MissingEndpointException(message: 'Please check your capacity builder, you should have selected an endpoint.');
        }

        return
            new Node\Stmt\Foreach_(
                expr: new Node\Expr\MethodCall(
                    var: new Node\Expr\MethodCall(
                        var: new Node\Expr\PropertyFetch(
                            var: new Node\Expr\Variable('this'),
                            name: new Node\Identifier('client')
                        ),
                        name: $this->endpoint
                    ),
                    name: new Node\Identifier('listPerPage'),
                    args: array_filter(
                        [
                            new Node\Arg(
                                value: new Node\Expr\Array_(
                                    items: $this->compileSearch(),
                                    attributes: [
                                        'kind' => Node\Expr\Array_::KIND_SHORT,
                                    ]
                                ),
                                name: new Node\Identifier('queryParameters'),
                            ),
                            null !== $this->code ? new Node\Arg(
                                value: $this->code,
                                name: new Node\Identifier('attributeCode'),
                            ) : null,
                        ],
                    ),
                ),
                valueVar: new Node\Expr\Variable('item'),
                subNodes: [
                    'stmts' => [
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\Yield_(
                                value: new Node\Expr\New_(
                                    class: new Node\Name\FullyQualified(name: \Kiboko\Component\Bucket\AcceptanceResultBucket::class),
                                    args: [
                                        new Node\Arg(
                                            new Node\Expr\Variable('item')
                                        ),
                                    ],
                                ),
                            ),
                        ),
                    ],
                ]
            );
    }

    private function compileSearch(): array
    {
        if (null === $this->search) {
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
