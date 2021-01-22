<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Symfony\Component\Config;

final class Search implements Config\Definition\ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new Config\Definition\Builder\TreeBuilder('search');

        /** @phpstan-ignore-next-line */
        return $builder->getRootNode()
            ->arrayPrototype()
                ->children()
                    ->scalarNode('field')->cannotBeEmpty()->isRequired()->end()
                    ->scalarNode('operator')->cannotBeEmpty()->isRequired()->end()
                    ->variableNode('value')->cannotBeEmpty()->isRequired()->end()
                    ->scalarNode('scope')->cannotBeEmpty()->end()
                    ->scalarNode('locale')->cannotBeEmpty()->end()
                ->end()
            ->end();
    }
}
