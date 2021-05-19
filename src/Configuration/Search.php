<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Symfony\Component\Config;
use Symfony\Component\ExpressionLanguage\Expression;

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
                    ->variableNode('value')
                        ->cannotBeEmpty()
                        ->isRequired()
                        ->validate()
                            ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                            ->then(fn ($data) => new Expression(substr($data, 2)))
                        ->end()
                    ->end()
                    ->scalarNode('scope')
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                            ->then(fn ($data) => new Expression(substr($data, 2)))
                        ->end()
                    ->end()
                    ->scalarNode('locale')
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@='))
                            ->then(fn ($data) => new Expression(substr($data, 2)))
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
