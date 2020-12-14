<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Configurator;

use Kiboko\Component\ETL\Flow\Akeneo\Builder;
use Kiboko\Component\ETL\Flow\Akeneo\Configuration;
use Kiboko\Component\ETL\Flow\Akeneo\Factory;
use Kiboko\Component\ETL\Flow\Akeneo\MissingAuthenticationMethodException;
use Kiboko\Contract\ETL\Configurator\ConfigurationException;
use Kiboko\Contract\ETL\Configurator\ConfigurationExceptionInterface;
use Kiboko\Contract\ETL\Configurator\FactoryInterface;
use PhpParser\Node;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\Processor;

final class ServiceFactory implements FactoryInterface
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
        } catch (InvalidTypeException|InvalidConfigurationException $exception) {
            throw new ConfigurationException($exception->getMessage(), 0, $exception);
        }
    }

    public function validate(array $config): bool
    {
        try {
            $this->normalize($config);

            return true;
        } catch (InvalidTypeException|InvalidConfigurationException $exception) {
            return false;
        }
    }

    private function compileFilters(array ...$filters): Node
    {
        $builder = new Builder\Search();
        foreach ($filters as $filter) {
            $builder->addFilter(...$filter);
        }

        return $builder->getNode();
    }

    /**
     * @throws ConfigurationException
     */
    public function compile(array $config): array
    {
        $client = new Factory\Client();
        $extractor = new Factory\Extractor();

        if (isset($config['extractor'])) {
            return $extractor->compile($config['extractor']);
        } else if (isset($config['loader'])) {
            $builder = new Builder\Loader();

            $builder->withEndpoint(new Node\Identifier(sprintf('get%sApi', ucfirst($config['loader']['type']))));
            $builder->withMethod(isset($config['loader']['method']) ? new Node\Identifier($config['loader']['method']) : null);
        } else {
            throw new ConfigurationException(
                'Could not determine if the factory should build an extractor or a loader.'
            );
        }

        if (isset($config['enterprise'])) {
            $builder->withEnterpriseSupport($config['enterprise']);
        }

        $builder->withClient(
            ...$client->compile(['client' => $config['client']])
        );

        try {
            return [
                $builder->getNode(),
            ];
        } catch (MissingAuthenticationMethodException $exception) {
            throw new ConfigurationException(
                'Your Akeneo API configuration is missing an authentication method, you should either define "username" or "token" options.',
                0,
                $exception,
            );
        } catch (InvalidTypeException|InvalidConfigurationException $exception) {
            throw new ConfigurationException($exception->getMessage(), 0, $exception);
        }
    }
}
