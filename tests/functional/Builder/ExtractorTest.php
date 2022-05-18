<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\Builder;

use functional\Kiboko\Plugin\Akeneo\Mock;
use functional\Kiboko\Plugin\Akeneo\PipelineRunner;
use Kiboko\Component\PHPUnitExtension\Assert\ExtractorBuilderAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use Kiboko\Plugin\Akeneo\Builder\Capacity;
use Kiboko\Plugin\Akeneo\Builder\Extractor;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class ExtractorTest extends TestCase
{
    use ExtractorBuilderAssertTrait;

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

    protected function getBuilderCompilePath(): string
    {
        return $this->fs->url();
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }

    public function testAllProducts(): void
    {
        $httpClient = new Mock\HttpClientBuilder(new Mock\ResponseFactoryBuilder());

        $httpClient
            ->expectResponse(
                new Mock\RequestMatcher\RequestMatcherBuilder('/product', methods: ['GET']),
                new Mock\ResponseBuilder(status: 200)
            );

        $client = new Mock\ApiClientMockBuilder(withEnterpriseSupport: false);
        $client
            ->withHttpClient($httpClient)
            ->withRequestFactory(new Mock\RequestFactoryBuilder())
            ->withStreamFactory(new Mock\StreamFactoryBuilder())
            ->withFileSystem(new Mock\FileSystemBuilder())
            ->withAuthenticatedByPassword();

        $capacity = new Capacity\Extractor\All();
        $capacity->withEndpoint(new Node\Identifier('getProductApi'));
        $capacity->withType('product');

        $builder = new Extractor($capacity);
        $builder->withClient($client->getNode());

        $this->assertBuildsExtractorExtractsLike(
            [
                [],
                [],
                [],
            ],
            $builder,
        );
    }
}
