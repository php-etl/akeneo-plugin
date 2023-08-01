<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Handler;

use Kiboko\Plugin\Akeneo\DTO\GetAssetManager;
use PhpParser\Node;

final readonly class GetAssetManagerHandler implements EndpointArgumentHandlerInterface
{
    public function __construct(private GetAssetManager $assetManager)
    {
    }

    public function compileEndpointArguments(): array
    {
        return [
            new Node\Arg(
                value: $this->assetManager->assetFamilyCode,
                name: new Node\Identifier('assetFamilyCode'),
            ),
            new Node\Arg(
                value: $this->assetManager->assetCode,
                name: new Node\Identifier('assetCode'),
            ),
        ];
    }
}
