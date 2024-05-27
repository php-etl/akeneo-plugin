<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Contract\Configurator\StepBuilderInterface;
use PhpParser\Node;

final class ConditionalLookup implements StepBuilderInterface
{
    private ?Node\Expr $logger = null;
    /** @var iterable<array{0: Node\Expr, 1: AlternativeLookup}> */
    private iterable $alternatives = [];
    private ?Node\Expr $client = null;

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

    public function addAlternative(Node\Expr $condition, AlternativeLookup $lookup): self
    {
        $this->alternatives[] = [$condition, $lookup];

        return $this;
    }

    /** @return array<int, Node> */
    private function compileAlternative(AlternativeLookup $lookup): array
    {
        return [
            $lookup->getNode(),
        ];
    }

    private function compileAllAlternatives(): Node
    {
        $alternatives = $this->alternatives;
        [$condition, $alternative] = array_shift($alternatives);

        return new Node\Stmt\Do_(
            cond: new Node\Expr\Assign(
                var: new Node\Expr\Variable('input'),
                expr: new Node\Expr\Yield_(
                    value: new Node\Expr\Variable('bucket')
                )
            ),
            stmts: array_filter([
                new Node\Stmt\Expression(
                    expr: new Node\Expr\Assign(
                        var: new Node\Expr\Variable('bucket'),
                        expr: new Node\Expr\New_(
                            new Node\Name\FullyQualified(\Kiboko\Component\Bucket\ComplexResultBucket::class)
                        )
                    )
                ),
                new Node\Stmt\Expression(
                    new Node\Expr\Assign(
                        var: new Node\Expr\Variable('output'),
                        expr: new Node\Expr\Variable('input'),
                    ),
                ),
                new Node\Stmt\If_(
                    cond: $condition,
                    subNodes: [
                        'stmts' => [
                            ...$this->compileAlternative($alternative),
                        ],
                        'elseifs' => array_map(
                            fn (Node\Expr $condition, AlternativeLookup $lookup) => new Node\Stmt\ElseIf_(
                                cond: $condition,
                                stmts: $this->compileAlternative($lookup)
                            ),
                            array_column($alternatives, 0),
                            array_column($alternatives, 1)
                        ),
                        'else' => new Node\Stmt\Else_(
                            stmts: [
                                new Node\Stmt\Expression(
                                    expr: new Node\Expr\MethodCall(
                                        var: new Node\Expr\Variable('bucket'),
                                        name: new Node\Name('accept'),
                                        args: [
                                            new Node\Arg(
                                                value: new Node\Expr\Variable('output')
                                            ),
                                        ]
                                    )
                                ),
                            ],
                        ),
                    ],
                ),
            ])
        );
    }

    public function getNode(): Node
    {
        return new Node\Expr\New_(
            class: new Node\Stmt\Class_(
                name: null,
                subNodes: [
                    'implements' => [
                        new Node\Name\FullyQualified(name: \Kiboko\Contract\Pipeline\TransformerInterface::class),
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
                            name: new Node\Identifier(name: 'transform'),
                            subNodes: [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [
                                ],
                                'returnType' => new Node\Name\FullyQualified(\Generator::class),
                                'stmts' => [
                                    new Node\Stmt\Expression(
                                        new Node\Expr\Assign(
                                            var: new Node\Expr\Variable('input'),
                                            expr: new Node\Expr\Yield_(null)
                                        ),
                                    ),
                                    $this->compileAllAlternatives(),
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
