<?php declare(strict_types=1);

namespace functional\Capacity\Lookup;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Kiboko\Plugin\Akeneo\Capacity;

final class DownloadTest extends TestCase
{
    public static function wrongConfigs(): \Generator
    {
        yield [
            'config' => [
                'type' => 'asset',
                'method' => 'download',
            ],
            'expected_message' => 'The configuration option "file" should be defined.'
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrongConfigs')]
    public function testWrongConfigs(array $config, string $expected_message): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expected_message);

        (new Capacity\Lookup\Download(new ExpressionLanguage()))->getBuilder($config);
    }
}
