<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Builder\Capacity\Extractor;

use Kiboko\Plugin\Akeneo\MissingEndpointException;
use PhpParser\Builder;
use PhpParser\Node;

final class All implements Builder
{
    private null|Node\Expr|Node\Identifier $endpoint = null;
    private null|Node\Expr $search = null;
    private null|Node\Expr $attributeCode = null;
    private null|Node\Expr $assetFamilyCode = null;
    private null|Node\Expr $file = null;
    private null|string $type = null;

    public function __construct()
    {
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

    public function withAttributeOption(?Node\Expr $attributeCode): self
    {
        $this->attributeCode = $attributeCode;

        return $this;
    }

    public function withAssetManager(?Node\Expr $assetFamilyCode): self
    {
        $this->assetFamilyCode = $assetFamilyCode;

        return $this;
    }

    public function withFile(Node\Expr $file): self
    {
        $this->file = $file;

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
                             null !== $this->attributeCode ? new Node\Arg(
                                 value: $this->attributeCode,
                                 name: $this->compileCodeNamedArgument($this->type),
                             ) : null,
                             null !== $this->assetFamilyCode ? new Node\Arg(
                                 value: $this->assetFamilyCode,
                                 name: $this->compileCodeNamedArgument($this->type),
                             ) : null,
                             null !== $this->file ? new Node\Arg(
                                 value: $this->file,
                                 name: $this->compileCodeNamedArgument($this->type),
                             ) : null,
                         ],
                     ),
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

    private function compileCodeNamedArgument(string $type): Node\Identifier
    {
        return match ($type) {
            'assetManager' => new Node\Identifier('assetFamilyCode'),
            'referenceEntityRecord' => new Node\Identifier('referenceEntityCode'),
            default => new Node\Identifier('attributeCode')
        };
    }
}
