<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
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
            expr: new Node\Expr\Yield_(
                value: new Node\Expr\New_(
                    class: new Node\Name\FullyQualified(name: 'Kiboko\\Component\\Bucket\\AcceptanceResultBucket'),
                    args: [
                        new Node\Arg(
                            value: new Node\Expr\MethodCall(
                                var: new Node\Expr\MethodCall(
                                    var: new Node\Expr\PropertyFetch(
                                        var: new Node\Expr\Variable('this'),
                                        name: new Node\Identifier('client')
                                    ),
                                    name: $this->endpoint
                                ),
                                name: new Node\Identifier('all'),
                                args: [
                                    new Node\Arg(
                                        value: new Node\Expr\Array_(
                                            items: $this->compileSearch(),
                                            attributes: [
                                                'kind' => Node\Expr\Array_::KIND_SHORT,
                                            ]
                                        ),
                                        name: new Node\Identifier('queryParameters'),
                                    ),
                                ],
                            ),
                            unpack: true,
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
