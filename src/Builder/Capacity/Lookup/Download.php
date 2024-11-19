<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use Kiboko\Component\SatelliteToolbox\Builder\IsolatedValueAppendingBuilder;
use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class Download implements Builder
{
    private Node\Expr|Node\Identifier|null $endpoint = null;
    private ?Node\Expr $file = null;

    public function withEndpoint(Node\Expr|Node\Identifier $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function withFile(Node\Expr $file): self
    {
        $this->file = $file;

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
                                null !== $this->file ? new Node\Arg(
                                    value: $this->file,
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
                                var: new Node\Expr\Variable('data'),
                                expr: new Node\Expr\MethodCall(
                                    var: new Node\Expr\MethodCall(
                                        var: new Node\Expr\PropertyFetch(
                                            var: new Node\Expr\Variable('this'),
                                            name: new Node\Identifier('client'),
                                        ),
                                        name: $this->endpoint
                                    ),
                                    name: new Node\Identifier('download'),
                                    args: array_filter(
                                        [
                                            null !== $this->file ? new Node\Arg(
                                                value: $this->file,
                                                name: new Node\Identifier('code'),
                                            ) : null,
                                        ],
                                    ),
                                ),
                            ),
                        ),
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\Assign(
                                var: new Node\Expr\Variable('image'),
                                expr: new Node\Expr\FuncCall(
                                    name: new Node\Name('fopen'),
                                    args: [
                                        new Node\Arg(new Node\Scalar\String_('php://temp')),
                                        new Node\Arg(new Node\Scalar\String_('r+')),
                                    ]
                                ),
                            ),
                        ),
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\Assign(
                                var: new Node\Expr\Variable('imageFromAkeneo'),
                                expr: new Node\Expr\MethodCall(
                                    var: new Node\Expr\MethodCall(
                                        var: new Node\Expr\Variable('data'),
                                        name: new Node\Identifier('getBody')
                                    ),
                                    name: new Node\Identifier('detach')
                                )
                            ),
                        ),
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\FuncCall(
                                name: new Node\Name('stream_copy_to_stream'),
                                args: [
                                    new Node\Arg(new Node\Expr\Variable('imageFromAkeneo')),
                                    new Node\Arg(new Node\Expr\Variable('image')),
                                ]
                            ),
                        ),
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\FuncCall(
                                name: new Node\Name('fseek'),
                                args: [
                                    new Node\Arg(new Node\Expr\Variable('image')),
                                    new Node\Arg(new Node\Scalar\LNumber(0)),
                                ]
                            ),
                        ),
                        new Node\Stmt\If_(
                            cond: new Node\Expr\FuncCall(
                                name: new Node\Name('preg_match'),
                                args: [
                                    new Node\Arg(new Node\Scalar\String_('/filename="([^"]+)"/')),
                                    new Node\Arg(
                                        new Node\Expr\ArrayDimFetch(
                                            var: new Node\Expr\MethodCall(
                                                var: new Node\Expr\Variable('data'),
                                                name: new Node\Identifier('getHeader'),
                                                args: [
                                                    new Node\Arg(new Node\Scalar\String_('content-disposition')),
                                                ]
                                            ),
                                            dim: new Node\Scalar\LNumber(0)
                                        )
                                    ),
                                    new Node\Expr\Variable('matches'),
                                ]
                            ),
                            subNodes: [
                                'stmts' => [
                                    new Node\Stmt\Expression(
                                        new Node\Expr\FuncCall(
                                            name: new Node\Name('stream_context_set_option'),
                                            args: [
                                                new Node\Arg(new Node\Expr\Variable('image')),
                                                new Node\Arg(new Node\Scalar\String_('http')),
                                                new Node\Arg(new Node\Scalar\String_('filename')),
                                                new Node\Arg(
                                                    new Node\Expr\ArrayDimFetch(
                                                        var: new Node\Expr\Variable('matches'),
                                                        dim: new Node\Scalar\LNumber(1)
                                                    )
                                                ),
                                            ]
                                        )
                                    ),
                                ],
                            ]
                        ),
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\FuncCall(
                                name: new Node\Name('fclose'),
                                args: [
                                    new Node\Arg(new Node\Expr\Variable('imageFromAkeneo')),
                                ]
                            ),
                        ),
                    ],
                    catches: [
                        new Node\Stmt\Catch_(
                            types: [
                                new Node\Name\FullyQualified(\Akeneo\Pim\ApiClient\Exception\HttpException::class),
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
                    expr: new Node\Expr\Variable('image'),
                ),
            ],
            new Node\Expr\Variable('bucket')
        ))->getNode();
    }
}
