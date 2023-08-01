<?php declare(strict_types=1);

namespace functional\Builder\Capacity\Extractor;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use Kiboko\Plugin\Akeneo\Builder\Capacity\Extractor\Get;
use Kiboko\Plugin\Akeneo\DTO\GetDefaultEndpoint;
use Kiboko\Plugin\Akeneo\Handler\GetDefaultEndpointHandler;
use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Node;

final class GetTest extends BuilderTestCase
{
    public function testWithoutEndpoint()
    {
        $capacity = new Get(
            new GetDefaultEndpointHandler(
                new GetDefaultEndpoint(
                    new Node\Scalar\String_('foo'),
                ),
            ),
        );

        $this->expectException(MissingEndpointException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have selected an endpoint.');

        $capacity->getNode();
    }

    public function testWithoutIdentifier()
    {
        $capacity = new Get(
            new GetDefaultEndpointHandler(
                new GetDefaultEndpoint(
                    new Node\Scalar\String_('foo'),
                ),
            ),
        );

        $capacity->withEndpoint(new Node\Identifier('foo'));

        $this->expectException(\TypeError::class);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $capacity->getNode());
    }

    public function testWithEndpoint()
    {
        $capacity = new Get(
            new GetDefaultEndpointHandler(
                new GetDefaultEndpoint(
                    new Node\Scalar\String_('foo'),
                ),
            ),
        );

        $capacity->withEndpoint(new Node\Identifier('foo'));

        $this->assertInstanceOf(Node\Stmt\Expression::class, $capacity->getNode());
    }
}
