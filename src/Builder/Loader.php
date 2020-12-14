<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class Loader implements Builder
{
    private bool $withEnterpriseSupport;
    private ?Node\Expr $client;
    private Node\Expr|Node\Identifier|null $method;
    private Node\Expr|Node\Identifier|null $endpoint;
    /** @var iterable<Node\Expr>  */
    private iterable $arguments;

    public function __construct()
    {
        $this->withEnterpriseSupport = false;
        $this->client = null;
        $this->method = null;
        $this->endpoint = null;
        $this->arguments = [];
    }

    public function withEnterpriseSupport(bool $withEnterpriseSupport): self
    {
        $this->withEnterpriseSupport = $withEnterpriseSupport;

        return $this;
    }

    public function withClient(Node\Expr $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function withEndpoint(Node\Expr|Node\Identifier $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function withMethod(Node\Expr|Node\Identifier|null $method = null, Node\Expr ...$arguments): self
    {
        $this->method = $method ?? new Node\Identifier('all');
        $this->arguments = $arguments;

        return $this;
    }

    public function getNode(): Node
    {
        return new Node\Expr\New_(
            class: new Node\Stmt\Class_(
                name: null,
                subNodes: [
                    'implements' => [
                        new Node\Name\FullyQualified(name: 'Kiboko\\Contracts\\ETL\\Pipeline\\LoaderInterface'),
                        new Node\Name\FullyQualified(name: 'Kiboko\\Contracts\\ETL\\Pipeline\\FlushableInterface'),
                    ],
                    'stmts' => [
                        new Node\Stmt\Property(
                            Node\Stmt\Class_::MODIFIER_PRIVATE,
                            [
                                new Node\Stmt\PropertyProperty('client'),
                            ],
                            [],
                            !$this->withEnterpriseSupport ?
                                new Node\Name\FullyQualified(name: 'Akeneo\\Pim\\ApiClient\\AkeneoPimClientInterface') :
                                new Node\Name\FullyQualified(name: 'Akeneo\\PimEnterprise\\ApiClient\\AkeneoPimEnterpriseClientInterface'),
                        ),
                        new Node\Stmt\ClassMethod(
                            new Node\Identifier('__construct'),
                            [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [
                                    new Node\Param(
                                        new Node\Expr\Variable('client'),
                                        null,
                                        !$this->withEnterpriseSupport ?
                                            new Node\Name\FullyQualified(name: 'Akeneo\\Pim\\ApiClient\\AkeneoPimClientInterface') :
                                            new Node\Name\FullyQualified(name: 'Akeneo\\PimEnterprise\\ApiClient\\AkeneoPimEnterpriseClientInterface')
                                    )
                                ],
                                'stmts' => [
                                    new Node\Stmt\Expression(
                                        new Node\Expr\Assign(
                                            new Node\Expr\PropertyFetch(
                                                new Node\Expr\Variable(name: 'this'),
                                                new Node\Identifier(name: 'client'),
                                            ),
                                            new Node\Expr\Variable(name: 'client'),
                                        ),
                                    ),
                                ],
                            ],
                        ),
                        new Node\Stmt\ClassMethod(
                            name: new Node\Identifier(name: 'load'),
                            subNodes: [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [],
                                'returnType' => new Node\Name\FullyQualified(name: 'Iterator'),
                                'stmts' => [
                                    new Node\Stmt\While_(
                                        cond: new Node\Expr\Assign(
                                            var: new Node\Expr\Variable(name: 'line'),
                                            expr: new Node\Expr\Yield_(),
                                        ),
                                        stmts: [
                                            new Node\Expr\MethodCall(
                                                var: new Node\Expr\MethodCall(
                                                    var: new Node\Expr\PropertyFetch(
                                                        var: new Node\Expr\Variable(name: 'this'),
                                                        name: new Node\Identifier(name: 'client')
                                                    ),
                                                    name: $this->endpoint
                                                ),
                                                name: $this->method,
                                                args: $this->arguments
                                            ),
                                            new Node\Expr\Yield_(
                                                value: new Node\Expr\New_(
                                                    class: new Node\Name\FullyQualified(name: '')
                                                )
                                            )
                                        ],
                                    ),
                                ],
                            ],
                        ),
                        new Node\Stmt\ClassMethod(
                            name: new Node\Identifier(name: 'flush'),
                            subNodes: [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [],
                                'returnType' => new Node\Name(name: 'iterable'),
                                'stmts' => [
                                    new Node\Stmt\Expression(
                                        expr: new Node\Expr\YieldFrom(
                                            expr: new Node\Expr\MethodCall(
                                                var: new Node\Expr\MethodCall(
                                                    var: new Node\Expr\PropertyFetch(
                                                        var: new Node\Expr\Variable(name: 'this'),
                                                        name: new Node\Identifier(name: 'client')
                                                    ),
                                                    name: $this->endpoint
                                                ),
                                                name: $this->method,
                                                args: $this->arguments,
                                            ),
                                        ),
                                    ),
                                ],
                            ],
                        ),
                    ],
                ],
            ),
            args: [
                new Node\Arg($this->client),
            ],
        );
    }
}
