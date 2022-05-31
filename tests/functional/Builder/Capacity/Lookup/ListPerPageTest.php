<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup\ListPerPage;
use Kiboko\Plugin\Akeneo\MissingEndpointException;

final class ListPerPageTest extends BuilderTestCase
{
    public function testWithoutEndpoint()
    {
        $capacity = new ListPerPage();

        $this->expectException(MissingEndpointException::class);
        $this->expectExceptionMessage('Please check your capacity builder, you should have selected an endpoint.');

        $capacity->getNode();
    }
}
