<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Component\FastMapConfig;
use Kiboko\Plugin\Akeneo;
use Kiboko\Contract\Configurator;
use Kiboko\Plugin\FastMap;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception as Symfony;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class Lookup implements Configurator\FactoryInterface
{
    private Processor $processor;
    private ConfigurationInterface $configuration;
    /** @var iterable<Akeneo\Capacity\CapacityInterface> */
    private iterable $capacities;

    public function __construct(private ExpressionLanguage $interpreter)
    {
        $this->processor = new Processor();
        $this->configuration = new Akeneo\Configuration\Lookup();
        $this->capacities = [
            new Akeneo\Capacity\Lookup\All($this->interpreter),
            new Akeneo\Capacity\Lookup\ListPerPage($this->interpreter),
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

    public function compile(array $config): Repository\Lookup
    {
        if (!array_key_exists('conditional', $config)) {
            $builder = new Akeneo\Builder\Lookup($this->interpreter);
            $repository = new Repository\Lookup($builder);

            if (array_key_exists('enterprise', $config)) {
                $builder->withEnterpriseSupport($config['enterprise']);
            }

            try {
                $builder->withCapacity(
                    $this->findCapacity($config)->getBuilder($config)
                );
            } catch (NoApplicableCapacityException $exception) {
                throw new Configurator\InvalidConfigurationException(
                    message: 'Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.',
                    previous: $exception,
                );
            }

            if (array_key_exists('merge', $config)) {
                if (array_key_exists('map', $config['merge'])) {
                    $mapper = new FastMapConfig\ArrayBuilder(
                        interpreter: $this->interpreter,
                    );

                    $fastMap = new Akeneo\Builder\Inline($mapper);

                    (new FastMap\Configuration\ConfigurationApplier(['lookup' => []]))($mapper->children(), $config['merge']['map']);

                    $builder->withMerge($fastMap);
                }
            }
        } else {
            $builder = new Akeneo\Builder\ConditionalLookup($this->interpreter);
            $repository = new Repository\Lookup($builder);

            foreach ($config['conditional'] as $alternative) {
                $alternativeBuilder = new Akeneo\Builder\AlternativeLookup($this->interpreter);

                try {
                    $alternativeBuilder->withCapacity(
                        $this->findCapacity($alternative)->getBuilder($alternative)
                    );
                } catch (NoApplicableCapacityException $exception) {
                    throw new Configurator\InvalidConfigurationException(
                        message: 'Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.',
                        previous: $exception,
                    );
                }

                $builder->addAlternative(
                    $alternative['condition'],
                    $alternativeBuilder
                );

                if (array_key_exists('merge', $alternative)) {
                    if (array_key_exists('map', $alternative['merge'])) {
                        $mapper = new FastMapConfig\ArrayBuilder(
                            interpreter: $this->interpreter,
                        );

                        $fastMap = new Akeneo\Builder\Inline($mapper);

                        (new FastMap\Configuration\ConfigurationApplier(['lookup' => []]))($mapper->children(), $alternative['merge']['map']);

                        $alternativeBuilder->withMerge($fastMap);
                    }
                }
            }
        }

        try {
            return $repository;
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new Configurator\InvalidConfigurationException(
                message: $exception->getMessage(),
                previous: $exception
            );
        }
    }
}
