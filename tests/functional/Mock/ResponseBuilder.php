<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Mock;

use Laminas\Diactoros\Response;
use PhpParser\Builder;
use PhpParser\Node;

final readonly class ResponseBuilder implements Builder
{
    public function __construct(
        private string $path,
    ) {
    }

    public function getNode(): Node
    {
        return new Node\Expr\Include_(new Node\Scalar\String_($this->path), Node\Expr\Include_::TYPE_INCLUDE);
    }
}
