<?php

namespace Kiboko\Plugin\Akeneo\Handler;

interface EndpointHandlerFactoryInterface
{
    public function create(string $endpointType, array $config): EndpointArgumentHandlerInterface;
}
