<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Mock;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Faker\Factory;
use functional\Kiboko\Plugin\Akeneo\Mock;
use PhpParser\Builder;
use PhpParser\Node;

final class ApiClientMockBuilder implements Builder
{
    private Node\Expr $node;

    public function __construct()
    {
        $faker = Factory::create();

        $this->node = new Node\Expr\New_(
            class: new Node\Name\FullyQualified(AkeneoPimClientBuilder::class),
            args: [
                new Node\Arg(
                    new Node\Scalar\String_('https://akeneo.'.$faker->safeEmailDomain()),
                ),
            ],
        );;
    }

    public function withHttpClient(Mock\HttpClientBuilder $httpClient): self
    {
        $this->node = new Node\Expr\MethodCall(
            var: $this->node,
            name: new Node\Identifier('setHttpClient'),
            args: [
                new Node\Arg(
                    $httpClient->getNode(),
                ),
            ],
        );

        return $this;
    }

    public function withRequestFactory(Mock\RequestFactoryBuilder $requestFactory): self
    {
        $this->node = new Node\Expr\MethodCall(
            var: $this->node,
            name: new Node\Identifier('setRequestFactory'),
            args: [
                new Node\Arg(
                    $requestFactory->getNode(),
                ),
            ],
        );

        return $this;
    }

    public function withStreamFactory(Mock\StreamFactoryBuilder $streamFactory): self
    {
        $this->node = new Node\Expr\MethodCall(
            var: $this->node,
            name: new Node\Identifier('setStreamFactory'),
            args: [
                new Node\Arg(
                    $streamFactory->getNode(),
                ),
            ],
        );

        return $this;
    }

    public function withFileSystem(Mock\FileSystemBuilder $fileSystem): self
    {
        $this->node = new Node\Expr\MethodCall(
            var: $this->node,
            name: new Node\Identifier('setFileSystem'),
            args: [
                new Node\Arg(
                    $fileSystem->getNode(),
                ),
            ],
        );

        return $this;
    }

    public function withAuthenticatedByPassword(): self
    {
        $faker = Factory::create();

        $this->node = new Node\Expr\MethodCall(
            var: $this->node,
            name: new Node\Identifier('buildAuthenticatedByPassword'),
            args: [
                new Node\Arg(
                    new Node\Scalar\String_($faker->regexify('\d{1,2}_[0-9a-f]{48}')),
                ),
                new Node\Arg(
                    new Node\Scalar\String_($faker->regexify('[0-9a-z]{64}')),
                ),
                new Node\Arg(
                    new Node\Scalar\String_($faker->userName()),
                ),
                new Node\Arg(
                    new Node\Scalar\String_($faker->password()),
                ),
            ],
        );

        return $this;
    }

    public function withAuthenticatedByToken(): self
    {
        $faker = Factory::create();

        $this->node = new Node\Expr\MethodCall(
            var: $this->node,
            name: new Node\Identifier('buildAuthenticatedByPassword'),
            args: [
                new Node\Arg(
                    new Node\Scalar\String_($faker->regexify('\d{1,2}_[0-9a-f]{48}')),
                ),
                new Node\Arg(
                    new Node\Scalar\String_($faker->regexify('[0-9a-z]{64}')),
                ),
                new Node\Arg(
                    new Node\Scalar\String_($faker->regexify('[0-9a-z/-]{48}')),
                ),
                new Node\Arg(
                    new Node\Scalar\String_($faker->regexify('[0-9a-z/-]{128}')),
                ),
            ],
        );

        return $this;
    }

    public function getNode(): Node\Expr
    {
        return $this->node;
    }
}
