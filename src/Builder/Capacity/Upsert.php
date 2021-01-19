<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use Kiboko\Plugin\Akeneo\MissingParameterException;
use PhpParser\Builder;
use PhpParser\Node;

final class Upsert implements Builder
{
    private null|Node\Expr|Node\Identifier $endpoint;
    private null|Node\Expr $code;
    private null|Node\Expr $data;

    public function __construct()
    {
        $this->endpoint = null;
        $this->code = null;
        $this->data = null;
    }

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

    public function withData(Node\Expr $line): self
    {
        $this->data = $line;

        return $this;
    }

    public function getNode(): Node
    {
        if ($this->endpoint === null) {
            throw new MissingEndpointException(
                message: 'Please check your capacity builder, you should have selected an endpoint.'
            );
        }
        if ($this->code === null) {
            throw new MissingParameterException(
                message: 'Please check your capacity builder, you should have provided a code.'
            );
        }
        if ($this->data === null) {
            throw new MissingParameterException(
                message: 'Please check your capacity builder, you should have provided some data.'
            );
        }

        return new Node\Stmt\While_(
            cond: new Node\Expr\Assign(
                var: new Node\Expr\Variable(name: 'line'),
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
                                new Node\Identifier('upsert'),
                                [
                                    new Node\Arg(value: $this->code),
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
                                            value: new Node\Expr\Variable('line'),
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
                                                value: new Node\Expr\PropertyFetch(
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
                                                            value: new Node\Expr\Variable('line'),
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
                                                    value: new Node\Expr\Variable('item'),
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
