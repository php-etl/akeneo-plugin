<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Configuration;

use Kiboko\Plugin\Akeneo\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config;

final class LookupTest extends TestCase
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
                'condition' => 'input["type"] === "pim_attribute_simpleselect"',
                'lookup' => [
                    'type' => 'attributeOption',
                    'field' => '@=input["code"]',
                    'method' => 'all',
                    'search' => [],
                ],
                'merge' => [
                    0 => [
                        'field' => '[options]',
                        'expression' => 'join(",", lookup)'
                    ],
                    1 => [
                        'field' => '[options]',
                        'expression' => 'join(",", lookup)'
                    ]
                ]
            ],
            'expected' => [
                'condition' => 'input["type"] === "pim_attribute_simpleselect"',
                'lookup' => [
                    'type' => 'attributeOption',
                    'field' => '@=input["code"]',
                    'method' => 'all',
                    'search' => [],
                ],
                'merge' => [
                    0 => [
                        'field' => '[options]',
                        'expression' => 'join(",", lookup)'
                    ],
                    1 => [
                        'field' => '[options]',
                        'expression' => 'join(",", lookup)'
                    ]
                ]
            ],
        ];
    }

    /** @dataProvider validDataProvider */
    public function testValidConfig(array $config, array $expected)
    {
        $client = new Configuration\Lookup();

        $this->assertSame($expected, $this->processor->processConfiguration($client, [$config]));
    }

    public function testInvalidConfig()
    {
        $client = new Configuration\Lookup();

        $this->expectException(
            Config\Definition\Exception\InvalidConfigurationException::class,
        );
        $this->expectExceptionMessage(
            'Invalid configuration for path "conditional.lookup": the value should be one of [listPerPage, all, get], got "invalidValue"',
        );

        $this->processor->processConfiguration($client, [
            [
                'lookup' => [
                    'type' => 'product',
                    'method' => 'invalidValue'
                ]
            ]
        ]);
    }
}
