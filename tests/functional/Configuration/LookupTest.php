<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Configuration;

use Kiboko\Plugin\Akeneo\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config;
use Symfony\Component\ExpressionLanguage\Expression;

final class LookupTest extends TestCase
{
    private ?Config\Definition\Processor $processor = null;

    protected function setUp(): void
    {
        $this->processor = new Config\Definition\Processor();
    }

    public static function validConfigs(): iterable
    {
        yield [
            'config' => [
                'conditional' => [
                    0 => [
                        'condition' => 'input["type"] === "pim_attribute_simpleselect"',
                        'type' => 'attributeOption',
                        'code' => '@=input["code"]',
                        'method' => 'all',
                        'merge' => [
                            'map' => [
                                0 => [
                                    'field' => '[options]',
                                    'expression' => 'join(",", lookup)'
                                ],
                                1 => [
                                    'field' => '[options]',
                                    'expression' => 'join(",", lookup)'
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'conditional' => [
                    0 => [
                        'condition' => 'input["type"] === "pim_attribute_simpleselect"',
                        'type' => 'attributeOption',
                        'code' => new Expression('input["code"]'),
                        'method' => 'all',
                        'search' => [],
                        'merge' => [
                            'map' => [
                                0 => [
                                    'field' => '[options]',
                                    'expression' => 'join(",", lookup)'
                                ],
                                1 => [
                                    'field' => '[options]',
                                    'expression' => 'join(",", lookup)'
                                ],
                            ],
                        ],
                    ],
                ],
                'search' => [],
            ],
        ];

        yield [
            'config' => [
                'type' => 'attributeOption',
                'code' => '@=input["code"]',
                'method' => 'all',
                'merge' => [
                    'map' => [
                        0 => [
                            'field' => '[options]',
                            'expression' => 'join(",", lookup)'
                        ],
                        1 => [
                            'field' => '[options]',
                            'expression' => 'join(",", lookup)'
                        ],
                    ],
                ],
            ],
            'expected' => [
                'type' => 'attributeOption',
                'code' => new Expression('input["code"]'),
                'method' => 'all',
                'search' => [],
                'merge' => [
                    'map' => [
                        0 => [
                            'field' => '[options]',
                            'expression' => 'join(",", lookup)'
                        ],
                        1 => [
                            'field' => '[options]',
                            'expression' => 'join(",", lookup)'
                        ],
                    ],
                ],
            ],
        ];

        yield [
            'config' => [
                'type' => 'productMediaFile',
                'file' => '123',
                'method' => 'all',
            ],
            'expected' => [
                'type' => 'productMediaFile',
                'file' => '123',
                'method' => 'all',
                'search' => [],
            ],
        ];

        yield [
            'config' => [
                'type' => 'product',
                'method' => 'listPerPage',
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
                'method' => 'all',
                'merge' => [
                    'map' => [
                        [
                            'field' => 'foo',
                            'constant' => 'bar'
                        ]
                    ]
                ],
            ],
            'expected' => [
                'type' => 'product',
                'method' => 'all',
                'merge' => [
                    'map' => [
                        [
                            'field' => 'foo',
                            'constant' => 'bar'
                        ]
                    ]
                ],
                'search' => [],
            ]
        ];

        yield [
            'config' => [
                'conditional' => [
                    [
                        'file' => '123',
                        'type' => 'productMediaFile',
                        'method' => 'download',
                    ],
                ],
            ],
            'expected' => [
                'conditional' => [
                    [
                        'file' => '123',
                        'type' => 'productMediaFile',
                        'method' => 'download',
                        'search' => [],
                    ],
                ],
                'search' => [],
            ]
        ];
    }

    public static function wrongConfigs(): \Generator
    {
        yield [
            'config' => [
                'type' => 'product',
                'method' => 'download'
            ],
            'excepted_message' => 'Invalid configuration for path "lookup": The value should be one of [listPerPage, all, get], got "download"',
            'excepted_class' => \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class,
        ];

        yield [
            'config' => [
                'file' => '123',
                'type' => 'product',
                'method' => 'get',
            ],
            'excepted_message' => 'Invalid configuration for path "lookup": The file option should only be used with the "productMediaFile" and "assetMediaFile" endpoints.',
            'excepted_class' => \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class,
        ];

        yield [
            'config' => [
                'conditional' => [
                    [
                        'type' => 'product',
                        'method' => 'wrong',
                    ],
                ],
            ],
            'excepted_message' => 'Invalid configuration for path "lookup.conditional.0": the value should be one of [listPerPage, all, get], got "wrong"',
            'excepted_class' => \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class,
        ];

        yield [
            'config' => [
                'file' => '123',
                'type' => 'product',
                'method' => 'get',
            ],
            'excepted_message' => 'Invalid configuration for path "lookup": The file option should only be used with the "productMediaFile',
            'excepted_class' => \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class,
        ];

        yield [
            'config' => [
                'conditional' => [
                    [
                        'identifier' => '123',
                        'type' => 'product',
                        'method' => 'all',
                    ],
                ],
            ],
            'excepted_message' => 'Invalid configuration for path "lookup.conditional.0": The identifier option should only be used with the "get" method.',
            'excepted_class' => \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validConfigs')]
    public function testValidConfigs(array $config, array $expected)
    {
        $client = new Configuration\Lookup();

        $this->assertEquals($expected, $this->processor->processConfiguration($client, [$config]));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrongConfigs')]
    public function testWrongConfigs(array $config, string $expectedMessage, string $exceptedClass)
    {
        $client = new Configuration\Lookup();

        $this->expectExceptionMessage(
            $expectedMessage
        );

        $this->expectException(
            $exceptedClass
        );

        $this->processor->processConfiguration($client, [
            $config
        ]);
    }
}
