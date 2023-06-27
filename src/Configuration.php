<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo;

use Kiboko\Contract\Configurator\PluginConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class Configuration implements PluginConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $client = new Configuration\Client();
        $extractor = new Configuration\Extractor();
        $lookup = new Configuration\Lookup();
        $loader = new Configuration\Loader();

        $builder = new TreeBuilder('akeneo');

        /* @phpstan-ignore-next-line */
        $builder->getRootNode()
            ->validate()
                ->ifTrue(fn (array $value) => \array_key_exists('extractor', $value) && \array_key_exists('loader', $value))
                    ->thenInvalid('Your configuration should either contain the "extractor" or the "loader" key, not both.')
                ->end()
                ->children()
                    ->booleanNode('enterprise')
                        ->setDeprecated('php-etl/akeneo-plugin', '0.7', '"enterprise" key is no longer needed as both Community and Enterprise use the same API now.')
                        ->defaultFalse()
                    ->end()
                    ->arrayNode('expression_language')
                        ->scalarPrototype()
                    ->end()
                ->end()
                ->append(node: $extractor->getConfigTreeBuilder()->getRootNode())
                ->append(node: $lookup->getConfigTreeBuilder()->getRootNode())
                ->append(node: $loader->getConfigTreeBuilder()->getRootNode())
                ->append(node: $client->getConfigTreeBuilder()->getRootNode())
                ->variableNode('logger')
                    ->setDeprecated('php-etl/akeneo-plugin', '0.1')
                ->end()
            ->end()
        ;

        return $builder;
    }
}
