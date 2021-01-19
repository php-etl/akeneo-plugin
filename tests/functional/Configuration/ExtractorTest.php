<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Configuration;

use Kiboko\Plugin\Akeneo\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config;

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
    }

    /** @dataProvider validDataProvider */
    public function testValidConfig(array $config, array $expected)
    {
        $client = new Configuration\Extractor();

        $this->assertSame($expected, $this->processor->processConfiguration($client, [$config]));
    }

    public function testInvalidConfig()
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
