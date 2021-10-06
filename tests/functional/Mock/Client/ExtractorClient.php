<?php

declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Mock\Client;

use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ExtractorClient extends Client
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        if (str_contains($uri,'api/oauth/v1/token')) {
            return new Response(200, [], json_encode([
                'access_token' => 'faketoken',
                'refresh_token' => 'refresh_token',
            ], JSON_THROW_ON_ERROR));
        }

        if (str_contains($uri,'api/rest/v1/families/test/variants')) {
            return new Response(200, [], $this->getResponseByFileName('single_clothing_family_variants'));
        }

        throw new \RuntimeException(sprintf('Not able to handle this request %s', $uri));
    }

    private function getResponseByFileName(string $filename): string
    {
        $path = __DIR__ . '/Response/' . $filename . '.json';
        return file_get_contents($path);
    }
}
