<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class Loader implements Builder
{
    private bool $withEnterpriseSupport;
    private ?Node\Expr $client;
    private ?Node\Expr $logger;
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

    public function withLogger(Node\Expr $logger): self
    {
        $this->logger = $logger;

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
                        new Node\Name\FullyQualified(name: 'Kiboko\\Contract\\Pipeline\\LoaderInterface'),
                    ],
                    'stmts' => [
                        new Node\Stmt\ClassMethod(
                            name: new Node\Identifier(name: '__construct'),
                            subNodes: [
                                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                                'params' => [
                                    new Node\Param(
                                        var: new Node\Expr\Variable('client'),
                                        type: !$this->withEnterpriseSupport ?
                                            new Node\Name\FullyQualified(name: 'Akeneo\\Pim\\ApiClient\\AkeneoPimClientInterface') :
                                            new Node\Name\FullyQualified(name: 'Akeneo\\PimEnterprise\\ApiClient\\AkeneoPimEnterpriseClientInterface'),
                                        flags: Node\Stmt\Class_::MODIFIER_PUBLIC,
                                    ),
                                    new Node\Param(
                                        var: new Node\Expr\Variable('logger'),
                                        type: new Node\Name\FullyQualified(name: 'Psr\\Log\\LoggerInterface'),
                                        flags: Node\Stmt\Class_::MODIFIER_PUBLIC,
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
                                    new Node\Stmt\TryCatch(
                                        stmts: [
                                            $this->capacity->getNode(),
                                        ],
                                        catches: [
                                            new Node\Stmt\Catch_(
                                                types: [
                                                    new Node\Name\FullyQualified('Throwable')
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
                new Node\Arg(value: $this->logger),
            ],
        );
    }
}
