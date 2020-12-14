<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Factory;

use Kiboko\Contract\ETL\Configurator\FactoryInterface;
use PhpParser\Node;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Search implements FactoryInterface
{
    public function configuration(): ConfigurationInterface
    {
        // TODO: Implement configuration() method.
    }

    public function normalize(array $config): array
    {
        // TODO: Implement normalize() method.
    }

    public function validate(array $config): bool
    {
        // TODO: Implement validate() method.
    }

    private function compileArgs(array $config): array
    {
        $args = [
            new Node\Arg(
                new Node\Scalar\String_($config['field']),
            ),
            new Node\Arg(
                new Node\Scalar\String_($config['operator']),
            ),
        ];

        $options = [];
        if (isset($config['scope'])) {
            $options[] = new Node\Expr\ArrayItem(
                new Node\Scalar\String_($config['scope']),
                new Node\Scalar\String_('scope'),
            );
        }
        if (isset($config['locale'])) {
            $options[] = new Node\Expr\ArrayItem(
                new Node\Scalar\String_($config['locale']),
                new Node\Scalar\String_('locale'),
            );
        }

        if (isset($config['value'])) {
            $args[] = new Node\Arg(
                new Node\Scalar\String_($config['value']),
            );
            if (count($options) > 0) {
                $args[] = new Node\Expr\Array_($options, ['kind' => Node\Expr\Array_::KIND_SHORT]);
            }
        } else if (count($options) > 0) {
            $args[] = new Node\Arg(
                new Node\Expr\ConstFetch(
                    new Node\Name('null'),
                ),
            );
            $args[] = new Node\Expr\Array_(
                $options,
                [
                    'kind' => Node\Expr\Array_::KIND_SHORT
                ]
            );
        }

        return $args;
    }

    public function compile(array $config): array
    {
        $instance = new Node\Expr\New_(
            new Node\Name\FullyQualified('Akeneo\\Pim\\ApiClient\\Search\\SearchBuilder')
        );

        foreach ($config as $field) {
            $instance = new Node\Expr\MethodCall(
                $instance,
                new Node\Identifier('addFilter'),
                $this->compileArgs($field),
            );
        }

        return [
            new Node\Expr\MethodCall(
                $instance,
                new Node\Identifier('getFilters')
            ),
        ];
    }
}
