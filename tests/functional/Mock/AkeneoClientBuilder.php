<?php

declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Mock;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Http\Client\HttpClient as Client;

class AkeneoClientBuilder extends AkeneoPimClientBuilder
{
    /** @var Client */
    protected $httpClient;

    public function __construct(string $baseUri, $httpClient)
    {
        parent::__construct($baseUri);
        $this->httpClient = $httpClient;
    }
}
