<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use Kiboko\Component\SatelliteToolbox\Builder\IsolatedValueAppendingBuilder;
use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class ListPerPage implements Builder
{
    private null|Node\Expr|Node\Identifier $endpoint;
    private null|Node\Expr $search;
    private null|Node\Expr $code;
    private null|string $type;

    public function __construct()
    {
        $this->endpoint = null;
        $this->search = null;
        $this->code = null;
        $this->type = null;
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

    public function withCode(?Node\Expr $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function withType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNode(): Node
    {
        if (null === $this->endpoint) {
            throw new MissingEndpointException(message: 'Please check your capacity builder, you should have selected an endpoint.');
        }

        return (new IsolatedValueAppendingBuilder(
            new Node\Expr\Variable('input'),
            new Node\Expr\Variable('lookup'),
            [
                new Node\Stmt\If_(
                    cond: new Node\Expr\FuncCall(
                        name: new Node\Name('is_null'),
                        args: array_filter(
                            [
                                null !== $this->code ? new Node\Arg(
                                    value: $this->code,
                                ) : null,
                            ],
                        ),
                    ),
                    subNodes: [
                        'stmts' => [
                            new Node\Stmt\Return_(
                                expr: new Node\Expr\ConstFetch(
                                    name: new Node\Name(name: 'null'),
                                ),
                            ),
                        ],
                    ],
                ),
                new Node\Stmt\TryCatch(
                    stmts: [
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\Assign(
                                var: new Node\Expr\Variable('items'),
                                expr: new Node\Expr\MethodCall(
                                    var: new Node\Expr\MethodCall(
                                        var: new Node\Expr\PropertyFetch(
                                            var: new Node\Expr\Variable('this'),
                                            name: new Node\Identifier('client'),
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
                                                name: $this->compileCodeNamedArgument($this->type),
                                            ) : null,
                                        ],
                                    ),
                                ),
                            ),
                        ),
                    ],
                    catches: [
                        new Node\Stmt\Catch_(
                            types: [
                                new Node\Name\FullyQualified('Akeneo\Pim\ApiClient\Exception\HttpException'),
                            ],
                            var: new Node\Expr\Variable('exception'),
                            stmts: [
                                new Node\Stmt\Expression(
                                    expr: new Node\Expr\MethodCall(
                                        var: new Node\Expr\PropertyFetch(
                                            var: new Node\Expr\Variable('this'),
                                            name: 'logger',
                                        ),
                                        name: new Node\Identifier('error'),
                                        args: [
                                            new Node\Arg(
                                                value: new Node\Expr\MethodCall(
                                                    var: new Node\Expr\Variable('exception'),
                                                    name: new Node\Identifier('getMessage'),
                                                ),
                                            ),
                                            new Node\Arg(
                                                value: new Node\Expr\Array_(
                                                    items: [
                                                        new Node\Expr\ArrayItem(
                                                            value: new Node\Expr\Variable('exception'),
                                                            key: new Node\Scalar\String_('exception'),
                                                        ),
                                                    ],
                                                    attributes: [
                                                        'kind' => Node\Expr\Array_::KIND_SHORT,
                                                    ],
                                                ),
                                            ),
                                        ],
                                    ),
                                ),
                                new Node\Stmt\Expression(
                                    expr: new Node\Expr\MethodCall(
                                        var: new Node\Expr\Variable('bucket'),
                                        name: new Node\Identifier('reject'),
                                        args: [
                                            new Node\Arg(
                                                new Node\Expr\Variable('input'),
                                            ),
                                        ],
                                    )
                                ),
                            ],
                        ),
                    ],
                ),
                new Node\Stmt\Return_(
                    expr: new Node\Expr\Variable('items'),
                ),
            ],
            new Node\Expr\Variable('bucket')
        ))->getNode();
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

    private function compileCodeNamedArgument(string $type): Node\Identifier
    {
        return match ($type) {
            'assetManager' => new Node\Identifier('assetFamilyCode'),
            default => new Node\Identifier('attributeCode')
        };
    }
}
