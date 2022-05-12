<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class Get implements Builder
{
    private null|Node\Expr|Node\Identifier $endpoint;
    private null|Node\Expr $identifier;
    private null|Node\Expr $code;
    private null|string $type;

    public function __construct()
    {
        $this->endpoint = null;
        $this->identifier = null;
        $this->code = null;
        $this->type = null;
    }

    public function withEndpoint(Node\Expr|Node\Identifier $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function withCode(Node\Expr $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function withIdentifier(Node\Expr $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function withType(string $type): self
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
                    name: new Node\Identifier('get'),
                    args: array_filter(
                        [
                            new Node\Arg(
                                value: $this->identifier,
                                name: $this->compileIdentifierNamedArgument($this->type),
                            ),
                            $this->code !== null ? new Node\Arg(
                                value: $this->code,
                                name: $this->compileCodeNamedArgument($this->type),
                            ) : null
                        ],
                    ),
                )
            )
        );
    }

    private function compileCodeNamedArgument(string $type): Node\Identifier
    {
        return match ($type) {
            'referenceEntityRecord' => new Node\Identifier('referenceEntityCode'),
            'assetManager' => new Node\Identifier('assetFamilyCode'),
            'referenceEntityRecord' => new Node\Identifier('recordCode'),
            default => new Node\Identifier('attributeCode')
        };
    }

    private function compileIdentifierNamedArgument(string $type): Node\Identifier
    {
        return match ($type) {
            'referenceEntityRecord' => new Node\Identifier('recordCode'),
            'assetManager' => new Node\Identifier('assetCode'),
            default => new Node\Identifier('code')
        };
    }
}
