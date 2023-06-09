<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ServiceTest extends TestCase
{
    public static function validConfigs(): \Generator
    {
        /** Get */
        yield [
            'expected' => [
                'expression_language' => [],
                'extractor' => [
                    'type' => 'product',
                    'method' => 'get',
                    'identifier' => 'azerty123',
                    'search' => []
                ],
                'client' => [
                    'api_url' => '1234',
                    'client_id' => '1234',
                    'secret' => '1234',
                    'username' => '1234',
                    'password' => '1234'
                ],
                'enterprise' => false,
            ],
            'expected_class' => \Kiboko\Plugin\Akeneo\Factory\Repository\Extractor::class,
            'actual' => [
                [
                    'extractor' => [
                        'type' => 'product',
                        'method' => 'get',
                        'identifier' => 'azerty123'
                    ],
                    'client' => [
                        'api_url' => '1234',
                        'client_id' => '1234',
                        'secret' => '1234',
                        'username' => '1234',
                        'password' => '1234'
                    ]
                ]
            ]
        ];

        /** Upsert */
        yield [
            'expected' => [
                'expression_language' => [],
                'loader' => [
                    'type' => 'product',
                    'method' => 'upsert',
                    'code' => 'azerty123',
                ],
                'client' => [
                    'api_url' => '1234',
                    'client_id' => '1234',
                    'secret' => '1234',
                    'username' => '1234',
                    'password' => '1234'
                ],
                'enterprise' => false,
            ],
            'expected_class' => \Kiboko\Plugin\Akeneo\Factory\Repository\Loader::class,
            'actual' => [
                [
                    'expression_language' => [],
                    'loader' => [
                        'type' => 'product',
                        'method' => 'upsert',
                        'code' => 'azerty123',
                    ],
                    'client' => [
                        'api_url' => '1234',
                        'client_id' => '1234',
                        'secret' => '1234',
                        'username' => '1234',
                        'password' => '1234'
                    ]
                ]
            ]
        ];
    }

    public function testEmptyConfiguration(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not determine if the factory should build an extractor or a loader.');

        $service = new Akeneo\Service();
        $this->assertTrue($service->validate(['akeneo' => []]));
        $service->compile([
            'akeneo' => []
        ]);
    }

    public function testWrongConfiguration(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Invalid type for path "akeneo". Expected "array", but got "string"');

        $service = new Akeneo\Service();
        $this->assertFalse($service->validate(['akeneo' => 'wrong']));
        $service->normalize(['akeneo' => 'wrong']);
    }

    public function testMissingAuthentication(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is missing an authentication method, you should either define "username" or "token" options.');

        $service = new Akeneo\Service();
        $service->compile([
            'loader' => [
                'type' => 'product',
                'method' => 'upsert',
                'code' => 'azerty123',
            ],
            'client' => [
                'api_url' => '1234',
                'client_id' => '1234',
                'secret' => '1234',
                'username' => '1234',
            ]
        ]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validConfigs')]
    public function testWithConfigurationAndProcessor(array $expected, string $expectedClass, array $actual): void
    {
        $service = new Akeneo\Service(new ExpressionLanguage());

        $this->assertEquals(
            $expected,
            $service->normalize($actual)
        );

        $this->assertTrue($service->validate($actual));
    }
}
