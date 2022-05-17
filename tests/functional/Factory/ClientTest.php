<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo\Factory\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ClientTest extends TestCase
{
    public function testNormalizeEmptyConfiguration()
    {
        $this->expectException(
            InvalidConfigurationException::class,
        );

        $client = new Client(new ExpressionLanguage());
        $client->normalize([]);
    }

    public function testValidateEmptyConfiguration()
    {
        $client = new Client(new ExpressionLanguage());
        $this->assertFalse($client->validate([]));
    }
}
