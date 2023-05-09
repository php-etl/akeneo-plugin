<?php declare(strict_types=1);

namespace functional\Configuration;

use Kiboko\Plugin\Akeneo\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config;

final class LoaderTest extends TestCase
{
    private ?Config\Definition\Processor $processor = null;

    protected function setUp(): void
    {
        $this->processor = new Config\Definition\Processor();
    }

    public function testInvalidMethodTypeConfig()
    {
        $client = new Configuration\Loader();

        $this->expectException(
            Config\Definition\Exception\InvalidConfigurationException::class,
        );
        $this->expectExceptionMessage(
            'Invalid configuration for path "loader": the value should be one of [create, upsert, upsertList, delete], got "invalidValue"',
        );

        $this->processor->processConfiguration($client, [
            [
                'type' => 'product',
                'method' => 'invalidValue'
            ]
        ]);
    }

    public function testUpsertMissingCode()
    {
        $client = new Configuration\Loader();

        $this->expectException(
            Config\Definition\Exception\InvalidConfigurationException::class,
        );
        $this->expectExceptionMessage(
            'Your configuration should contain the "code" field if the "upsert" method is present.',
        );

        $this->processor->processConfiguration($client, [
            [
                'type' => 'product',
                'method' => 'upsert'
            ]
        ]);
    }
}
