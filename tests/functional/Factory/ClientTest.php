<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Plugin\Akeneo\Factory\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ClientTest extends TestCase
{
    public function testMissingCapacity()
    {
        $this->expectException(InvalidConfigurationException::class);

        $client = new Client(new ExpressionLanguage());
        $this->assertFalse($client->validate([]));
        $client->normalize([]);
    }

    public function testValidateConfiguration()
    {
        $client = new Client(new ExpressionLanguage());
        $this->assertTrue($client->validate([
            'client' => [
                'api_url' => '123',
                'client_id' => '123',
                'secret' => '123',
                'username' => '123',
                'password' => '123',
            ]
        ]));

        $client->compile([
            'api_url' => '123',
            'client_id' => '123',
            'secret' => '123',
            'username' => '123',
            'password' => '123',
        ]);
    }

    public function testWithHttpClient()
    {
        $client = new Client(new ExpressionLanguage());
        $this->assertTrue($client->validate([
            'client' => [
                'api_url' => '123',
                'client_id' => '123',
                'secret' => '123',
                'username' => '123',
                'password' => '123',
                'context' => [
                    'http_client' => 'truc'
                ]
            ]
        ]));

        $client->compile([
            'api_url' => '123',
            'client_id' => '123',
            'secret' => '123',
            'username' => '123',
            'password' => '123',
            'context' => [
                'http_client' => 'foo'
            ]
        ]);
    }
}
