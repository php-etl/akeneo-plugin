<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class All implements Builder
{
    private Node\Expr|Node\Identifier|null $endpoint = null;
    private ?Node\Expr $search = null;
    private ?Node\Expr $code = null;
    private string $type = '';

    public function withEndpoint(Node\Expr|Node\Identifier $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function withSearch(Node\Expr $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function withCode(Node\Expr $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function withType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNode(): Node
    {
        if (null === $this->endpoint) {
            throw new MissingEndpointException(message: 'Please check your capacity builder, you should have selected an endpoint.');
        }

        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                var: new Node\Expr\Variable('lookup'),
                expr: new Node\Expr\MethodCall(
                    var: new Node\Expr\MethodCall(
                        var: new Node\Expr\PropertyFetch(
                            var: new Node\Expr\Variable('this'),
                            name: new Node\Identifier('client')
                        ),
                        name: $this->endpoint
                    ),
                    name: new Node\Identifier('all'),
                    args: array_filter(
                        [
                            new Node\Arg(
                                value: new Node\Expr\Array_(
                                    items: $this->compileSearch(),
                                    attributes: [
                                        'kind' => Node\Expr\Array_::KIND_SHORT,
                                    ]
                                ),
                                name: new Node\Identifier('queryParameters'),
                            ),
                            null !== $this->code ? new Node\Arg(
                                value: $this->code,
                                name: $this->compileCodeNamedArgument($this->type),
                            ) : null,
                        ],
                    ),
                ),
            ),
        );
    }

    private function compileCodeNamedArgument(string $type): Node\Identifier
    {
        return match ($type) {
            'referenceEntityRecord' => new Node\Identifier('referenceEntityCode'),
            'assetManager' => new Node\Identifier('assetFamilyCode'),
            default => new Node\Identifier('attributeCode'),
        };
    }

    private function compileSearch(): array
    {
        if (null === $this->search) {
            return [];
        }

        return [
            new Node\Expr\ArrayItem(
                $this->search,
                new Node\Scalar\String_('search'),
            ),
        ];
    }
}
