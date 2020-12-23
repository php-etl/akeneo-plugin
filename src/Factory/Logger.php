<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Factory;

use Kiboko\Component\ETL\Flow\Akeneo;
use Kiboko\Contract\ETL\Configurator;
use PhpParser\Node;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception as Symfony;
use Symfony\Component\Config\Definition\Processor;

final class Logger implements Configurator\FactoryInterface
{
    private Processor $processor;
    private ConfigurationInterface $configuration;

    public function __construct()
    {
        $this->processor = new Processor();
        $this->configuration = new Akeneo\Configuration\Logger();
    }

    public function configuration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * @throws Configurator\ConfigurationExceptionInterface
     */
    public function normalize(array $config): array
    {
        try {
            return $this->processor->processConfiguration($this->configuration, $config);
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new Configurator\InvalidConfigurationException($exception->getMessage(), 0, $exception);
        }
    }

    public function validate(array $config): bool
    {
        try {
            $this->normalize($config);

            return true;
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            return false;
        }
    }

    public function compile(array $config): Akeneo\Builder\Logger
    {
        $builder = new Akeneo\Builder\Logger();
        try {
            if ($config['type'] === 'stderr') {
                $builder->withLogger((new Akeneo\Builder\StderrLogger())->getNode());
            } else {
                $builder->withLogger((new Akeneo\Builder\NullLogger())->getNode());
            }

            return $builder;
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new Configurator\InvalidConfigurationException(
                message: $exception->getMessage(),
                previous: $exception
            );
        }
    }
}
