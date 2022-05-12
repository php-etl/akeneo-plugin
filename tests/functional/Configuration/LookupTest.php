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

    public function validDataProvider(): iterable
    {
        yield [
            'config' => [
                'conditional' => [
                    0 => [
                        'condition' => 'input["type"] === "pim_attribute_simpleselect"',
                        'type' => 'attributeOption',
                        'code' => '@=input["code"]',
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
    }

    /** @dataProvider validDataProvider */
    public function testValidConfig(array $config, array $expected)
    {
        $client = new Configuration\Lookup();

        $this->assertEquals($expected, $this->processor->processConfiguration($client, [$config]));
    }

    public function testInvalidConfig()
    {
        $client = new Configuration\Lookup();

        $this->expectException(
            Config\Definition\Exception\InvalidConfigurationException::class,
        );

        $this->expectExceptionMessage(
            'Invalid configuration for path "lookup.type": The value should be one of [product, category, attribute, attributeOption, attributeGroup, family, productMediaFile, locale, channel, currency, measureFamily, associationType, familyVariant, productModel, publishedProduct, productModelDraft, productDraft, asset, assetCategory, assetTag, referenceEntityRecord, referenceEntityAttribute, referenceEntityAttributeOption, referenceEntity, assetManager, assetMediaFiles], got "invalidType"',
        );

        $this->processor->processConfiguration($client, [
            [
                'type' => 'invalidType',
                'method' => 'all'
            ]
        ]);
    }
}
