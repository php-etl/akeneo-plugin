<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Configurator;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientBuilder;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Kiboko\Component\ETL\Contract\Configurator\ServiceInterface;
use Kiboko\Component\ETL\Contracts\ExtractorInterface;
use Kiboko\Component\ETL\Flow\Akeneo\ClientBuilder;
use Kiboko\Component\ETL\Flow\Akeneo\ConfigurationException;
use Kiboko\Component\ETL\Flow\Akeneo\ExtractorBuilder;
use Kiboko\Component\ETL\Flow\Akeneo\MissingAuthenticationMethodException;
use PhpParser\Node;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\Processor;

final class ServiceFactory implements ServiceInterface
{
    private Processor $processor;
    private ConfigurationInterface $configuration;

    public function __construct()
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
    }

    /**
     * @throws ConfigurationException
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

    /**
     * @throws ConfigurationException
     */
    public function compile(array $config): array
    {
        $config = $this->normalize($config);

        $clientBuilder = new ClientBuilder(
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

        $builder = new ExtractorBuilder();

        if (isset($config['enterprise'])) {
            $builder->withEnterpriseSupport($config['enterprise']);
        }

        $builder->withClient(
            $clientBuilder->getNode(),
        );

        $builder->withEndpoint(new Node\Identifier(sprintf('get%sApi', ucfirst($config['endpoint']['type']))));
        $builder->withMethod(isset($config['endpoint']['method']) ? new Node\Identifier($config['endpoint']['method']) : null);

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

    public function buildClient(array $config): AkeneoPimClientInterface|AkeneoPimEnterpriseClientInterface
    {
        $config = $this->normalize($config);

        if ($config['enterprise'] === true) {
            $builder = new AkeneoPimEnterpriseClientBuilder($config['client']['api_url']);
        } else {
            $builder = new AkeneoPimClientBuilder($config['client']['api_url']);
        }
        if (isset($config['client']['context'])) {
            if (isset($config['client']['context']['http_client'])) {
                $builder->setHttpClient(new $config['client']['context']['http_client']());
            }
            if (isset($config['client']['context']['http_request_factory'])) {
                $builder->setRequestFactory(new $config['client']['context']['http_request_factory']());
            }
            if (isset($config['client']['context']['http_stream_factory'])) {
                $builder->setStreamFactory(new $config['client']['context']['http_stream_factory']());
            }
            if (isset($config['client']['context']['filesystem'])) {
                $builder->setFileSystem(new $config['client']['context']['filesystem']());
            }
        }

        if (isset($config['client']['password'])) {
            return $builder->buildAuthenticatedByPassword($config['client']['client_id'], $config['client']['secret'], $config['client']['username'], $config['client']['password']);
        } else if (isset($config['client']['refresh_token'])) {
            return $builder->buildAuthenticatedByToken($config['client']['client_id'], $config['client']['secret'], $config['client']['token'], $config['client']['refresh_token']);
        }

        throw new ConfigurationException('No authentication method provided for the Akeneo API');
    }

    public function build(array $config): ExtractorInterface
    {
        $config = $this->normalize($config);

        if ($config['enterprise'] === true) {
            $builder = new AkeneoPimEnterpriseClientBuilder($config['client']['api_url']);
        } else {
            $builder = new AkeneoPimClientBuilder($config['client']['api_url']);
        }
        if (isset($config['client']['context'])) {
            if (isset($config['client']['context']['http_client'])) {
                $builder->setHttpClient(new $config['client']['context']['http_client']());
            }
            if (isset($config['client']['context']['http_request_factory'])) {
                $builder->setRequestFactory(new $config['client']['context']['http_request_factory']());
            }
            if (isset($config['client']['context']['http_stream_factory'])) {
                $builder->setStreamFactory(new $config['client']['context']['http_stream_factory']());
            }
            if (isset($config['client']['context']['filesystem'])) {
                $builder->setFileSystem(new $config['client']['context']['filesystem']());
            }
        }

        if (isset($config['client']['password'])) {
            return $builder->buildAuthenticatedByPassword($config['client']['client_id'], $config['client']['secret'], $config['client']['username'], $config['client']['password']);
        } else if (isset($config['client']['refresh_token'])) {
            return $builder->buildAuthenticatedByToken($config['client']['client_id'], $config['client']['secret'], $config['client']['token'], $config['client']['refresh_token']);
        }

        throw new ConfigurationException('No authentication method provided for the Akeneo API');
    }
}
