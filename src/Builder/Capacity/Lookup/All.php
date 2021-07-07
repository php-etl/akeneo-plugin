<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class All implements Builder
{
    private null|Node\Expr|Node\Identifier $endpoint;
    private null|Node\Expr $search;
    private null|Node\Expr $code;
    private ?string $type;

    public function __construct()
    {
        $this->endpoint = null;
        $this->search = null;
        $this->code = null;
        $this->type = null;
    }

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

    public function withType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getNode(): Node
    {
        if ($this->endpoint === null) {
            throw new MissingEndpointException(
                message: 'Please check your capacity builder, you should have selected an endpoint.'
            );
        }

        return new Node\Stmt\Expression(
            expr: new Node\Expr\Assign(
                var: new Node\Expr\Variable('lookup'),
                expr:  new Node\Expr\MethodCall(
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
                            $this->code !== null ? new Node\Arg(
                                value: $this->code,
                                name: new Node\Identifier($this->type === 'familyVariant' ? 'familyCode' : 'attributeCode'),
                            ) : null
                        ],
                    ),
                )
            )
        );
    }

    private function compileSearch(): array
    {
        if ($this->search === null) {
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
