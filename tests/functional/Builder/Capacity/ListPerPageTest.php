<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\Flow\Akeneo\Builder\Capacity;

use functional\Kiboko\Component\ETL\Flow\Akeneo\Builder\BuilderTestCase;
use Kiboko\Component\ETL\Flow\Akeneo\Builder\Capacity\ListPerPage;
use Kiboko\Component\ETL\Flow\Akeneo\MissingEndpointException;
use PhpParser\Node;

final class ListPerPageTest extends BuilderTestCase
{
    public function testWithoutEndpoint()
    {
        $capacity = new ListPerPage();

        $this->expectException(MissingEndpointException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have selected an endpoint.');

        $capacity->getNode();
    }

    public function testWithEndpoint()
    {
        $capacity = new ListPerPage();

        $capacity->withEndpoint(new Node\Identifier('foo'));

        $this->assertInstanceOf(Node\Stmt\Expression::class, $capacity->getNode());
    }
}
