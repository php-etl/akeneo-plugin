<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo;

use Kiboko\Component\ETL\Flow\Akeneo\Builder;
use Kiboko\Component\ETL\Flow\Akeneo\Factory;
use Kiboko\Contract\ETL\Configurator\InvalidConfigurationException;
use Kiboko\Contract\ETL\Configurator\ConfigurationExceptionInterface;
use Kiboko\Contract\ETL\Configurator\FactoryInterface;
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
    public function compile(array $config): \PhpParser\Builder
    {
        $clientFactory = new Factory\Client();
        $loggerFactory = new Factory\Logger();

        try {
            if (isset($config['extractor'])) {
                $extractorFactory = new Factory\Extractor();

                $extractor = $extractorFactory->compile($config['extractor']);

                $client = $clientFactory->compile($config['client']);
                $client->withEnterpriseSupport($config['enterprise']);

                $logger = $loggerFactory->compile($config['logger'] ?? []);

                $extractor->withClient($client->getNode());
                $extractor->withLogger($logger->getNode());

                return $extractor;
            } else if (isset($config['loader'])) {
                $loaderFactory = new Factory\Loader();

                $loader = $loaderFactory->compile($config['loader']);

                $client = $clientFactory->compile($config['client']);
                $client->withEnterpriseSupport($config['enterprise']);

                $logger = $loggerFactory->compile($config['logger'] ?? []);

                $loader->withClient($client->getNode());
                $loader->withLogger($logger->getNode());

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
