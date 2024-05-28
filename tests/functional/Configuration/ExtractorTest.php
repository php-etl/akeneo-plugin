<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Configuration;

use Kiboko\Plugin\Akeneo\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config;
use Symfony\Component\ExpressionLanguage\Expression;

final class ExtractorTest extends TestCase
{
    private ?Config\Definition\Processor $processor = null;

    protected function setUp(): void
    {
        $this->processor = new Config\Definition\Processor();
    }

    public static function validDataProvider(): iterable
    {
        yield [
            'config' => [
                'type' => 'product',
                'method' => 'all',
                'search' => [],
            ],
            'expected' => [
                'type' => 'product',
                'method' => 'all',
                'search' => [],
            ],
        ];
        yield [
            'config' => [
                'type' => 'product',
                'method' => 'listPerPage',
                'search' => [],
            ],
            'expected' => [
                'type' => 'product',
                'method' => 'listPerPage',
                'search' => [],
            ],
        ];
        yield [
            'config' => [
                'type' => 'product',
                'method' => 'get',
                'search' => [],
            ],
            'expected' => [
                'type' => 'product',
                'method' => 'get',
                'search' => [],
            ],
        ];
        yield [
            'config' => [
                'type' => 'productMediaFile',
                'method' => 'all',
                'file' => '@="foo"',
                'search' => [],
            ],
            'expected' => [
                'type' => 'productMediaFile',
                'method' => 'all',
                'file' => new Expression('"foo"'),
                'search' => [],
            ],
        ];
        yield [
            'config' => [
                'method' => 'get',
                'type' => 'attributeOption',
                'code' => '123',
                'search' => [],
            ],
            'expected' => [
                'method' => 'get',
                'type' => 'attributeOption',
                'code' => '123',
                'search' => [],
            ],
        ];
        yield [
            'config' => [
                'method' => 'all',
                'type' => 'category',
                'search' => [],
                'with_enriched_attributes' => true,
            ],
            'expected' => [
                'method' => 'all',
                'type' => 'category',
                'search' => [],
                'with_enriched_attributes' => true,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validDataProvider')]
    public function testValidConfig(array $config, array $expected)
    {
        $client = new Configuration\Extractor();

        $this->assertEquals($expected, $this->processor->processConfiguration($client, [$config]));
    }

    public function testInvalidMethodTypeConfig()
    {
        $client = new Configuration\Extractor();

        $this->expectException(
            Config\Definition\Exception\InvalidConfigurationException::class,
        );
        $this->expectExceptionMessage(
            'Invalid configuration for path "extractor": the value should be one of [listPerPage, all, get], got "invalidValue"',
        );

        $this->processor->processConfiguration($client, [
            [
                'type' => 'product',
                'method' => 'invalidValue'
            ]
        ]);
    }
}
