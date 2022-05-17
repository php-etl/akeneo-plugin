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
}
