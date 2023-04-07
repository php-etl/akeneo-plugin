<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Symfony\Component\Config;

use function Kiboko\Component\SatelliteToolbox\Configuration\asExpression;
use function Kiboko\Component\SatelliteToolbox\Configuration\isExpression;

final class Search implements Config\Definition\ConfigurationInterface
{
    public function getConfigTreeBuilder(): Config\Definition\Builder\TreeBuilder
    {
        $builder = new Config\Definition\Builder\TreeBuilder('search');

        /* @phpstan-ignore-next-line */
        $builder->getRootNode()
            ->arrayPrototype()
                ->children()
                    ->scalarNode('field')->cannotBeEmpty()->isRequired()->end()
                    ->scalarNode('operator')->cannotBeEmpty()->isRequired()->end()
                    ->variableNode('value')
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(isExpression())
                            ->then(asExpression())
                        ->end()
                    ->end()
                    ->scalarNode('scope')
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(isExpression())
                            ->then(asExpression())
                        ->end()
                    ->end()
                    ->scalarNode('locale')
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(isExpression())
                            ->then(asExpression())
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
