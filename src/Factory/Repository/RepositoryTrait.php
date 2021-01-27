<?php declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Factory\Repository;

use Kiboko\Contract\Configurator;

trait RepositoryTrait
{
    /** @var Configurator\FileInterface[] */
    private array $files;
    /** @var string[] */
    private array $packages;

    public function addFiles(Configurator\FileInterface ...$files): Configurator\RepositoryInterface
    {
        array_push($this->files, ...$files);

        return $this;
    }

    /** @return iterable<Configurator\FileInterface> */
    public function getFiles(): iterable
    {
        return $this->files;
    }

    public function addPackages(string ...$packages): Configurator\RepositoryInterface
    {
        array_push($this->packages, ...$packages);

        return $this;
    }

    /** @return iterable<string> */
    public function getPackages(): iterable
    {
        return $this->packages;
    }
}
