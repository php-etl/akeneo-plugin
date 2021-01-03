<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\Flow\Akeneo\Builder;

use PhpParser\Node;
use PhpParser\PrettyPrinter;
use PhpParser\Builder as DefaultBuilder;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;
use Vfs\Node\File;

abstract class BuilderTestCase extends TestCase
{
    private ?FileSystem $fs = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;
    }

    protected function assertNodeIsInstanceOf(string $expected, DefaultBuilder $builder, string $message = '')
    {
        $printer = new PrettyPrinter\Standard();

        try {
            $filename = sha1(random_bytes(128)) .'.php';

            $this->fs->get('/')->add($filename, new File($printer->prettyPrintFile([
                new Node\Stmt\Return_($builder->getNode()),
            ])));

            $actual = include 'vfs://'.$filename;
        } catch (\ParseError $exception) {
            echo $printer->prettyPrintFile([$builder->getNode()]);
            $this->fail($exception->getMessage());
        }

        $this->assertInstanceOf($expected, $actual, $message);
    }

    protected function assertNodeIsNotInstanceOf(string $expected, DefaultBuilder $builder, string $message = '')
    {
        $printer = new PrettyPrinter\Standard();

        try {
            $filename = sha1(random_bytes(128)) .'.php';

            $this->fs->get('/')->add($filename, new File($printer->prettyPrintFile([
                new Node\Stmt\Return_($builder->getNode()),
            ])));

            $actual = include 'vfs://'.$filename;
        } catch (\ParseError $exception) {
            echo $printer->prettyPrintFile([$builder->getNode()]);
            $this->fail($exception->getMessage());
        }

        $this->assertNotInstanceOf($expected, $actual, $message);
    }
}
