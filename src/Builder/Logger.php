<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class Logger implements Builder
{
    public function __construct(private ?Node\Expr $logger = null)
    {}

    public function withLogger(Node\Expr $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function getNode(): Node\Expr
    {
        if ($this->logger === null) {
            return new Node\Expr\New_(
                class: new Node\Name\FullyQualified('Psr\\Log\\NullLogger'),
            );
        }

        return $this->logger;
    }
}
