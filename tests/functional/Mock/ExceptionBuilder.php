<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Mock;

use PhpParser\Builder;
use PhpParser\Node;

final class ExceptionBuilder implements Builder
{
    /** @var Node\Expr[] */
    private readonly array $arguments;

    public function __construct(
        private readonly string $class,
        Node\Expr ...$arguments,
    ) {
        $this->arguments = $arguments;
    }

    public function getNode(): Node
    {
        return new Node\Expr\New_(
            class: new Node\Name\FullyQualified($this->class),
            args: [
                array_map(
                    fn (Node\Expr $value, int|string $key) => !is_string($key) ? new Node\Arg(value: $value) : new Node\Arg(value: $value, name: new Node\Identifier($key)),
                    $this->arguments,
                    array_keys($this->arguments),
                )
            ]
        );
    }
}
