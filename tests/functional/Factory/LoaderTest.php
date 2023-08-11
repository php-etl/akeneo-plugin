<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo\Factory\Loader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class LoaderTest extends TestCase
{
    public function testValidateConfiguration()
    {
        $client = new Loader(new ExpressionLanguage());
        $this->assertTrue($client->validate([
            [
                'type' => 'product',
                'method' => 'upsert',
                'code' => '123',
            ]
        ]));
    }

    public static function wrongConfigs(): \Generator
    {
        yield [
            'config' => [
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
                'method' => 'upsert',
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
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrongConfigs')]
    public function testMissingCapacity(array $config): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.');

        $client = new Loader(new ExpressionLanguage());
        $this->assertFalse($client->validate($config));
        $client->compile($config);
    }
}
