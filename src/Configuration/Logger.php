<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Configuration;

use Symfony\Component\Config;

final class Logger implements Config\Definition\ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new Config\Definition\Builder\TreeBuilder('logger');

        $builder->getRootNode()
            ->children()
                ->enumNode('type')->values(['null', 'stderr'])->end()
            ->end();
        return $builder;
    }
}
