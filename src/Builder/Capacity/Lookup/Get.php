<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class Get implements Builder
{
    private Node\Expr|Node\Identifier|null $endpoint = null;
    private ?Node\Expr $identifier = null;
    private ?Node\Expr $code = null;
    private string $type = '';

    public function withEndpoint(Node\Expr|Node\Identifier $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function withCode(Node\Expr $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function withIdentifier(Node\Expr $identifier): self
    {
        $this->identifier = $identifier;

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

        return new Node\Stmt\TryCatch(
            stmts: [
                new Node\Stmt\Expression(
                    expr: new Node\Expr\Assign(
                        var: new Node\Expr\Variable('lookup'),
                        expr: new Node\Expr\MethodCall(
                            var: new Node\Expr\MethodCall(
                                var: new Node\Expr\PropertyFetch(
                                    var: new Node\Expr\Variable('this'),
                                    name: new Node\Identifier('client')
                                ),
                                name: $this->endpoint
                            ),
                            name: new Node\Identifier('get'),
                            args: array_filter(
                                [
                                    new Node\Arg(
                                        value: $this->identifier,
                                        name: $this->compileIdentifierNamedArgument($this->type),
                                    ),
                                    null !== $this->code ? new Node\Arg(
                                        value: $this->code,
                                        name: $this->compileCodeNamedArgument($this->type),
                                    ) : null,
                                ],
                            ),
                        )
                    )
                ),
            ],
            catches: [
                new Node\Stmt\Catch_(
                    types: [
                        new Node\Name\FullyQualified(
                            name: \Akeneo\Pim\ApiClient\Exception\NotFoundHttpException::class,
                        ),
                    ],
                    var: new Node\Expr\Variable('exception'),
                    stmts: [
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\MethodCall(
                                var: new Node\Expr\PropertyFetch(
                                    var: new Node\Expr\Variable('this'),
                                    name: 'logger',
                                ),
                                name: new Node\Identifier('warning'),
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
                                                new Node\Expr\ArrayItem(
                                                    value: new Node\Expr\Variable('input'),
                                                    key: new Node\Scalar\String_('item'),
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
                                name: new Node\Name('reject'),
                                args: [
                                    new Node\Arg(
                                        value: new Node\Expr\Variable('input')
                                    ),
                                ]
                            ),
                        ),
                        new Node\Stmt\Return_(),
                    ]
                ),
            ]
        );
    }

    private function compileCodeNamedArgument(string $type): Node\Identifier
    {
        return match ($type) {
            'referenceEntityRecord' => new Node\Identifier('referenceEntityCode'),
            'assetManager' => new Node\Identifier('assetFamilyCode'),
            default => new Node\Identifier('attributeCode'),
        };
    }

    private function compileIdentifierNamedArgument(string $type): Node\Identifier
    {
        return match ($type) {
            'referenceEntityRecord' => new Node\Identifier('recordCode'),
            'assetManager' => new Node\Identifier('assetCode'),
            default => new Node\Identifier('code'),
        };
    }
}
