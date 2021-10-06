<?php

declare(strict_types=1);

namespace functional\Builder;

use functional\Kiboko\Plugin\Akeneo\AkeneoClientTestCase;
use functional\Kiboko\Plugin\Akeneo\Mock\Client\ExtractorClient;
use functional\Kiboko\Plugin\Akeneo\PipelineRunner;
use Kiboko\Component\PHPUnitExtension\Assert\ExtractorBuilderAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use Kiboko\Plugin\Akeneo\Builder;
use Kiboko\Plugin\Akeneo\Capacity;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Vfs\FileSystem;

class ExtractorTest extends AkeneoClientTestCase
{
    use ExtractorBuilderAssertTrait;

    private ?FileSystem $fs = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;
    }

    public function testExtractWithLookupAll(): void
    {
        $allCapacity = new Capacity\Lookup\All(new ExpressionLanguage());
        $allCapacityBuilder = $allCapacity->getBuilder([
            'type' => 'familyVariant',
            'method' => 'all',
            'code' => 'test'
        ]);

        $clientBuilder = $this->getAkeneoClientBuilder(ExtractorClient::class);

        $extractorBuilder = (new Builder\Extractor())
            ->withCapacity($allCapacityBuilder)
            ->withClient($clientBuilder);

        $this->assertBuildsExtractorExtractsLike(
            [
                [
                    '_links' => ['self' => ['href' => 'http://localhost:8080/api/rest/v1/families/clothing/variants/clothing_color',],],
                    'code' => 'clothing_color',
                    'labels' => [
                        'de_DE' => 'Kleidung nach Farbe',
                        'en_US' => 'Clothing by color',
                        'fr_FR' => 'Vêtements par couleur',
                    ],
                    'variant_attribute_sets' => [
                        0 => [
                            'level' => 1,
                            'axes' => [0 => 'color',],
                            'attributes' => [
                                0 => 'sku',
                                1 => 'variation_name',
                                2 => 'variation_image',
                                3 => 'composition',
                                4 => 'color',
                                5 => 'ean',
                            ],
                        ],
                    ],
                ]
            ],
            $extractorBuilder,
        );
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}
