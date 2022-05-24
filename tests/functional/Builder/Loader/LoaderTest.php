<?php declare(strict_types=1);

namespace functional\Builder\Loader;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use functional\Kiboko\Plugin\Akeneo\Mock;
use Kiboko\Component\PHPUnitExtension\Assert\LoaderBuilderAssertTrait;
use Kiboko\Plugin\Akeneo\Builder\Capacity;
use Kiboko\Plugin\Akeneo\Builder\Loader;
use PhpParser\Node;

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

        $client = new Mock\ApiClientMockBuilder(withEnterpriseSupport: false);
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword();

        $capacity = new Capacity\Loader\Upsert();
        $capacity
            ->withEndpoint(new Node\Identifier('getProductApi'))
            ->withCode(new Node\Expr\ArrayDimFetch(new Node\Expr\Variable('line'), new Node\Scalar\String_('code')))
            ->withData(new Node\Expr\Variable('line'));

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

        $client = new Mock\ApiClientMockBuilder(withEnterpriseSupport: false);
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword();

        $capacity = new Capacity\Loader\UpsertList();
        $capacity
            ->withEndpoint(new Node\Identifier('getProductApi'))
            ->withData(new Node\Expr\Variable('line'));

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
