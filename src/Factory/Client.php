<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Factory;

use Kiboko\Component\ETL\Flow\Akeneo\Builder;
use Kiboko\Component\ETL\Flow\Akeneo\Configuration;
use Kiboko\Contract\ETL\Configurator\ConfigurationException;
use Kiboko\Contract\ETL\Configurator\ConfigurationExceptionInterface;
use Kiboko\Contract\ETL\Configurator\FactoryInterface;
use PhpParser\Node;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\Processor;

final class Client implements FactoryInterface
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

    private function buildFactoryNode(string $name): Node\Expr
    {
        if (($position = strpos($name, '::')) === false) {
            return new Node\Expr\New_(
                new Node\Name\FullyQualified($name),
            );
        } else {
            return new Node\Expr\StaticCall(
                new Node\Name\FullyQualified(substr($name, 0, $position)),
                new Node\Identifier(substr($name, $position + 2)),
            );
        }
    }

    public function compile(array $config): array
    {
        $clientBuilder = new Builder\Client(
            new Node\Scalar\String_($config['client']['api_url']),
            new Node\Scalar\String_($config['client']['client_id']),
            new Node\Scalar\String_($config['client']['secret']),
        );

        if (isset($config['enterprise'])) {
            $clientBuilder->withEnterpriseSupport($config['enterprise']);
        }

        if (isset($config['client']['context'])) {
            if (isset($config['client']['context']['http_client'])) {
                $clientBuilder->withHttpClient($this->buildFactoryNode($config['client']['context']['http_client']));
            }
            if (isset($config['client']['context']['http_request_factory'])) {
                $clientBuilder->withHttpRequestFactory($this->buildFactoryNode($config['client']['context']['http_request_factory']));
            }
            if (isset($config['client']['context']['http_stream_factory'])) {
                $clientBuilder->withHttpStreamFactory($this->buildFactoryNode($config['client']['context']['http_stream_factory']));
            }
            if (isset($config['client']['context']['filesystem'])) {
                $clientBuilder->withFileSystem($this->buildFactoryNode($config['client']['context']['filesystem']));
            }
        }

        if (isset($config['client']['password'])) {
            $clientBuilder->withPassword(
                new Node\Scalar\String_($config['client']['username']),
                new Node\Scalar\String_($config['client']['password']),
            );
        } else if (isset($config['refresh_token'])) {
            $clientBuilder->withPassword(
                new Node\Scalar\String_($config['client']['token']),
                new Node\Scalar\String_($config['client']['refresh_token']),
            );
        }

        return [
            $clientBuilder->getNode()
        ];
    }
}
