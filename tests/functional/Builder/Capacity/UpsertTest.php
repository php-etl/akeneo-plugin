<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\Flow\Akeneo\Builder\Capacity;

use functional\Kiboko\Component\ETL\Flow\Akeneo\Builder\BuilderTestCase;
use Kiboko\Component\ETL\Flow\Akeneo\Builder\Capacity\Upsert;
use Kiboko\Component\ETL\Flow\Akeneo\MissingEndpointException;
use Kiboko\Component\ETL\Flow\Akeneo\MissingParameterException;
use PhpParser\Node;

final class UpsertTest extends BuilderTestCase
{
    public function testWithoutEndpoint()
    {
        $capacity = new Upsert();

        $capacity->withCode(new Node\Scalar\String_('foo'));
        $capacity->withData(new Node\Expr\Array_());

        $this->expectException(MissingEndpointException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have selected an endpoint.');

        $capacity->getNode();
    }

    public function testWithoutCode()
    {
        $capacity = new Upsert();

        $capacity->withEndpoint(new Node\Identifier('foo'));
        $capacity->withData(new Node\Expr\Array_());

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have provided a code.');

        $capacity->getNode();
    }

    public function testWithoutData()
    {
        $capacity = new Upsert();

        $capacity->withEndpoint(new Node\Identifier('foo'));
        $capacity->withCode(new Node\Scalar\String_('foo'));

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have provided some data.');

        $capacity->getNode();
    }

    public function testWithEndpoint()
    {
        $capacity = new Upsert();

        $capacity->withEndpoint(new Node\Identifier('foo'));
        $capacity->withCode(new Node\Scalar\String_('foo'));
        $capacity->withData(new Node\Expr\Array_());

        $this->assertInstanceOf(Node\Stmt\While_::class, $capacity->getNode());
    }
}
