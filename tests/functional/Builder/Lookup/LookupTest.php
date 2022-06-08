<?php declare(strict_types=1);

namespace functional\Builder\Lookup;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use functional\Kiboko\Plugin\Akeneo\Mock;
use Kiboko\Component\PHPUnitExtension\Assert\TransformerBuilderAssertTrait;
use Kiboko\Plugin\Akeneo\Builder\AlternativeLookup;
use Kiboko\Plugin\Akeneo\Builder\Lookup;
use Kiboko\Plugin\Akeneo\Capacity;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class LookupTest extends BuilderTestCase
{
    use TransformerBuilderAssertTrait;

    public function testLookupProduct()
    {
        $httpClient = new Mock\HttpClientBuilder(new Mock\ResponseFactoryBuilder());

        $httpClient
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/oauth/v1/token', methods: ['POST']),
                new Mock\ResponseBuilder(__DIR__ . '/../token.php')
            )
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/rest/v1/products/0987uiop', methods: ['GET']),
                new Mock\ResponseBuilder(__DIR__ . '/get-product.php')
            )
        ;

        $client = new Mock\ApiClientMockBuilder();
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword();

        $capacity = (new Capacity\Lookup\Get(new ExpressionLanguage()))->getBuilder([
            'type' => 'product',
            'method' => 'get',
            'identifier' => '0987uiop',
        ]);

        $builder = new Lookup(new AlternativeLookup($capacity));
        $builder->withClient($client->getNode());

        $this->assertBuildsTransformerTransformsLike(
            [
                [
                    "identifier" => "0987uiop"
                ]
            ],
            [
                [
                    "identifier" => "0987uiop"
                ]
            ],
            $builder,
        );
    }

    public function testConditionalLookupProduct()
    {
        $httpClient = new Mock\HttpClientBuilder(new Mock\ResponseFactoryBuilder());

        $httpClient
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/oauth/v1/token', methods: ['POST']),
                new Mock\ResponseBuilder(__DIR__ . '/../token.php')
            )
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/rest/v1/products/0987uiop', methods: ['GET']),
                new Mock\ResponseBuilder(__DIR__ . '/get-product.php')
            )
        ;

        $client = new Mock\ApiClientMockBuilder();
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword();

        $capacity = (new Capacity\Lookup\Get(new ExpressionLanguage()))->getBuilder([
            'type' => 'product',
            'method' => 'get',
            'identifier' => '0987uiop'
        ]);

        $builder = new Lookup(new AlternativeLookup($capacity));
        $builder->withClient($client->getNode());

        $this->assertBuildsTransformerTransformsLike(
            [
                [
                    "identifier" => "0987uiop"
                ]
            ],
            [
                [
                    "identifier" => "0987uiop"
                ]
            ],
            $builder,
        );
    }
}
