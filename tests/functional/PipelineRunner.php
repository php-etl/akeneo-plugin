<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo;

use Kiboko\Contract\Bucket\AcceptanceResultBucketInterface;
use Kiboko\Contract\Bucket\RejectionResultBucketInterface;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use Kiboko\Contract\Pipeline\RejectionInterface;
use Kiboko\Contract\Pipeline\StateInterface;

final class PipelineRunner implements PipelineRunnerInterface
{
    public function run(
        \Iterator $source,
        \Generator $async,
        RejectionInterface $rejection,
        StateInterface $state,
    ): \Iterator {
        $state->initialize();
        $rejection->initialize();

        $source->rewind();
        $async->rewind();

        while ($source->valid() && $async->valid()) {
            $bucket = $async->send($source->current());

            if ($bucket instanceof RejectionResultBucketInterface) {
                foreach ($bucket->walkRejection() as $line) {
                    $rejection->reject($line);
                    $state->reject();
                }
            }
            if ($bucket instanceof AcceptanceResultBucketInterface) {
                yield from $bucket->walkAcceptance();
                $state->accept();
            }

            $source->next();
        }

        $rejection->teardown();
        $state->teardown();
    }
}
