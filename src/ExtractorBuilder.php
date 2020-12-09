<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo;

use PhpParser\Builder;
use PhpParser\Node;

final class ExtractorBuilder implements Builder
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
            new Node\Stmt\Class_(
                null,
                [
                    'implements' => [
                        new Node\Name\FullyQualified('Kiboko\\Component\\ETL\\Contracts\\ExtractorInterface'),
                    ],
                    'stmts' => [
                        new Node\Stmt\Property(
                            Node\Stmt\Class_::MODIFIER_PRIVATE,
                            [
                                new Node\Stmt\PropertyProperty('client'),
                            ],
                            [],
                            !$this->withEnterpriseSupport ?
                                new Node\Name\FullyQualified('Akeneo\\Pim\\ApiClient\\AkeneoPimClientInterface') :
                                new Node\Name\FullyQualified('Akeneo\\PimEnterprise\\ApiClient\\AkeneoPimEnterpriseClientInterface'),
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
                                            new Node\Name\FullyQualified('Akeneo\\Pim\\ApiClient\\AkeneoPimClientInterface') :
                                            new Node\Name\FullyQualified('Akeneo\\PimEnterprise\\ApiClient\\AkeneoPimEnterpriseClientInterface')
                                    )
                                ],
                                'stmts' => [
                                    new Node\Stmt\Expression(
                                        new Node\Expr\Assign(
                                            new Node\Expr\PropertyFetch(
                                                new Node\Expr\Variable('this'),
                                                new Node\Identifier('client'),
                                            ),
                                            new Node\Expr\Variable('client'),
                                        ),
                                    ),
                                ],
                            ],
                        ),
                        new Node\Stmt\ClassMethod(
                            new Node\Identifier('extract'),
                            [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [],
                                'returnType' => new Node\Name('iterable'),
                                'stmts' => [
                                    new Node\Stmt\Expression(
                                        new Node\Expr\YieldFrom(
                                            new Node\Expr\MethodCall(
                                                new Node\Expr\MethodCall(
                                                    new Node\Expr\PropertyFetch(
                                                        new Node\Expr\Variable('this'),
                                                        new Node\Identifier('client')
                                                    ),
                                                    $this->endpoint
                                                ),
                                                $this->method,
                                                $this->arguments
                                            ),
                                        ),
                                    ),
                                ],
                            ],
                        ),
                    ],
                ],
            ),
            [
                new Node\Arg($this->client),
            ],
        );
    }
}
