<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo;

use Kiboko\Component\ETL\Flow\Akeneo\Builder;
use Kiboko\Component\ETL\Flow\Akeneo\Factory;
use Kiboko\Contract\ETL\Configurator\InvalidConfigurationException;
use Kiboko\Contract\ETL\Configurator\ConfigurationExceptionInterface;
use Kiboko\Contract\ETL\Configurator\FactoryInterface;
use PhpParser\Node;
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
            $this->normalize($config);

            return true;
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
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
     * @throws ConfigurationExceptionInterface
     */
    public function compile(array $config): \PhpParser\Builder
    {
        $clientFactory = new Factory\Client();
        $extractorFactory = new Factory\Extractor();

        if (isset($config['extractor'])) {
            $extractor = $extractorFactory->compile($config['extractor']);

            $client = $clientFactory->compile($config['client']);

            $client->withEnterpriseSupport($config['enterprise']);

            $extractor->withClient($client->getNode());

            return $extractor;
        } else if (isset($config['loader'])) {
            $builder = new Builder\Loader();

            $builder->withEndpoint(new Node\Identifier(sprintf('get%sApi', ucfirst($config['loader']['type']))));
            $builder->withMethod(isset($config['loader']['method']) ? new Node\Identifier($config['loader']['method']) : null);
        } else {
            throw new InvalidConfigurationException(
                'Could not determine if the factory should build an extractor or a loader.'
            );
        }

        if (isset($config['enterprise'])) {
            $builder->withEnterpriseSupport($config['enterprise']);
        }

        $builder->withClient(
            ...$clientFactory->compile(['client' => $config['client']])
        );

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
