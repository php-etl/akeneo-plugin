<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Extractor;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class All implements Builder
{
    private Node\Expr|Node\Identifier|null $endpoint = null;
    private ?Node\Expr $search = null;
    private ?Node\Expr $code = null;
    private ?Node\Expr $referenceEntity = null;
    private ?Node\Expr $referenceEntityAttributeCode = null;
    private ?Node\Expr $withEnrichedAttributes = null;

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

    public function withCode(?Node\Expr $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function withReferenceEntityCode(?Node\Expr $referenceEntity): self
    {
        $this->referenceEntity = $referenceEntity;

        return $this;
    }

    public function withReferenceEntityAttributeOption(?Node\Expr $referenceEntityAttrbuteCode): self
    {
        $this->referenceEntityAttributeCode = $referenceEntityAttrbuteCode;

        return $this;
    }

    public function withEnrichedAttributes(?Node\Expr $withEnrichedAttributes): self
    {
        $this->withEnrichedAttributes = $withEnrichedAttributes;

        return $this;
    }

    public function getNode(): Node
    {
        if (null === $this->endpoint) {
            throw new MissingEndpointException(message: 'Please check your capacity builder, you should have selected an endpoint.');
        }

        return
             new Node\Stmt\Foreach_(
                 expr: new Node\Expr\MethodCall(
                     var: new Node\Expr\MethodCall(
                         var: new Node\Expr\PropertyFetch(
                             var: new Node\Expr\Variable('this'),
                             name: new Node\Identifier('client')
                         ),
                         name: $this->endpoint
                     ),
                     name: new Node\Identifier('all'),
                     args: $this->compileArguments(),
                 ),
                 valueVar: new Node\Expr\Variable('item'),
                 subNodes: [
                     'stmts' => [
                         new Node\Stmt\Expression(
                             expr: new Node\Expr\Yield_(
                                 value: new Node\Expr\New_(
                                     class: new Node\Name\FullyQualified(name: \Kiboko\Component\Bucket\AcceptanceResultBucket::class),
                                     args: [
                                         new Node\Arg(
                                             new Node\Expr\Variable('item')
                                         ),
                                     ],
                                 ),
                             ),
                         ),
                     ],
                 ]
             );
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

    private function compileArguments(): array
    {
        $args = [];

        if (null !== $this->search) {
            $items = $this->compileSearch();
            if (null !== $this->withEnrichedAttributes) {
                $items = array_merge(
                    $this->compileSearch(),
                    [
                        new Node\Expr\ArrayItem(
                            $this->withEnrichedAttributes,
                            new Node\Scalar\String_('with_enriched_attributes'),
                        ),
                    ]
                );
            }
            $args[] = new Node\Arg(
                value: new Node\Expr\Array_(
                    items: $items,
                    attributes: [
                        'kind' => Node\Expr\Array_::KIND_SHORT,
                    ]
                ),
                name: new Node\Identifier('queryParameters'),
            );
        }

        if (null !== $this->code) {
            $args[] = new Node\Arg(
                value: $this->code,
                name: new Node\Identifier('attributeCode'),
            );
        }

        if (null !== $this->referenceEntity) {
            $args[] = new Node\Arg(
                value: $this->referenceEntity,
                name: new Node\Identifier('referenceEntityCode'),
            );
        }

        if (null !== $this->referenceEntityAttributeCode) {
            $args[] = new Node\Arg(
                value: $this->referenceEntityAttributeCode,
                name: new Node\Identifier('attributeCode'),
            );
        }

        if (null !== $this->withEnrichedAttributes && null === $this->search) {
            $args[] = new Node\Arg(
                value: new Node\Expr\Array_(
                    items: [
                        new Node\Expr\ArrayItem(
                            $this->withEnrichedAttributes,
                            new Node\Scalar\String_('with_enriched_attributes'),
                        ),
                    ],
                    attributes: [
                        'kind' => Node\Expr\Array_::KIND_SHORT,
                    ]
                ),
                name: new Node\Identifier('queryParameters'),
            );
        }

        return $args;
    }
}
