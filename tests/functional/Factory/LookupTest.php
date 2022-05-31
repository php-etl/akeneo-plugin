<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo\Factory\Lookup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class LookupTest extends TestCase
{
    public function testNormalizeEmptyConfiguration()
    {
        $this->expectException(
            InvalidConfigurationException::class,
        );

        $client = new Lookup(new ExpressionLanguage());
        $client->normalize([
            'condition' => [],
            'type' => 'product',
        ]);
    }

    public function testValidateEmptyConfiguration()
    {
        $client = new Lookup(new ExpressionLanguage());
        $this->assertFalse($client->validate([
            'condition' => [],
            'type' => 'product',
        ]));
    }

    public function testMissingType(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.');

        $client = new Lookup(new ExpressionLanguage());
        $this->assertFalse($client->validate(['wrong']));
        $client->compile(['wrong']);
    }

    public function testTypeNotFound(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.');

        $client = new Lookup(new ExpressionLanguage());
        $client->compile([
            'type' => 'wrong',
            'method' => 'all',
        ]);
    }

    public function testCapacityNotFound(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.');

        $client = new Lookup(new ExpressionLanguage());
        $client->compile([
            'type' => 'product',
            'method' => 'wrong',
        ]);
    }
}
