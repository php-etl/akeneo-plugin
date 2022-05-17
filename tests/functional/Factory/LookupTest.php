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
}
