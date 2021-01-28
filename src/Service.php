<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo;

use Kiboko\Contract\Configurator\RepositoryInterface;
use Kiboko\Plugin\Akeneo\Factory;
use Kiboko\Contract\Configurator\InvalidConfigurationException;
use Kiboko\Contract\Configurator\ConfigurationExceptionInterface;
use Kiboko\Contract\Configurator\FactoryInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception as Symfony;
use Symfony\Component\Config\Definition\Processor;

final class Service implements FactoryInterface
{
    private Processor $processor;
    private ConfigurationInterface $configuration;

    public function __construct()
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
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
            $this->processor->processConfiguration($this->configuration, $config);

            return true;
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            return false;
        }
    }

    /**
     * @throws ConfigurationExceptionInterface
     */
    public function compile(array $config): RepositoryInterface
    {
        $clientFactory = new Factory\Client();
        $loggerFactory = new Factory\Logger();

        try {
            if (array_key_exists('extractor', $config)) {
                $extractorFactory = new Factory\Extractor();

                $extractor = $extractorFactory->compile($config['extractor']);
                $extractorBuilder = $extractor->getBuilder();

                $logger = $loggerFactory->compile($config['logger'] ?? []);

                $client = $clientFactory->compile($config['client']);
                $client->getBuilder()->withEnterpriseSupport($config['enterprise']);

                $extractorBuilder
                    ->withClient($client->getBuilder()->getNode())
                    ->withLogger($logger->getBuilder()->getNode());

                $extractor
                    ->merge($client)
                    ->merge($logger);

                return $extractor;
            } else if (array_key_exists('loader', $config)) {
                $loaderFactory = new Factory\Loader();

                $loader = $loaderFactory->compile($config['loader']);
                $loaderBuilder = $loader->getBuilder();

                $logger = $loggerFactory->compile($config['logger'] ?? []);

                $client = $clientFactory->compile($config['client']);
                $client->getBuilder()->withEnterpriseSupport($config['enterprise']);

                $loaderBuilder
                    ->withClient($client->getBuilder()->getNode())
                    ->withLogger($logger->getBuilder()->getNode());

                $loader
                    ->merge($client)
                    ->merge($logger);

                return $loader;
            } else {
                throw new InvalidConfigurationException(
                    'Could not determine if the factory should build an extractor or a loader.'
                );
            }
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
