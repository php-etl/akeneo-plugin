<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Plugin\Akeneo;
use Kiboko\Contract\Configurator;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception as Symfony;
use Symfony\Component\Config\Definition\Processor;

final class Loader implements Configurator\FactoryInterface
{
    private Processor $processor;
    private ConfigurationInterface $configuration;
    /** @var iterable<Akeneo\Capacity\CapacityInterface>  */
    private iterable $capacities;

    public function __construct()
    {
        $this->processor = new Processor();
        $this->configuration = new Akeneo\Configuration\Loader();
        $this->capacities = [
            new Akeneo\Capacity\Upsert(),
        ];
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

    private function findCapacity(array $config): Akeneo\Capacity\CapacityInterface
    {
        foreach ($this->capacities as $capacity) {
            if ($capacity->applies($config)) {
                return $capacity;
            }
        }

        throw new NoApplicableCapacityException(
            message: 'No capacity was able to handle the configuration.'
        );
    }

    public function compile(array $config): Repository\Loader
    {
        $builder = new Akeneo\Builder\Loader();

        try {
            $builder->withCapacity($this->findCapacity($config)->getBuilder($config));
        } catch (NoApplicableCapacityException $exception) {
            throw new Configurator\InvalidConfigurationException(
                message: 'Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.',
                previous: $exception,
            );
        }

        if (isset($config['enterprise'])) {
            $builder->withEnterpriseSupport($config['enterprise']);
        }

        try {
            return new Repository\Loader($builder);
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new Configurator\InvalidConfigurationException(
                message: $exception->getMessage(),
                previous: $exception
            );
        }
    }
}
