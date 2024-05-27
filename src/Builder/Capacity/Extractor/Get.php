<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Extractor;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class Get implements Builder
{
    private Node\Expr|Node\Identifier|null $endpoint = null;
    private ?Node\Expr $identifier = null;

    public function withEndpoint(Node\Expr|Node\Identifier $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function withIdentifier(?Node\Expr $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getNode(): Node
    {
        if (null === $this->endpoint) {
            throw new MissingEndpointException(message: 'Please check your capacity builder, you should have selected an endpoint.');
        }

        return new Node\Stmt\Expression(
            expr: new Node\Expr\Yield_(
                value: new Node\Expr\New_(
                    class: new Node\Name\FullyQualified(name: \Kiboko\Component\Bucket\AcceptanceResultBucket::class),
                    args: [
                        new Node\Arg(
                            value: new Node\Expr\MethodCall(
                                var: new Node\Expr\MethodCall(
                                    var: new Node\Expr\PropertyFetch(
                                        var: new Node\Expr\Variable('this'),
                                        name: new Node\Identifier('client')
                                    ),
                                    name: $this->endpoint
                                ),
                                name: new Node\Identifier('get'),
                                args: array_filter(
                                    [
                                        new Node\Arg(
                                            value: $this->identifier,
                                            name: new Node\Identifier('code'),
                                        ),
                                    ],
                                ),
                            ),
                            unpack: true,
                        ),
                    ],
                ),
            ),
        );
    }
}
