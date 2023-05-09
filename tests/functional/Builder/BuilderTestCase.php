<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder;

use functional\Kiboko\Plugin\Akeneo\PipelineRunner;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
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
        $this->fs = vfsStream::setup('root');
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    protected function getBuilderCompilePath(): string
    {
        return $this->fs->url();
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }

    protected function assertNodeIsInstanceOf(string $expected, DefaultBuilder $builder, string $message = '')
    {
        $printer = new PrettyPrinter\Standard();

        try {
            $filename = sha1(random_bytes(128)) .'.php';
            $file = new vfsStreamFile($filename);
            $file->setContent($printer->prettyPrintFile([
                new Node\Stmt\Return_($builder->getNode()),
            ]));
            $this->fs->addChild($file);

            $actual = include vfsStream::url('root/'.$filename);
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
            $file = new vfsStreamFile($filename);
            $file->setContent($printer->prettyPrintFile([
                new Node\Stmt\Return_($builder->getNode()),
            ]));
            $this->fs->addChild($file);

            $actual = include vfsStream::url('root/'.$filename);
        } catch (\ParseError $exception) {
            echo $printer->prettyPrintFile([$builder->getNode()]);
            $this->fail($exception->getMessage());
        }

        $this->assertNotInstanceOf($expected, $actual, $message);
    }
}
