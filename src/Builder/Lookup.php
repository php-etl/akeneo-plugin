<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Contract\Configurator\StepBuilderInterface;
use PhpParser\Builder;
use PhpParser\Node;
use Psr\Log\LoggerInterface;

final class Lookup implements StepBuilderInterface
{
    private bool $withEnterpriseSupport;
    private ?LoggerInterface $logger;
    private ?Node\Expr $rejection;
    private ?Node\Expr $state;
    private ?Node\Expr $client;
    private ?Builder $capacity;

    public function __construct(private string $condition, private array $lookup, private array $merge)
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

    public function withRejection(Node\Expr $rejection): self
    {
        $this->rejection = $rejection;

        return $this;
    }

    public function withState(Node\Expr $state): self
    {
        $this->state = $state;

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
                new Node\Name\FullyQualified(name: 'Kiboko\\Contract\\Pipeline\\TransformerInterface'),
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
                    name: new Node\Identifier(name: 'transform'),
                    subNodes: [
                    'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                    'params' => [],
                    'returnType' => new Node\Name(name: 'iterable'),
                    'stmts' => [
//                        new Node\Expr\Assign(
//                            var: new Node\Expr\Variable('input'),
//                            expr: new Node\Expr\New_($this->capacity->getNode()),
//                        ),
                        new Node\Stmt\If_(
                            cond: new Node\Scalar\String_($this->condition),
                            subNodes: [
                                'stmts' => [
                                    new Node\Stmt\Expression(
                                        new Node\Expr\Assign(
                                            var: new Node\Expr\Variable('line'),
                                            expr: new Node\Expr\Yield_(null)
                                        ),
                                    ),
                                    new Node\Stmt\Do_(
                                        cond: new Node\Expr\Assign(
                                            var: new Node\Expr\Variable('line'),
                                            expr: new Node\Expr\Yield_(
                                                new Node\Expr\New_(
                                                    class: new Node\Name\FullyQualified(
                                                    'Kiboko\\Component\\Bucket\\AcceptanceResultBucket'
                                                ),
                                                    args: [
                                                    new Node\Arg(
                                                        new Node\Expr\Variable('line'),
                                                    ),
                                                ],
                                                )
                                            )
                                        ),
                                        stmts: [
                                            new Node\Expr\FuncCall(
                                                name: new Node\Scalar\String_('merge'),
                                                args: [
                                                    new Node\Arg(new Node\Scalar\String_('d'))
                                                ]
                                            )
                                        ]
                                    )
                                ]
                            ]
                        )
                    ],
                ],
                ),
            ],
        ],
        ), args: [
            new Node\Arg(value: $this->client),
//            new Node\Arg(value: $this->logger),
        ],
        );
    }
}
