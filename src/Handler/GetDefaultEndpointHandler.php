<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Handler;

use Kiboko\Plugin\Akeneo\DTO\GetDefaultEndpoint;
use PhpParser\Node;

final readonly class GetDefaultEndpointHandler implements EndpointArgumentHandlerInterface
{
    public function __construct(private GetDefaultEndpoint $defaultEndpoint)
    {
    }

    public function compileEndpointArguments(): array
    {
        return [
            new Node\Arg(
                value: $this->defaultEndpoint->identifier,
                name: new Node\Identifier('identifier'),
            ),
        ];
    }
}
