<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Mock;

use PhpParser\Builder;
use PhpParser\Node;

final class ResponseFactoryBuilder implements Builder
{
    public function getNode(): Node\Expr
    {
        return new Node\Expr\New_(
            class: new Node\Name\FullyQualified(ResponseFactory::class)
        );
    }
}
