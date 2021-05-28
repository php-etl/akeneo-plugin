<?php declare(strict_types=1);

namespace functional\Kiboko\Plugin\Akeneo\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final class File extends ExpressionFunction
{
    public function __construct(string $name)
    {
        parent::__construct(
            $name,
            fn ($value) => sprintf('file_get_contents(%s)', $value),
            fn ($value) => file_get_contents($value),
        );
    }
}
