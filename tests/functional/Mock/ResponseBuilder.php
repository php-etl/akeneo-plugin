<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Mock;

use Laminas\Diactoros\Response;
use PhpParser\Builder;
use PhpParser\Node;

final class ResponseBuilder implements Builder
{
    public function __construct(
        private int $status,
    ) {
    }

    public static function fromFile(string $path, int $status): self
    {
        $instance = new self($status);
        $instance->a();
        return $instance;
    }

    public function getNode(): Node
    {
        return new Node\Expr\New_(
            class: new Node\Name(Response::class),
            args: [
                new Node\Arg(
                    value: new Node\Scalar\LNumber($this->status),
                    name: new Node\Identifier('status'),
                ),
            ]
        );
    }
}
