<?php declare(strict_types=1);

namespace functional\Capacity\Lookup;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Kiboko\Plugin\Akeneo\Capacity;

final class ListPerPageTest extends TestCase
{
    public function wrongConfigs(): \Generator
    {
        yield [
            'config' => [
                'type' => 'product',
                'search' => [
                    [
                        'operator' => 'EMPTY',
                        'value' => 'bar'
                    ]
                ]
            ],
            'expected_message' => 'You should not provide a value for the EMPTY operator'
        ];

        yield [
            'config' => [
                'type' => 'product',
                'search' => [
                    [
                        'operator' => '=',
                        'field' => 'foo'
                    ]
                ]
            ],
            'expected_message' => 'You should provide a value for the = operator'
        ];
    }

    public function goodConfigs(): \Generator
    {
        yield [
            'config' => [
                'type' => 'product',
                'search' => [
                    [
                        'operator' => 'EMPTY',
                        'field' => 'foo',
                    ]
                ]
            ]
        ];
    }

    /** @dataProvider wrongConfigs */
    public function testWrongConfigs(array $config, string $expected_message): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expected_message);

        (new Capacity\Lookup\ListPerPage(new ExpressionLanguage()))->getBuilder($config);
    }

    /** @dataProvider goodConfigs */
    public function testGoodConfigs(array $config): void
    {
        $this->assertInstanceOf(
            'PhpParser\Builder',
            (new Capacity\Lookup\ListPerPage(new ExpressionLanguage()))->getBuilder($config)
        );
    }
}
