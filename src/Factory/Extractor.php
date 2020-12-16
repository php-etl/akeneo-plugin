<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Factory;

use Kiboko\Component\ETL\Flow\Akeneo\Builder;
use Kiboko\Component\ETL\Flow\Akeneo\Capacity;
use Kiboko\Component\ETL\Flow\Akeneo\Configuration;
use Kiboko\Component\ETL\Flow\Akeneo\MissingAuthenticationMethodException;
use Kiboko\Contract\ETL\Configurator\ConfigurationExceptionInterface;
use Kiboko\Contract\ETL\Configurator\FactoryInterface;
use Kiboko\Contract\ETL\Configurator\InvalidConfigurationException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception as Symfony;
use Symfony\Component\Config\Definition\Processor;

final class Extractor implements FactoryInterface
{
    private Processor $processor;
    private ConfigurationInterface $configuration;
    /** @var iterable<Capacity\CapacityInterface>  */
    private iterable $capacities;

    public function __construct()
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
        $this->capacities = [
            new Capacity\All(),
        ];
    }

    public function configuration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * @throws ConfigurationExceptionInterface
     */
    public function normalize(array $config): array
    {
        try {
            return $this->processor->processConfiguration($this->configuration, $config);
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new InvalidConfigurationException($exception->getMessage(), 0, $exception);
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

    public function compile(array $config): Builder\Extractor
    {
        $builder = new Builder\Extractor();

        foreach ($this->capacities as $capacity) {
            if ($capacity->applies($config)) {
                $builder->withCapacity($capacity->getBuilder($config));
                break;
            }
        }

        if (isset($config['enterprise'])) {
            $builder->withEnterpriseSupport($config['enterprise']);
        }

        try {
            return $builder;
        } catch (MissingAuthenticationMethodException $exception) {
            throw new InvalidConfigurationException(
                'Your Akeneo API configuration is missing an authentication method, you should either define "username" or "token" options.',
                0,
                $exception,
            );
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new InvalidConfigurationException($exception->getMessage(), 0, $exception);
        }
    }
}
