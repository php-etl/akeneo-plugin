<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Component\FastMap\Compiler\Builder\IsolatedCodeAppendVariableBuilder;
use Kiboko\Component\FastMapConfig\ArrayBuilderInterface;
use Kiboko\Component\FastMapConfig\ObjectBuilderInterface;
use Kiboko\Contract\Mapping\CompilableMapperInterface;
use PhpParser\Builder;
use PhpParser\Node;

final class Inline implements Builder
{
    public function __construct(private ArrayBuilderInterface|ObjectBuilderInterface $mapper)
    {}

    public function getNode(): Node
    {
        $mapper = $this->mapper->getMapper();

        if (!$mapper instanceof CompilableMapperInterface) {
            throw new \RuntimeException(strtr(
                'The specified argument is invalid, expected %expected%, got %actual%.',
                [
                    '%expected%' => CompilableMapperInterface::class,
                    '%actual%' => get_debug_type($mapper),
                ]
            ));
        }

        $mapper->addContextVariable(new Node\Expr\Variable('lookup'));

        return new Node\Stmt\Expression(
            (new IsolatedCodeAppendVariableBuilder(
                new Node\Expr\Variable('input'),
                new Node\Expr\Variable('output'),
                array_merge(
                    $mapper->compile(new Node\Expr\Variable('output')),
                    [
                        new Node\Stmt\Return_(
                            new Node\Expr\Variable('output')
                        )
                    ]
                ),
                new Node\Expr\Variable('lookup'),
            ))->getNode()
        );
    }
}
