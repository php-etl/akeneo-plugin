<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Flow\Akeneo\Factory;

use Kiboko\Component\ETL\Flow\Akeneo\Builder;
use Kiboko\Contract\ETL\Configurator\FactoryInterface;
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

    public function compile(array $config): Builder\Search
    {
        $builder = new Builder\Search();

        foreach ($config as $field) {
            $builder->addFilter(...$field);
        }

        return $builder;
    }
}
