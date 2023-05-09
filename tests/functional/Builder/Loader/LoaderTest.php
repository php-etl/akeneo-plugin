<?php declare(strict_types=1);

namespace functional\Builder\Loader;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use functional\Kiboko\Plugin\Akeneo\Mock;
use Kiboko\Component\PHPUnitExtension\Assert\LoaderBuilderAssertTrait;
use Kiboko\Plugin\Akeneo\Builder\Loader;
use Kiboko\Plugin\Akeneo\Capacity;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class LoaderTest extends BuilderTestCase
{
    use LoaderBuilderAssertTrait;

    public function testUpsertProduct(): void
    {
        $httpClient = new Mock\HttpClientBuilder(new Mock\ResponseFactoryBuilder());

        $httpClient
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/oauth/v1/token', methods: ['POST']),
                new Mock\ResponseBuilder(__DIR__ . '/../token.php')
            )
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('api/rest/v1/products/[^/]+', methods: ['PATCH']),
                new Mock\ResponseBuilder(__DIR__ . '/post-product.php')
            )
        ;

        $client = new Mock\ApiClientMockBuilder();
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword();

        $capacity = (new Capacity\Loader\Upsert(new ExpressionLanguage()))->getBuilder([
            'type' => 'product',
            'method' => 'all',
            'code' => 'line[code]'
        ]);

        $builder = new Loader($capacity);
        $builder->withClient($client->getNode());

        $this->assertBuildsLoaderLoadsExactly(
            [
                [
                    "code" => "0987uiop"
                ]
            ],
            [
                [
                    "code" => "0987uiop"
                ]
            ],
            $builder,
        );
    }

    public function testUpsertProductsList(): void
    {
        $httpClient = new Mock\HttpClientBuilder(new Mock\ResponseFactoryBuilder());

        $httpClient
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/oauth/v1/token', methods: ['POST']),
                new Mock\ResponseBuilder(__DIR__ . '/../token.php')
            )
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('api/rest/v1/products', methods: ['PATCH']),
                new Mock\ResponseBuilder(__DIR__ . '/post-product.php')
            )
        ;

        $client = new Mock\ApiClientMockBuilder();
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword();

        $capacity = (new Capacity\Loader\UpsertList())->getBuilder([
            'type' => 'product',
            'code' => 'line'
        ]);

        $builder = new Loader($capacity);
        $builder->withClient($client->getNode());

        $this->assertBuildsLoaderLoadsExactly(
            expected: [
                [
                    [
                        "code" => "0987uiop"
                    ],
                    [
                        "code" => "0987uiop"
                    ]
                ]
            ],
            input: [
                [
                    [
                        "code" => "0987uiop"
                    ],
                    [
                        "code" => "0987uiop"
                    ]
                ]
            ],
            builder: $builder,
        );
    }
}
