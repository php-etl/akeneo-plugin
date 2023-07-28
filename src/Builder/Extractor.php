<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Contract\Configurator\StepBuilderInterface;
use PhpParser\Builder;
use PhpParser\Node;

final class Extractor implements StepBuilderInterface
{
    private ?Node\Expr $logger = null;
    private ?Node\Expr $client = null;

    public function __construct(
        private readonly Builder $capacity,
    ) {
    }

    public function withClient(Node\Expr $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function withLogger(Node\Expr $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function withRejection(Node\Expr $rejection): self
    {
        return $this;
    }

    public function withState(Node\Expr $state): self
    {
        return $this;
    }

    public function getNode(): Node
    {
        return new Node\Expr\New_(
            class: new Node\Stmt\Class_(
                name: null,
                subNodes: [
                    'implements' => [
                        new Node\Name\FullyQualified(name: \Kiboko\Contract\Pipeline\ExtractorInterface::class),
                    ],
                    'stmts' => [
                        new Node\Stmt\ClassMethod(
                            name: new Node\Identifier(name: '__construct'),
                            subNodes: [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [
                                    new Node\Param(
                                        var: new Node\Expr\Variable('client'),
                                        type: new Node\Name\FullyQualified(name: \Akeneo\Pim\ApiClient\AkeneoPimClientInterface::class),
                                        flags: Node\Stmt\Class_::MODIFIER_PUBLIC,
                                    ),
                                    new Node\Param(
                                        var: new Node\Expr\Variable('logger'),
                                        type: new Node\Name\FullyQualified(name: \Psr\Log\LoggerInterface::class),
                                        flags: Node\Stmt\Class_::MODIFIER_PUBLIC,
                                    ),
                                ],
                            ],
                        ),
                        new Node\Stmt\ClassMethod(
                            name: new Node\Identifier(name: 'extract'),
                            subNodes: [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [],
                                'returnType' => new Node\Name(name: 'iterable'),
                                'stmts' => [
                                    new Node\Stmt\TryCatch(
                                        stmts: [
                                            $this->capacity->getNode(),
                                        ],
                                        catches: [
                                            new Node\Stmt\Catch_(
                                                types: [
                                                    new Node\Name\FullyQualified(
                                                        name: \Akeneo\Pim\ApiClient\Exception\UnprocessableEntityHttpException::class
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
                                                                                value: new Node\Expr\MethodCall(
                                                                                    var: new Node\Expr\Variable('exception'),
                                                                                    name: new Node\Identifier('getResponseErrors'),
                                                                                ),
                                                                                key: new Node\Scalar\String_('errors'),
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
                                                ],
                                            ),
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
                                                ],
                                            ),
                                            new Node\Stmt\Catch_(
                                                types: [
                                                    new Node\Name\FullyQualified('Throwable'),
                                                ],
                                                var: new Node\Expr\Variable('exception'),
                                                stmts: [
                                                    new Node\Stmt\Expression(
                                                        expr: new Node\Expr\MethodCall(
                                                            var: new Node\Expr\PropertyFetch(
                                                                var: new Node\Expr\Variable('this'),
                                                                name: 'logger',
                                                            ),
                                                            name: new Node\Identifier('critical'),
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
                                                ],
                                            ),
                                        ],
                                    ),
                                ],
                            ],
                        ),
                    ],
                ],
            ),
            args: [
                new Node\Arg(value: $this->client),
                new Node\Arg(value: $this->logger ?? new Node\Expr\New_(new Node\Name\FullyQualified(\Psr\Log\NullLogger::class))),
            ],
        );
    }
}
