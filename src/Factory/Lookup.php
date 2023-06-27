<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Component\FastMapConfig;
use Kiboko\Contract\Configurator;
use Kiboko\Plugin\Akeneo;
use Kiboko\Plugin\FastMap;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception as Symfony;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function Kiboko\Component\SatelliteToolbox\Configuration\compileValueWhenExpression;

final readonly class Lookup implements Configurator\FactoryInterface
{
    private Processor $processor;
    private ConfigurationInterface $configuration;
    /** @var iterable<Akeneo\Capacity\CapacityInterface> */
    private iterable $capacities;

    public function __construct(
        private ExpressionLanguage $interpreter = new ExpressionLanguage(),
    ) {
        $this->processor = new Processor();
        $this->configuration = new Akeneo\Configuration\Lookup();
        $this->capacities = [
            new Akeneo\Capacity\Lookup\All($this->interpreter),
            new Akeneo\Capacity\Lookup\Get($this->interpreter),
            new Akeneo\Capacity\Lookup\ListPerPage($this->interpreter),
            new Akeneo\Capacity\Lookup\Download($this->interpreter),
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
            throw new Configurator\InvalidConfigurationException($exception->getMessage(), previous: $exception);
        }
    }

    public function validate(array $config): bool
    {
        try {
            $this->normalize($config);

            return true;
        } catch (Configurator\ConfigurationExceptionInterface) {
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

        throw new NoApplicableCapacityException(message: 'No capacity was able to handle the configuration.');
    }

    private function merge(Akeneo\Builder\AlternativeLookup $alternativeBuilder, array $config): void
    {
        if (\array_key_exists('merge', $config)) {
            if (\array_key_exists('map', $config['merge'])) {
                $mapper = new FastMapConfig\ArrayAppendBuilder(
                    interpreter: $this->interpreter,
                );

                $fastMap = new Akeneo\Builder\Inline($mapper);

                (new FastMap\Configuration\ConfigurationApplier(['lookup' => []]))($mapper->children(), $config['merge']['map']);

                $alternativeBuilder->withMerge($fastMap);
            }
        }
    }

    public function compile(array $config): Repository\Lookup
    {
        try {
            if (!\array_key_exists('conditional', $config)) {
                try {
                    $alternativeBuilder = new Akeneo\Builder\AlternativeLookup(
                        $this->findCapacity($config)->getBuilder($config)
                    );
                    $builder = new Akeneo\Builder\Lookup($alternativeBuilder);
                    $repository = new Repository\Lookup($builder);
                } catch (NoApplicableCapacityException $exception) {
                    throw new Configurator\InvalidConfigurationException(message: 'Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.', previous: $exception);
                }

                $this->merge($alternativeBuilder, $config);
            } else {
                $builder = new Akeneo\Builder\ConditionalLookup();
                $repository = new Repository\Lookup($builder);

                foreach ($config['conditional'] as $alternative) {
                    try {
                        $alternativeBuilder = new Akeneo\Builder\AlternativeLookup(
                            $this->findCapacity($alternative)->getBuilder($alternative)
                        );
                    } catch (NoApplicableCapacityException $exception) {
                        throw new Configurator\InvalidConfigurationException(message: 'Your Akeneo API configuration is using some unsupported capacity, check your "type" and "method" properties to a suitable set.', previous: $exception);
                    }

                    $builder->addAlternative(
                        compileValueWhenExpression($this->interpreter, $alternative['condition']),
                        $alternativeBuilder
                    );

                    $this->merge($alternativeBuilder, $alternative);
                }
            }

            return $repository;
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new Configurator\InvalidConfigurationException(message: $exception->getMessage(), previous: $exception);
        }
    }
}
