<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder;

use functional\Kiboko\Plugin\Akeneo\Mock;
use functional\Kiboko\Plugin\Akeneo\PipelineRunner;
use Kiboko\Component\PHPUnitExtension\Assert\ExtractorBuilderAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use Kiboko\Plugin\Akeneo\Builder\Capacity;
use Kiboko\Plugin\Akeneo\Builder\Extractor;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class ExtractorTest extends TestCase
{
    use ExtractorBuilderAssertTrait;

    private ?vfsStreamDirectory $fs = null;

    protected function setUp(): void
    {
        $this->fs = vfsStream::setup();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
        vfsStreamWrapper::unregister();
    }

    protected function getBuilderCompilePath(): string
    {
        return $this->fs->url();
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }

    public function testAllProducts(): void
    {
        $httpClient = new Mock\HttpClientBuilder(new Mock\ResponseFactoryBuilder());

        $httpClient
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/api/oauth/v1/token', methods: ['POST']),
                new Mock\ResponseBuilder(__DIR__ . '/fake-token.php')
            )
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/products', methods: ['GET']),
                new Mock\ResponseBuilder(__DIR__ . '/products.all.php')
            );

        $client = new Mock\ApiClientMockBuilder(withEnterpriseSupport: false);
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword();

        $capacity = new Capacity\Extractor\All();
        $capacity->withEndpoint(new Node\Identifier('getProductApi'));
        $capacity->withType('product');

        $builder = new Extractor($capacity);
        $builder->withClient($client->getNode());

        $this->assertBuildsExtractorExtractsLike(
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
                    'parent' => '321azerty',
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
                            'href' => 'http://test.com/api/rest/v1/products/0987uiop'
                        ]
                    ],
                    'identifier' => '0987uiop',
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
