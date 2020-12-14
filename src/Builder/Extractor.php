<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class Extractor implements Builder
{
    private bool $withEnterpriseSupport;
    private ?Node\Expr $client;
    private ?Builder $capacity;

    public function __construct()
    {
        $this->withEnterpriseSupport = false;
        $this->client = null;
        $this->capacity = null;
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

    public function withCapacity(Builder $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getNode(): Node
    {
        return new Node\Expr\New_(
            class: new Node\Stmt\Class_(
                name: null,
                subNodes: [
                    'implements' => [
                        new Node\Name\FullyQualified('Kiboko\\Contracts\\ETL\\Pipeline\\ExtractorInterface'),
                    ],
                    'stmts' => [
                        new Node\Stmt\Property(
                            flags: Node\Stmt\Class_::MODIFIER_PRIVATE,
                            props: [
                                new Node\Stmt\PropertyProperty('client'),
                            ],
                            type: !$this->withEnterpriseSupport ?
                                new Node\Name\FullyQualified('Akeneo\\Pim\\ApiClient\\AkeneoPimClientInterface') :
                                new Node\Name\FullyQualified('Akeneo\\PimEnterprise\\ApiClient\\AkeneoPimEnterpriseClientInterface'),
                        ),
                        new Node\Stmt\ClassMethod(
                            name: new Node\Identifier(name: '__construct'),
                            subNodes: [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [
                                    new Node\Param(
                                        var: new Node\Expr\Variable('client'),
                                        type: !$this->withEnterpriseSupport ?
                                            new Node\Name\FullyQualified('Akeneo\\Pim\\ApiClient\\AkeneoPimClientInterface') :
                                            new Node\Name\FullyQualified('Akeneo\\PimEnterprise\\ApiClient\\AkeneoPimEnterpriseClientInterface')
                                    )
                                ],
                                'stmts' => [
                                    new Node\Stmt\Expression(
                                        expr: new Node\Expr\Assign(
                                            var: new Node\Expr\PropertyFetch(
                                                var: new Node\Expr\Variable('this'),
                                                name: new Node\Identifier('client'),
                                            ),
                                            expr: new Node\Expr\Variable('client'),
                                        ),
                                    ),
                                ],
                            ],
                        ),
                        new Node\Stmt\ClassMethod(
                            name: new Node\Identifier('extract'),
                            subNodes: [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [],
                                'returnType' => new Node\Name(name: 'iterable'),
                                'stmts' => [
                                    new Node\Stmt\Expression(
                                        expr: new Node\Expr\YieldFrom(
                                            expr: $this->capacity->getNode(),
                                        ),
                                    ),
                                ],
                            ],
                        ),
                    ],
                ],
            ),
            args: [
                new Node\Arg(value: $this->client),
            ],
        );
    }
}
