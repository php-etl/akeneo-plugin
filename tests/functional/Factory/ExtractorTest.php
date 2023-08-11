<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo\Factory\Extractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExtractorTest extends TestCase
{
    public function testValidateConfiguration()
    {
        $client = new Extractor(new ExpressionLanguage());
        $this->assertTrue($client->validate([
            [
                'type' => 'product',
                'method' => 'all',
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
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrongConfigs')]
    public function testMissingCapacity(array $config): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.');

        $client = new Extractor(new ExpressionLanguage());
        $this->assertFalse($client->validate($config));
        $client->compile($config);
    }
}
