<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Handler;

interface EndpointHandlerFactoryInterface
{
    public function create(string $endpointType, array $config): EndpointArgumentHandlerInterface;
}
