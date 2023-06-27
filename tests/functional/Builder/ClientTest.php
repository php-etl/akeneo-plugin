<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder;

use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use functional\Kiboko\Plugin\Akeneo\Mock\ResponseFactory;
use Http\Mock\Client;
use Kiboko\Plugin\Akeneo\Builder;
use Kiboko\Plugin\Akeneo\MissingAuthenticationMethodException;
use PhpParser\Node;

final class ClientTest extends BuilderTestCase
{
    private function getClientNode(): Node\Expr
    {
        return new Node\Expr\New_(
            class: new Node\Name\FullyQualified(Client::class),
            args: [
                new Node\Arg(
                    new Node\Expr\New_(
                        new Node\Name\FullyQualified(ResponseFactory::class)
                    ),
                ),
            ],
        );
    }

    public function testExpectingTokenOrPassword(): void
    {
        $client = new Builder\Client(
            new Node\Scalar\String_('http://demo.akeneo.com'),
            new Node\Scalar\String_(''),
            new Node\Scalar\String_(''),
        );

        $this->expectException(MissingAuthenticationMethodException::class);
        $this->expectExceptionMessage('Please check your client builder, you should either call withToken() or withPassword() methods.');

        $client->getNode();
    }

    public function testWithToken(): void
    {
        $client = new Builder\Client(
            new Node\Scalar\String_('http://demo.akeneo.com'),
            new Node\Scalar\String_(''),
            new Node\Scalar\String_(''),
        );

        $client->withToken(
            new Node\Scalar\String_(''),
            new Node\Scalar\String_(''),
        );

        $client->withHttpClient($this->getClientNode());

        $this->assertNodeIsInstanceOf(AkeneoPimClientInterface::class, $client);
    }
}
