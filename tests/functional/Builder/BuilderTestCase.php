<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PhpParser\Node;
use PhpParser\PrettyPrinter;
use PhpParser\Builder as DefaultBuilder;
use PHPUnit\Framework\TestCase;

abstract class BuilderTestCase extends TestCase
{
    private ?vfsStreamDirectory $fs = null;

    protected function setUp(): void
    {
        $this->fs = vfsStream::setup();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
        vfsStreamWrapper::unregister();
    }

    protected function assertNodeIsInstanceOf(string $expected, DefaultBuilder $builder, string $message = '')
    {
        $printer = new PrettyPrinter\Standard();

        try {
            $filename = sha1(random_bytes(128)) .'.php';
            file_put_contents($this->fs->url() . '/' . $filename, $printer->prettyPrintFile([
                new Node\Stmt\Return_($builder->getNode()),
            ]));

            $actual = include $this->fs->url().'/'.$filename;
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
            file_put_contents($this->fs->url() . '/' . $filename, $printer->prettyPrintFile([
                new Node\Stmt\Return_($builder->getNode()),
            ]));

            $actual = include $this->fs->url().'/'.$filename;
        } catch (\ParseError $exception) {
            echo $printer->prettyPrintFile([$builder->getNode()]);
            $this->fail($exception->getMessage());
        }

        $this->assertNotInstanceOf($expected, $actual, $message);
    }
}
