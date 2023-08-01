<?php

namespace Kiboko\Plugin\Akeneo\Handler;

interface EndpointArgumentHandlerInterface
{
    public function compileEndpointArguments(): array;
}
