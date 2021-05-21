<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Lookup;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class All implements Builder
{
    private null|Node\Expr|Node\Identifier $endpoint;
    private null|Node\Expr $search;
    private null|string|Expression $code;

    public function __construct(private ExpressionLanguage $interpreter)
    {
        $this->endpoint = null;
        $this->search = null;
        $this->code = null;
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

    public function withCode(string|Expression $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getNode(): Node
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, null);

        if ($this->endpoint === null) {
            throw new MissingEndpointException(
                message: 'Please check your capacity builder, you should have selected an endpoint.'
            );
        }

        return new Node\Expr\MethodCall(
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
                    (function () use ($parser) {
                        if (is_string($this->code)) {
                            return new Node\Arg(
                                value: new Node\Scalar\String_($this->code),
                                name: new Node\Identifier('attributeCode'),
                            );
                        }
                        if ($this->code instanceof Expression) {
                            return new Node\Arg(
                                value: $parser->parse('<?php ' . $this->interpreter->compile($this->code, ['input']) . ';')[0]->expr,
                                name: new Node\Identifier('attributeCode'),
                            );
                        }
                        return null;
                    })(),
                ],
            ),
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
