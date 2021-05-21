<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Contract\Configurator\StepBuilderInterface;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ConditionalLookup implements StepBuilderInterface
{
    private ?Node\Expr $logger;
    private ?Node\Expr $rejection;
    private ?Node\Expr $state;
    /** @var iterable<array{0: string, 1: Node\Expr}> */
    private iterable $alternatives;
    private bool $withEnterpriseSupport;
    private ?Node\Expr $client;

    public function __construct(private ExpressionLanguage $interpreter)
    {
        $this->logger = null;
        $this->rejection = null;
        $this->state = null;
        $this->alternatives = [];
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

    public function addAlternative(string|Expression $condition, AlternativeLookup $lookup): self
    {
        $this->alternatives[] = [$condition, $lookup];

        return $this;
    }

    /** @return Node[] */
    private function compileAlternative(AlternativeLookup $lookup): array
    {
        return [
            new Node\Stmt\Expression(
                $lookup->getNode(),
            ),
        ];
    }

    private function compileAllAlternatives(): Node
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, null);

        $alternatives = $this->alternatives;
        [$condition, $alternative] = array_shift($alternatives);

        return new Node\Stmt\Do_(
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
            stmts: array_filter([
                new Node\Stmt\Expression(
                    new Node\Expr\Assign(
                        var: new Node\Expr\Variable('output'),
                        expr:new Node\Expr\Variable('input'),
                    ),
                ),
                new Node\Stmt\If_(
                    cond: (function () use ($parser, $condition) {
                        if (is_string($condition)) {
                            return new Node\Scalar\String_($condition);
                        }
                        if ($condition instanceof Expression) {
                            return $parser->parse('<?php ' . $this->interpreter->compile($condition, ['input']) . ';')[0]->expr;
                        }
                        return null;
                    })(),
                    subNodes: [
                        'stmts' => [
                            ...$this->compileAlternative($alternative),
                        ],
                        'elseifs' => array_map(
                            fn (string $condition, AlternativeLookup $lookup)
                                => new Node\Stmt\ElseIf_(
                                    cond: $parser->parse('<?php ' . $this->interpreter->compile($condition, ['input', 'lookup', 'output']) . ';')[0]->expr,
                                    stmts: $this->compileAlternative($lookup)
                                ),
                            array_column($alternatives, 0),
                            array_column($alternatives, 1)
                        ),
//                        'else' => new Node\Stmt\Else_(
//                            stmts: [
//                                new Node\Stmt\Expression(
//                                    new Node\Expr\Yield_(
//                                        new Node\Expr\Variable('line')
//                                    ),
//                                ),
//                            ],
//                        ),
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
                new Node\Arg(value: $this->logger ?? new Node\Expr\New_(new Node\Name\FullyQualified('Psr\\Log\\NullLogger'))),
            ],
        );
    }
}
