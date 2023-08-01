<?php

namespace Kiboko\Plugin\Akeneo\DTO;

use PhpParser\Node;

final class GetDefaultEndpoint
{
    public function __construct(
        public Node\Expr $identifier,
    ) {
    }
}
