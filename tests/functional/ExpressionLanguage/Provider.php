<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class Provider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new Env('env'),
            new File('file'),
        ];
    }
}
