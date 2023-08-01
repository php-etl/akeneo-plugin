<?php

namespace Kiboko\Plugin\Akeneo\DTO;

use PhpParser\Node;

final class GetAttributeOption
{
    public function __construct(
        public Node\Expr $attributeCode,
        public Node\Expr $code,
    ) {
    }
}
