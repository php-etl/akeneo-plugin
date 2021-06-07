<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Contract\Configurator\StepBuilderInterface;
use PhpParser\Node;

final class Lookup implements StepBuilderInterface
{
    private ?Node\Expr $logger;
    private bool $withEnterpriseSupport;
    private ?Node\Expr $client;

    public function __construct(private AlternativeLookup $alternative)
    {
        $this->logger = null;
        $this->rejection = null;
        $this->state = null;
        $this->withEnterpriseSupport = false;
        $this->client = null;
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

    /** @return Node[] */
    private function compileAlternative(AlternativeLookup $lookup): array
    {
        return [
            new Node\Stmt\Expression(
                new Node\Expr\Assign(
                    var: new Node\Expr\Variable('output'),
                    expr: new Node\Expr\Variable('input'),
                ),
            ),
            $lookup->getNode(),
        ];
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
                                'returnType' => new Node\Name\FullyQualified(\Generator::class),
                                'stmts' => array_filter([
                                    new Node\Stmt\Expression(
                                        new Node\Expr\Assign(
                                            var: new Node\Expr\Variable('input'),
                                            expr: new Node\Expr\Yield_(null)
                                        ),
                                    ),
                                    new Node\Stmt\Do_(
                                        cond: new Node\Expr\Assign(
                                            var: new Node\Expr\Variable('input'),
                                            expr: new Node\Expr\Yield_(
                                                value: new Node\Expr\New_(
                                                    class: new Node\Name\FullyQualified('Kiboko\\Component\\Bucket\\AcceptanceResultBucket'),
                                                    args: [
                                                        new Node\Arg(
                                                            value: new Node\Expr\Variable('output'),
                                                        ),
                                                    ],
                                                )
                                            )
                                        ),
                                        stmts: $this->compileAlternative($this->alternative)
                                    ),
                                ]),
                            ],
                        ),
                    ],
                ],
            ),
            args: [
                new Node\Arg(value: $this->client),
                new Node\Arg(value: $this->logger ?? new Node\Expr\New_(new Node\Name\FullyQualified('Psr\\Log\\NullLogger'))),
            ],
        );
    }
}
