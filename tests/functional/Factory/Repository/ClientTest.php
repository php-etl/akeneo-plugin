<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Factory\Repository;

use Kiboko\Contract\Configurator\RepositoryInterface;
use Kiboko\Contract\Packaging\FileInterface;
use Kiboko\Plugin\Akeneo\Builder;
use Kiboko\Plugin\Akeneo\Factory\Repository;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function fileMock(string $filename): FileInterface
    {
        $file = $this->createMock(FileInterface::class);

        $file->method('getPath')
            ->willReturn($filename);

        $file->method('asResource')
            ->willReturn(fopen('php://temp', 'w+'));

        return $file;
    }

    public function testMergeWithPackages(): void
    {
        $builder = new Builder\Client(
            new Node\Scalar\String_(''),
            new Node\Scalar\String_(''),
            new Node\Scalar\String_(''),
        );

        $child = $this->createMock(RepositoryInterface::class);

        $child->method('getFiles')->willReturn([]);
        $child->method('getPackages')->willReturn(['baz/baz']);

        $repository = new Repository\Client($builder);

        $repository->addPackages('foo/foo', 'bar/bar:^1.0');

        $repository->merge($child);

        $this->assertCount(3, $repository->getPackages());
    }

    public function testMergeWithFiles(): void
    {
        $builder = new Builder\Client(
            new Node\Scalar\String_(''),
            new Node\Scalar\String_(''),
            new Node\Scalar\String_(''),
        );

        $child = $this->createMock(RepositoryInterface::class);

        $child->method('getFiles')->willReturn([
            $this->fileMock('baz.php')
        ]);
        $child->method('getPackages')->willReturn([]);

        $repository = new Repository\Client($builder);

        $repository->addFiles(
            $this->fileMock('foo.php'),
            $this->fileMock('bar.php'),
        );

        $repository->merge($child);

        $this->assertCount(3, $repository->getFiles());
    }
}
