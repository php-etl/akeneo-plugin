<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\DTO;

use PhpParser\Node;

final class GetAssetManager
{
    public function __construct(
        public Node\Expr $assetFamilyCode,
        public Node\Expr $assetCode,
    ) {
    }
}
