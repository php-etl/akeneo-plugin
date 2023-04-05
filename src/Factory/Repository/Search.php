<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Factory\Repository;

use Kiboko\Contract\Configurator;
use Kiboko\Plugin\Akeneo;

final class Search implements Configurator\RepositoryInterface
{
    use RepositoryTrait;

    public function __construct(private readonly Akeneo\Builder\Search $builder)
    {
        $this->files = [];
        $this->packages = [];
    }

    public function getBuilder(): Akeneo\Builder\Search
    {
        return $this->builder;
    }

    public function merge(Configurator\RepositoryInterface $friend): self
    {
        array_push($this->files, ...$friend->getFiles());
        array_push($this->packages, ...$friend->getPackages());

        return $this;
    }
}
