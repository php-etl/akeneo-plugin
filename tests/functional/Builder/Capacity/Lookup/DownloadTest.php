<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup\Download;
use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Node;

final class DownloadTest extends BuilderTestCase
{
    public function testWithoutEndpoint()
    {
        $capacity = new Download();

        $this->expectException(MissingEndpointException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have selected an endpoint.');

        $capacity->getNode();
    }

    public function testWithEndpoint()
    {
        $capacity = new Download();
        $capacity->withEndpoint(new Node\Scalar\String_('foo'));

        $this->assertInstanceOf(Node\Stmt\Expression::class, $capacity->getNode());
    }
}
