<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Handler;

use Kiboko\Plugin\Akeneo\DTO\GetAttributeOption;
use PhpParser\Node;

final readonly class GetAttributeOptionHandler implements EndpointArgumentHandlerInterface
{
    public function __construct(private GetAttributeOption $attributeOption)
    {
    }

    public function compileEndpointArguments(): array
    {
        return [
            new Node\Arg(
                value: $this->attributeOption->attributeCode,
                name: new Node\Identifier('attributeCode'),
            ),
            new Node\Arg(
                value: $this->attributeOption->code,
                name: new Node\Identifier('code'),
            ),
        ];
    }
}
