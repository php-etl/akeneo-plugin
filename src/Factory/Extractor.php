<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Factory;

use Kiboko\Plugin\Akeneo;
use Kiboko\Contract\Configurator;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception as Symfony;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class Extractor implements Configurator\FactoryInterface
{
    private Processor $processor;
    private ConfigurationInterface $configuration;
    /** @var iterable<Akeneo\Capacity\CapacityInterface>  */
    private iterable $capacities;
    private ExpressionLanguage $interpreter;

    public function __construct(
        ?ExpressionLanguage $interpreter = null,
    ) {
        $this->interpreter = $interpreter ?? new ExpressionLanguage();
        $this->processor = new Processor();
        $this->configuration = new Akeneo\Configuration\Extractor();
        $this->capacities = [
            new Akeneo\Capacity\Extractor\All($this->interpreter),
            new Akeneo\Capacity\Extractor\Get($this->interpreter),
            new Akeneo\Capacity\Extractor\ListPerPage($this->interpreter),
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
        } catch (Configurator\InvalidConfigurationException) {
            return false;
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException) {
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

    public function compile(array $config): Repository\Extractor
    {
        $builder = new Akeneo\Builder\Extractor();

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

        if (array_key_exists('enterprise', $config)) {
            $builder->withEnterpriseSupport($config['enterprise']);
        }

        try {
            return new Repository\Extractor($builder);
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new Configurator\InvalidConfigurationException(
                message: $exception->getMessage(),
                previous: $exception
            );
        }
    }
}
