<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder\Capacity\Extractor;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use Kiboko\Plugin\Akeneo\Builder\Capacity\Extractor\All;
use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Node;

final class AllTest extends BuilderTestCase
{
    public function testWithoutEndpoint()
    {
        $capacity = new All();

        $this->expectException(MissingEndpointException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have selected an endpoint.');

        $capacity->withCode(new Node\Scalar\String_('foo'));

        $capacity->getNode();
    }
}
