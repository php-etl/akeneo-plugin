<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder\Capacity\Extractor;

use functional\Kiboko\Plugin\Akeneo\Builder\BuilderTestCase;
use Kiboko\Plugin\Akeneo\Builder\Capacity\Extractor\ListPerPage;
use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

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

        $this->assertInstanceOf(Node\Stmt\Foreach_::class, $capacity->getNode());
    }
}
