<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Factory;

use Kiboko\Component\ETL\Flow\Akeneo\Builder;
use Kiboko\Component\ETL\Flow\Akeneo\Configuration;
use Kiboko\Contract\ETL\Configurator\InvalidConfigurationException;
use Kiboko\Contract\ETL\Configurator\ConfigurationExceptionInterface;
use Kiboko\Contract\ETL\Configurator\FactoryInterface;
use PhpParser\Node;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception as Symfony;
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

    public function compile(array $config): Builder\Client
    {
        $clientBuilder = new Builder\Client(
            new Node\Scalar\String_($config['api_url']),
            new Node\Scalar\String_($config['client_id']),
            new Node\Scalar\String_($config['secret']),
        );

        if (isset($config['context'])) {
            if (isset($config['context']['http_client'])) {
                $clientBuilder->withHttpClient($this->buildFactoryNode($config['context']['http_client']));
            }
            if (isset($config['context']['http_request_factory'])) {
                $clientBuilder->withHttpRequestFactory($this->buildFactoryNode($config['context']['http_request_factory']));
            }
            if (isset($config['context']['http_stream_factory'])) {
                $clientBuilder->withHttpStreamFactory($this->buildFactoryNode($config['context']['http_stream_factory']));
            }
            if (isset($config['context']['filesystem'])) {
                $clientBuilder->withFileSystem($this->buildFactoryNode($config['context']['filesystem']));
            }
        }

        if (isset($config['password'])) {
            $clientBuilder->withPassword(
                new Node\Scalar\String_($config['username']),
                new Node\Scalar\String_($config['password']),
            );
        } else if (isset($config['refresh_token'])) {
            $clientBuilder->withPassword(
                new Node\Scalar\String_($config['token']),
                new Node\Scalar\String_($config['refresh_token']),
            );
        }

        return $clientBuilder;
    }
}
