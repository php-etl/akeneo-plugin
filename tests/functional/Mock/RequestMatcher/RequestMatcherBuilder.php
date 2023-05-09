<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Mock\RequestMatcher;

use PhpParser\Node;

final class RequestMatcherBuilder implements RequestMatcherBuilderInterface
{
    public function __construct(
        private readonly ?string $path = null,
        private readonly ?string $host = null,
        private $methods = [],
        private $schemes = []
    ) {}

    public function getNode(): Node
    {
        return new Node\Expr\New_(
            class: new Node\Name\FullyQualified(\Http\Message\RequestMatcher\RequestMatcher::class),
            args: [
                new Node\Arg(
                    value: $this->path !== null ? new Node\Scalar\String_($this->path) : new Node\Expr\ConstFetch(new Node\Name('null')),
                    name: new Node\Identifier('path'),
                ),
                new Node\Arg(
                    value: $this->host !== null ? new Node\Scalar\String_($this->host) : new Node\Expr\ConstFetch(new Node\Name('null')),
                    name: new Node\Identifier('host'),
                ),
                new Node\Arg(
                    value: new Node\Expr\Array_(
                        items: array_map(
                            fn (string $value): Node\Expr => new Node\Expr\ArrayItem(
                                new Node\Scalar\String_($value),
                            ),
                            $this->methods
                        ),
                        attributes: [
                            'kind' => Node\Expr\Array_::KIND_SHORT
                        ],
                    ),
                    name: new Node\Identifier('methods'),
                ),
                new Node\Arg(
                    value: new Node\Expr\Array_(
                        items: array_map(
                            fn (string $value): Node\Expr => new Node\Expr\ArrayItem(
                                new Node\Scalar\String_($value),
                            ),
                            $this->schemes
                        ),
                        attributes: [
                            'kind' => Node\Expr\Array_::KIND_SHORT
                        ],
                    ),
                    name: new Node\Identifier('schemes'),
                ),
            ],
        );
    }
}
