<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo\Factory\Loader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class LoaderTest extends TestCase
{
    public function testNormalizeEmptyConfiguration()
    {
        $this->expectException(
            InvalidConfigurationException::class,
        );

        $client = new Loader(new ExpressionLanguage());
        $client->normalize([]);
    }

    public function testValidateEmptyConfiguration()
    {
        $client = new Loader(new ExpressionLanguage());
        $this->assertFalse($client->validate([]));
    }

    public function testMissingType(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.');

        $client = new Loader(new ExpressionLanguage());
        $this->assertFalse($client->validate(['wrong']));
        $client->compile(['wrong']);
    }

    public function testTypeNotFound(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.');

        $client = new Loader(new ExpressionLanguage());
        $client->compile([
            'type' => 'wrong',
            'method' => 'upsert',
        ]);
    }

    public function testCapacityNotFound(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.');

        $client = new Loader(new ExpressionLanguage());
        $client->compile([
            'type' => 'product',
            'method' => 'wrong',
        ]);
    }
}
