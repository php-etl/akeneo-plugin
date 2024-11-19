<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Capacity\Loader;

use Kiboko\Plugin\Akeneo;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function Kiboko\Component\SatelliteToolbox\Configuration\compileValueWhenExpression;

final class Create implements Akeneo\Capacity\CapacityInterface
{
    private static array $endpoints = [
        'productMediaFile',
    ];

    public function __construct(private readonly ExpressionLanguage $interpreter)
    {
    }

    public function applies(array $config): bool
    {
        return isset($config['type'])
            && \in_array($config['type'], self::$endpoints)
            && isset($config['method'])
            && 'create' === $config['method'];
    }

    public function getBuilder(array $config): Builder
    {
        return (new Akeneo\Builder\Capacity\Loader\Create())
            ->withEndpoint(endpoint: new Node\Identifier(\sprintf('get%sApi', ucfirst((string) $config['type']))))
            ->withCode(code: compileValueWhenExpression($this->interpreter, $config['code'], 'line'))
            ->withData(line: new Node\Expr\Variable('line["product"]'))
        ;
    }
}
