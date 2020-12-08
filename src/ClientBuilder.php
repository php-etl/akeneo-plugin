<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo;

use PhpParser\Builder;
use PhpParser\Node;

final class ClientBuilder implements Builder
{
    private bool $withEnterpriseSupport;
    private Node\Expr $baseUrl;
    private Node\Expr $clientId;
    private Node\Expr $secret;
    private ?Node\Expr $username;
    private ?Node\Expr $password;
    private ?Node\Expr $token;
    private ?Node\Expr $refreshToken;

    public function __construct(Node\Expr $baseUrl, Node\Expr $clientId, Node\Expr $secret)
    {
        $this->withEnterpriseSupport = false;
        $this->baseUrl = $baseUrl;
        $this->clientId = $clientId;
        $this->secret = $secret;
        $this->username = null;
        $this->password = null;
        $this->token = null;
        $this->refreshToken = null;
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

    public function getNode(): Node
    {
        return new Node\Expr\MethodCall(
            new Node\Expr\New_(
                !$this->withEnterpriseSupport ?
                    new Node\Name\FullyQualified('Akeneo\\Pim\\ApiClient\\AkeneoPimClientBuilder') :
                    new Node\Name\FullyQualified('Akeneo\\PimEnterprise\\ApiClient\\AkeneoPimEnterpriseClientBuilder'),
                [
                    new Node\Arg($this->baseUrl),
                ],
            ),
            $this->getFactoryMethod(),
            $this->getFactoryArguments(),
        );
    }

    private function getFactoryMethod(): string
    {
        if ($this->password !== null) {
            return 'buildAuthenticatedByPassword';
        }

        if ($this->refreshToken !== null) {
            return 'buildAuthenticatedByToken';
        }

        throw new \RuntimeException('Please check your client builder, you should either call withToken() or withPassword() methods.');
    }

    private function getFactoryArguments(): array
    {
        if ($this->password !== null) {
            return [
                $this->clientId,
                $this->secret,
                $this->username,
                $this->password,
            ];
        }

        if ($this->refreshToken !== null) {
            return [
                $this->clientId,
                $this->secret,
                $this->token,
                $this->refreshToken,
            ];
        }

        throw new \RuntimeException('Please check your client builder, you should either call withToken() or withPassword() methods.');
    }
}
