<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo\Factory\Lookup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class LookupTest extends TestCase
{
    public function testValidateConfiguration()
    {
        $client = new Lookup(new ExpressionLanguage());
        $this->assertTrue($client->validate([
            [
                'type' => 'product',
                'method' => 'all',
            ]
        ]));

        $client->compile([
            'type' => 'product',
            'method' => 'all',
        ]);
    }

    public static function wrongConfigs(): \Generator
    {
        yield [
            'config' => [
                'type' => 'product',
                'condition' => [],
            ]
        ];
        yield [
            'config' => [
                'wrong',
            ]
        ];
        yield [
            'config' => [
                'type' => 'wrong',
                'method' => 'all',
            ]
        ];
        yield [
            'config' => [
                'type' => 'product',
                'method' => 'wrong',
            ]
        ];
        yield [
            'config' => [
                'type' => 'product',
            ]
        ];
        yield [
            'config' => [
                'type' => 'assetMediaFile',
                'file' => '123'
            ]
        ];
        yield [
            'config' => [
                'type' => 'assetMediaFile',
                'method' => 'wrong',
                'file' => '123'
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrongConfigs')]
    public function testMissingCapacity(array $config): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.');

        $client = new Lookup(new ExpressionLanguage());
        $this->assertFalse($client->validate($config));
        $client->compile($config);
    }
}
