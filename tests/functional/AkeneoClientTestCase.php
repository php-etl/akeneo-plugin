<?php

declare(strict_types=1);

namespace  functional\Kiboko\Plugin\Akeneo;

use functional\Kiboko\Plugin\Akeneo\Mock\AkeneoClientBuilder;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

abstract class AkeneoClientTestCase extends TestCase
{
    public function getAkeneoClientBuilder(string $httpClientClass)
    {
        $node = new Node\Expr\MethodCall(
            new Node\Expr\New_(new Node\Name\FullyQualified(AkeneoClientBuilder::class),
                args: [
                    new Node\Arg(new Node\Scalar\String_('localhost:8080')),
                    new Node\Arg(new Node\Expr\New_(new Node\Name\FullyQualified($httpClientClass)))
                ]),
            'buildAuthenticatedByPassword',
            [
                new Node\Arg(new Node\Scalar\String_('clientId')),
                new Node\Arg(new Node\Scalar\String_('secret')),
                new Node\Arg(new Node\Scalar\String_('user')),
                new Node\Arg(new Node\Scalar\String_('password'))
            ]
        );

        return $node;
    }
}
