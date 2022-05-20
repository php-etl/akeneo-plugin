<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder;

use Kiboko\Plugin\Akeneo\MissingAuthenticationMethodException;
use PhpParser\Builder;
use PhpParser\Node;

final class Client implements Builder
{
    private bool $withEnterpriseSupport;
    private ?Node\Expr $username;
    private ?Node\Expr $password;
    private ?Node\Expr $token;
    private ?Node\Expr $refreshToken;
    private ?Node\Expr $httpClient;
    private ?Node\Expr $httpRequestFactory;
    private ?Node\Expr $httpStreamFactory;
    private ?Node\Expr $fileSystem;

    public function __construct(
        private Node\Expr $baseUrl,
        private Node\Expr $clientId,
        private Node\Expr $secret
    ) {
        $this->withEnterpriseSupport = false;
        $this->username = null;
        $this->password = null;
        $this->token = null;
        $this->refreshToken = null;
        $this->httpClient = null;
        $this->httpRequestFactory = null;
        $this->httpStreamFactory = null;
        $this->fileSystem = null;
    }

    public function withEnterpriseSupport(bool $withEnterpriseSupport): self
    {
        $this->withEnterpriseSupport = $withEnterpriseSupport;

        return $this;
    }

    public function withToken(Node\Expr $token, Node\Expr $refreshToken): self
    {
        $this->token = $token;
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function withPassword(Node\Expr $username, Node\Expr $password): self
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    public function withHttpClient(Node\Expr $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function withHttpRequestFactory(Node\Expr $httpRequestFactory): self
    {
        $this->httpRequestFactory = $httpRequestFactory;

        return $this;
    }

    public function withHttpStreamFactory(Node\Expr $httpStreamFactory): self
    {
        $this->httpStreamFactory = $httpStreamFactory;

        return $this;
    }

    public function withFileSystem(Node\Expr $fileSystem): self
    {
        $this->fileSystem = $fileSystem;

        return $this;
    }

    public function getNode(): Node\Expr\MethodCall
    {
        $instance = new Node\Expr\New_(
            !$this->withEnterpriseSupport ?
                new Node\Name\FullyQualified('Akeneo\\Pim\\ApiClient\\AkeneoPimClientBuilder') :
                new Node\Name\FullyQualified('Akeneo\\PimEnterprise\\ApiClient\\AkeneoPimEnterpriseClientBuilder'),
            [
                new Node\Arg($this->baseUrl),
            ],
        );

        if (null !== $this->httpClient) {
            $instance = new Node\Expr\MethodCall(
                $instance,
                'setHttpClient',
                [
                    new Node\Arg($this->httpClient),
                ],
            );
        }

        if (null !== $this->httpRequestFactory) {
            $instance = new Node\Expr\MethodCall(
                $instance,
                'setRequestFactory',
                [
                    new Node\Arg($this->httpRequestFactory),
                ],
            );
        }

        if (null !== $this->httpStreamFactory) {
            $instance = new Node\Expr\MethodCall(
                $instance,
                'setStreamFactory',
                [
                    new Node\Arg($this->httpStreamFactory),
                ],
            );
        }

        if (null !== $this->fileSystem) {
            $instance = new Node\Expr\MethodCall(
                $instance,
                'setFileSystem',
                [
                    new Node\Arg($this->fileSystem),
                ],
            );
        }

        return new Node\Expr\MethodCall(
            $instance,
            $this->getFactoryMethod(),
            $this->getFactoryArguments(),
        );
    }

    private function getFactoryMethod(): string
    {
        if (null !== $this->password) {
            return 'buildAuthenticatedByPassword';
        }

        if (null !== $this->refreshToken) {
            return 'buildAuthenticatedByToken';
        }

        throw new MissingAuthenticationMethodException('Please check your client builder, you should either call withToken() or withPassword() methods.');
    }

    private function getFactoryArguments(): array
    {
        if (null !== $this->password) {
            return [
                $this->clientId,
                $this->secret,
                $this->username,
                $this->password,
            ];
        }

        if (null !== $this->refreshToken) {
            return [
                $this->clientId,
                $this->secret,
                $this->token,
                $this->refreshToken,
            ];
        }

        throw new MissingAuthenticationMethodException('Please check your client builder, you should either call withToken() or withPassword() methods.');
    }
}
