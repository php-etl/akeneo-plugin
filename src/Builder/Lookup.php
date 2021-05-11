<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Contract\Configurator\RepositoryInterface;
use Kiboko\Contract\Configurator\StepBuilderInterface;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class Lookup implements StepBuilderInterface
{
    private bool $withEnterpriseSupport;
    private ?LoggerInterface $logger;
    private ?Node\Expr $rejection;
    private ?Node\Expr $state;
    private ?Node\Expr $client;
    private ?Builder $capacity;
    private iterable $alternatives;
    private array $merge;

    public function __construct(private ExpressionLanguage $interpreter)
    {
        $this->withEnterpriseSupport = false;
        $this->client = null;
        $this->capacity = null;
        $this->alternatives = [];
        $this->merge = [];
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

    public function withAlternative(string $condition): self
    {
        $this->alternatives[] = [$condition];

        return $this;
    }

    public function withMerge(array $merge): self
    {
        $this->merge = $merge;

        return $this;
    }

    public function getFieldsNode(): Node
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, null);

        foreach ($this->merge as $item) {
            return new Node\Stmt\Expression(
                new Node\Expr\Assign(
                    var: new Node\Expr\ArrayDimFetch(
                    var: new Node\Expr\Variable('output'),
                    dim: new Node\Scalar\String_($item['field']),
                ),
                    expr: $parser->parse('<?php ' . $this->interpreter->compile($item['expression'], ['lookup', 'output']) . ';')[0]->expr,
                )
            );
        }
    }

    public function getNode(): Node
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, null);

        /** @var RepositoryInterface $repository */
        [$condition] = array_shift($this->alternatives);

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
                    'stmts' => [
                        new Node\Stmt\Expression(
                            new Node\Expr\Assign(
                                var: new Node\Expr\Variable('input'),
                                expr: new Node\Expr\Yield_(null)
                            ),
                        ),
                        new Node\Stmt\If_(
                            cond: $parser->parse('<?php ' . $this->interpreter->compile($condition, ['input', 'output']) . ';')[0]->expr,
                            subNodes: [
                                'stmts' => [
                                    new Node\Expr\Assign(
                                        var: new Node\Expr\Variable('lookup'),
                                        expr: new Node\Expr\Array_(
                                            [$this->capacity->getNode()]
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
                                            new Node\Stmt\Expression(
                                                new Node\Expr\Assign(
                                                    new Node\Expr\Variable('line'),
                                                    new Node\Expr\FuncCall(
                                                        new Node\Expr\Closure([
                                                            'params' => [
                                                                new Node\Param(
                                                                    var: new Node\Expr\Variable('input'),
                                                                ),
                                                                new Node\Param(
                                                                    var: new Node\Expr\Variable('lookup'),
                                                                )
                                                            ],
                                                            'stmts' => [
                                                                new Node\Expr\Assign(
                                                                    var: new Node\Expr\Variable('output'),
                                                                    expr: new Node\Expr\Array_(
                                                                        attributes: [
                                                                            'kind' => Node\Expr\Array_::KIND_SHORT
                                                                        ]
                                                                    )
                                                                ),
                                                                new Node\Stmt\Expression(
                                                                    new Node\Expr\Assign(
                                                                        var: new Node\Expr\Variable(
                                                                            name: 'output'
                                                                        ),
                                                                        expr: new Node\Expr\Variable('input'),
                                                                    )
                                                                ),
                                                                $this->getFieldsNode(),
                                                                new Node\Stmt\Return_(
                                                                    expr: new Node\Expr\Variable('output')
                                                                )
                                                            ],
                                                        ]),
                                                        [
                                                            new Node\Arg(new Node\Expr\Variable('input'))
                                                        ],
                                                    ),
                                                ),
                                            )
                                        ]
                                    ),
                                ]
                            ]
                        ),
                        new Node\Stmt\Expression(
                            new Node\Expr\Yield_(
                                new Node\Expr\New_(
                                    class: new Node\Name\FullyQualified(
                                    'Kiboko\\Component\\Bucket\\AcceptanceResultBucket',
                                ),
                                    args: [
                                    new Node\Arg(
                                        new Node\Expr\Variable('line'),
                                    ),
                                ],
                                ),
                            )
                        ),
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
