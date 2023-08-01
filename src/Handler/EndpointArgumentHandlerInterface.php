<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Handler;

interface EndpointArgumentHandlerInterface
{
    public function compileEndpointArguments(): array;
}
