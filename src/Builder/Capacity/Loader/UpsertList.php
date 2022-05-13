<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Loader;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use Kiboko\Plugin\Akeneo\MissingParameterException;
use PhpParser\Builder;
use PhpParser\Node;

final class UpsertList implements Builder
{
    private null|Node\Expr|Node\Identifier $endpoint;
    private null|Node\Expr $data;

    public function __construct()
    {
        $this->endpoint = null;
        $this->data = null;
    }

    public function withEndpoint(Node\Expr|Node\Identifier $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function withData(Node\Expr $lines): self
    {
        $this->data = $lines;

        return $this;
    }

    public function getNode(): Node
    {
        if ($this->endpoint === null) {
            throw new MissingEndpointException(
                message: 'Please check your capacity builder, you should have selected an endpoint.'
            );
        }
        if ($this->data === null) {
            throw new MissingParameterException(
                message: 'Please check your capacity builder, you should have provided some data.'
            );
        }

        return new Node\Stmt\While_(
            cond: new Node\Expr\Assign(
                var: new Node\Expr\Variable(name: 'lines'),
                expr: new Node\Expr\Yield_(),
            ),
            stmts: [
                new Node\Stmt\TryCatch(
                    stmts: [
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\MethodCall(
                                new Node\Expr\MethodCall(
                                    var: new Node\Expr\PropertyFetch(
                                        var: new Node\Expr\Variable('this'),
                                        name: new Node\Identifier('client'),
                                    ),
                                    name: $this->endpoint,
                                ),
                                new Node\Identifier('upsertList'),
                                [
                                    new Node\Arg(value: $this->data),
                                ],
                            ),
                        ),
                        new Node\Stmt\Expression(
                            expr: new Node\Expr\Yield_(
                                value: new Node\Expr\New_(
                                    class: new Node\Name\FullyQualified(name: 'Kiboko\\Component\\Bucket\\AcceptanceResultBucket'),
                                    args: [
                                        new Node\Arg(
                                            value: new Node\Expr\Variable('lines'),
                                        ),
                                    ],
                                ),
                            ),
                        ),
                    ],
                    catches: [
                        new Node\Stmt\Catch_(
                            types: [
                                new Node\Name\FullyQualified(
                                    name: 'Akeneo\\Pim\\ApiClient\\Exception\\HttpException',
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
                                                        new Node\Expr\ArrayItem(
                                                            value: new Node\Expr\Variable('lines'),
                                                            key: new Node\Scalar\String_('items'),
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
                                    new Node\Expr\Yield_(
                                        value: new Node\Expr\New_(
                                            class: new Node\Name\FullyQualified(
                                                name: 'Kiboko\\Component\\Bucket\\RejectionResultBucket'
                                            ),
                                            args: [
                                                new Node\Arg(
                                                    value: new Node\Expr\Variable('exception'),
                                                ),
                                                new Node\Arg(
                                                    value: new Node\Expr\Variable('lines'),
                                                ),
                                            ],
                                        ),
                                    ),
                                ),
                            ],
                        ),
                    ],
                ),
            ],
        );
    }
}
