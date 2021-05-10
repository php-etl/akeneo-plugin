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
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

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
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException) {
            return false;
        }
    }

    /**
     * @throws ConfigurationExceptionInterface
     */
    public function compile(array $config): RepositoryInterface
    {
        $clientFactory = new Factory\Client();

        $interpreter = new ExpressionLanguage();
        if (array_key_exists('expression_language', $config)
            && is_array($config['expression_language'])
            && count($config['expression_language'])
        ) {
            foreach ($config['expression_language'] as $provider) {
                $interpreter->registerProvider(new $provider);
            }
        }

        try {
            if (array_key_exists('extractor', $config)) {
                $extractorFactory = new Factory\Extractor();

                $extractor = $extractorFactory->compile($config['extractor']);
                $extractorBuilder = $extractor->getBuilder();

                $client = $clientFactory->compile($config['client']);
                $client->getBuilder()->withEnterpriseSupport($config['enterprise']);

                $extractorBuilder
                    ->withClient($client->getBuilder()->getNode());

                $extractor
                    ->merge($client);

                return $extractor;
            } elseif (array_key_exists('loader', $config)) {
                $loaderFactory = new Factory\Loader();

                $loader = $loaderFactory->compile($config['loader']);
                $loaderBuilder = $loader->getBuilder();

                $client = $clientFactory->compile($config['client']);
                $client->getBuilder()->withEnterpriseSupport($config['enterprise']);

                $loaderBuilder
                    ->withClient($client->getBuilder()->getNode());

                $loader
                    ->merge($client);

                return $loader;
            } elseif (array_key_exists('conditional', $config)) {
                $loaderFactory = new Factory\Lookup($interpreter);

                $lookup = $loaderFactory->compile($config['conditional']);
                $loaderBuilder = $lookup->getBuilder();

                $client = $clientFactory->compile($config['client']);
                $client->getBuilder()->withEnterpriseSupport($config['enterprise']);

                $loaderBuilder
                    ->withClient($client->getBuilder()->getNode());

                $lookup
                    ->merge($client);

                return $lookup;
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
