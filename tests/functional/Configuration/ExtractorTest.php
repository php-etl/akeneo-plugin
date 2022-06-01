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

    public function validDataProvider(): iterable
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
    }

    /** @dataProvider validDataProvider */
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

    public function testUnexpectedCodeOptionConfig()
    {
        $client = new Configuration\Extractor();

        $this->expectException(
            Config\Definition\Exception\InvalidConfigurationException::class,
        );
        $this->expectExceptionMessage(
            'The code option should only be used with the "attributeOption" and "assetManager" endpoints.',
        );

        $this->processor->processConfiguration($client, [
            [
                'method' => 'get',
                'type' => 'product',
                'code' => '123',
            ]
        ]);
    }

    public function testUnexpectedProductMediaFileFieldConfig()
    {
        $client = new Configuration\Extractor();

        $this->expectException(
            Config\Definition\Exception\InvalidConfigurationException::class,
        );
        $this->expectExceptionMessage(
            'Invalid configuration for path "extractor": The file option should only be used with the "productMediaFile" endpoint.',
        );

        $this->processor->processConfiguration($client, [
            [
                'type' => 'product',
                'method' => 'all',
                'file' => 'foo'
            ]
        ]);
    }

    public function testUnexpectedIdentifierFieldConfig()
    {
        $client = new Configuration\Extractor();

        $this->expectException(
            Config\Definition\Exception\InvalidConfigurationException::class,
        );
        $this->expectExceptionMessage(
            'Invalid configuration for path "extractor": The identifier option should only be used with the "get" method.',
        );

        $this->processor->processConfiguration($client, [
            [
                'type' => 'product',
                'method' => 'all',
                'identifier' => 'foo'
            ]
        ]);
    }
}
