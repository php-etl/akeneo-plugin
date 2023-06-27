<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder\Capacity\Loader;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use Kiboko\Plugin\Akeneo\Builder\Capacity\Loader\UpsertList;
use Kiboko\Plugin\Akeneo\MissingEndpointException;
use Kiboko\Plugin\Akeneo\MissingParameterException;
use PhpParser\Node;

final class UpsertListTest extends BuilderTestCase
{
    public function testWithoutEndpoint()
    {
        $capacity = new UpsertList();

        $capacity->withData(new Node\Expr\Array_());

        $this->expectException(MissingEndpointException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have selected an endpoint.');

        $capacity->getNode();
    }

    public function testWithoutData()
    {
        $capacity = new UpsertList();

        $capacity->withEndpoint(new Node\Identifier('foo'));

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have provided some data.');

        $capacity->getNode();
    }

    public function testWithEndpoint()
    {
        $capacity = new UpsertList();

        $capacity->withEndpoint(new Node\Identifier('foo'));
        $capacity->withData(new Node\Expr\Array_());

        $this->assertInstanceOf(Node\Stmt\While_::class, $capacity->getNode());
    }
}
