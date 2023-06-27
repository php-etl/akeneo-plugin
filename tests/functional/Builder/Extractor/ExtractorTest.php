<?php declare(strict_types=1);

namespace functional\Builder\Extractor;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use functional\Kiboko\Plugin\Akeneo\Mock;
use Kiboko\Component\PHPUnitExtension\Assert\ExtractorBuilderAssertTrait;
use Kiboko\Plugin\Akeneo\Builder\Extractor;
use Kiboko\Plugin\Akeneo\Capacity;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExtractorTest extends BuilderTestCase
{
    use ExtractorBuilderAssertTrait;

    public function testAllProducts(): void
    {
        $httpClient = new Mock\HttpClientBuilder(new Mock\ResponseFactoryBuilder());

        $httpClient
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/oauth/v1/token', methods: ['POST']),
                new Mock\ResponseBuilder(__DIR__ . '/../token.php')
            )
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/products', methods: ['GET']),
                new Mock\ResponseBuilder(__DIR__ . '/get-all-products.php')
            )
        ;

        $client = new Mock\ApiClientMockBuilder();
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword()
        ;

        $capacity = (new Capacity\Extractor\All(new ExpressionLanguage()))->getBuilder([
            'type' => 'product',
        ]);

        $builder = new Extractor($capacity);
        $builder->withClient($client->getNode());

        $this->assertBuildsExtractorExtractsExactly(
            [
                [
                    '_links' => [
                        'self' => [
                            'href' => 'http://test.com/api/rest/v1/products/123qwerty'
                        ]
                    ],
                    'identifier' => '123qwerty',
                    'enabled' => true,
                    'family' => 'all_in_the_family',
                    'categories' => ['pizza'],
                    'groups' => [],
                    'parent' => '987qwerty',
                    'values' => [
                        'color' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => "#fff"
                            ]
                        ],
                        'brand' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => ["8"]
                            ]
                        ],
                        'weight' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => "0.5300"
                            ]
                        ]
                    ],
                    'created' => '2021-06-18T03:30:11+00:00',
                    'updated' => '2022-05-16T08:37:11+00:00',
                    'associations' => [
                        'UPSELL' => [
                            'products' => [],
                            'product_models' => [],
                            'groups' => []
                        ]
                    ],
                    'quantified_associations' => [],
                    'metadata' => [
                        'workflow_status' => 'working_copy'
                    ]
                ],
                [
                    '_links' => [
                        'self' => [
                            'href' => 'http://test.com/api/rest/v1/products/123uiop'
                        ]
                    ],
                    'identifier' => '123uiop',
                    'enabled' => true,
                    'family' => 'family_feud',
                    'categories' => ['pizza'],
                    'groups' => [],
                    'parent' => '0987azerty',
                    'values' => [
                        'color' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => "#f00"
                            ]
                        ],
                        'brand' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => ["3"]
                            ]
                        ],
                        'weight' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => "0.1000"
                            ]
                        ]
                    ],
                    'created' => '2021-06-18T03:30:11+00:00',
                    'updated' => '2022-05-16T08:37:11+00:00',
                    'associations' => [
                        'UPSELL' => [
                            'products' => [],
                            'product_models' => [],
                            'groups' => []
                        ]
                    ],
                    'quantified_associations' => [],
                    'metadata' => [
                        'workflow_status' => 'working_copy'
                    ]
                ]
            ],
            $builder,
        );
    }

    public function testGetProduct(): void
    {
        $httpClient = new Mock\HttpClientBuilder(new Mock\ResponseFactoryBuilder());

        $httpClient
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/oauth/v1/token', methods: ['POST']),
                new Mock\ResponseBuilder(__DIR__ . '/../token.php')
            )
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/rest/v1/products/123qwerty', methods: ['GET']),
                new Mock\ResponseBuilder(__DIR__ . '/get-product.php')
            )
        ;

        $client = new Mock\ApiClientMockBuilder();
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword()
        ;

        $capacity = (new Capacity\Extractor\Get(new ExpressionLanguage()))->getBuilder([
            'type' => 'product',
            'identifier' => '123qwerty'
        ]);

        $builder = new Extractor($capacity);
        $builder->withClient($client->getNode());

        $this->assertBuildsExtractorExtractsExactly(
            [
                'identifier' => '123qwerty',
                'enabled' => true,
                'family' => 'all_in_the_family',
                'categories' => ['pizza'],
                'groups' => [],
                'parent' => '987qwerty',
                'values' => [
                    'color' => [
                        [
                            "locale" => null,
                            "scope" => null,
                            "data" => "#fff"
                        ]
                    ],
                    'brand' => [
                        [
                            "locale" => null,
                            "scope" => null,
                            "data" => ["8"]
                        ]
                    ],
                    'weight' => [
                        [
                            "locale" => null,
                            "scope" => null,
                            "data" => "0.5300"
                        ]
                    ]
                ],
                'created' => '2021-06-18T03:30:11+00:00',
                'updated' => '2022-05-16T08:37:11+00:00',
                'associations' => [
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ]
                ],
                'quantified_associations' => [],
                'metadata' => [
                    'workflow_status' => 'working_copy'
                ]
            ],
            $builder,
        );
    }

    public function testSearchProduct(): void
    {
        $httpClient = new Mock\HttpClientBuilder(new Mock\ResponseFactoryBuilder());

        $httpClient
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/oauth/v1/token', methods: ['POST']),
                new Mock\ResponseBuilder(__DIR__ . '/../token.php')
            )
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/rest/v1/products', methods: ['GET']),
                new Mock\ResponseBuilder(__DIR__ . '/get-all-products.php')
            )
        ;

        $client = new Mock\ApiClientMockBuilder();
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword()
        ;

        $capacity = (new Capacity\Extractor\All(new ExpressionLanguage()))->getBuilder([
            'type' => 'product',
            'search' => '123',
        ]);

        $builder = new Extractor($capacity);
        $builder->withClient($client->getNode());

        $this->assertBuildsExtractorExtractsExactly(
            [
                [
                    '_links' => [
                        'self' => [
                            'href' => 'http://test.com/api/rest/v1/products/123qwerty'
                        ]
                    ],
                    'identifier' => '123qwerty',
                    'enabled' => true,
                    'family' => 'all_in_the_family',
                    'categories' => ['pizza'],
                    'groups' => [],
                    'parent' => '987qwerty',
                    'values' => [
                        'color' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => "#fff"
                            ]
                        ],
                        'brand' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => ["8"]
                            ]
                        ],
                        'weight' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => "0.5300"
                            ]
                        ]
                    ],
                    'created' => '2021-06-18T03:30:11+00:00',
                    'updated' => '2022-05-16T08:37:11+00:00',
                    'associations' => [
                        'UPSELL' => [
                            'products' => [],
                            'product_models' => [],
                            'groups' => []
                        ]
                    ],
                    'quantified_associations' => [],
                    'metadata' => [
                        'workflow_status' => 'working_copy'
                    ]
                ],
                [
                    '_links' => [
                        'self' => [
                            'href' => 'http://test.com/api/rest/v1/products/123uiop'
                        ]
                    ],
                    'identifier' => '123uiop',
                    'enabled' => true,
                    'family' => 'family_feud',
                    'categories' => ['pizza'],
                    'groups' => [],
                    'parent' => '0987azerty',
                    'values' => [
                        'color' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => "#f00"
                            ]
                        ],
                        'brand' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => ["3"]
                            ]
                        ],
                        'weight' => [
                            [
                                "locale" => null,
                                "scope" => null,
                                "data" => "0.1000"
                            ]
                        ]
                    ],
                    'created' => '2021-06-18T03:30:11+00:00',
                    'updated' => '2022-05-16T08:37:11+00:00',
                    'associations' => [
                        'UPSELL' => [
                            'products' => [],
                            'product_models' => [],
                            'groups' => []
                        ]
                    ],
                    'quantified_associations' => [],
                    'metadata' => [
                        'workflow_status' => 'working_copy'
                    ]
                ]
            ],
            $builder,
        );
    }
}
