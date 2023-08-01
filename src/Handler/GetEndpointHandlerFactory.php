<?php

declare(strict_types=1);

namespace Kiboko\Plugin\Akeneo\Handler;

use Kiboko\Plugin\Akeneo\DTO\GetAssetManager;
use Kiboko\Plugin\Akeneo\DTO\GetAttributeOption;
use Kiboko\Plugin\Akeneo\DTO\GetDefaultEndpoint;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function Kiboko\Component\SatelliteToolbox\Configuration\compileValueWhenExpression;

final readonly class GetEndpointHandlerFactory implements EndpointHandlerFactoryInterface
{
    public function __construct(
        private ExpressionLanguage $interpreter,
    ) {
    }

    public function create(string $endpointType, array $config): EndpointArgumentHandlerInterface
    {
        return match ($endpointType) {
            'attributeOption' => new GetAttributeOptionHandler(
                new GetAttributeOption(
                    compileValueWhenExpression($this->interpreter, $config['attribute_code']),
                    compileValueWhenExpression($this->interpreter, $config['code']),
                ),
            ),
            'assetManager' => new GetAssetManagerHandler(
                new GetAssetManager(
                    compileValueWhenExpression($this->interpreter, $config['asset_family_code']),
                    compileValueWhenExpression($this->interpreter, $config['asset_code']),
                ),
            ),
            default => new GetDefaultEndpointHandler(
                new GetDefaultEndpoint(
                    compileValueWhenExpression($this->interpreter, $config['asset_family_code'])
                ),
            ),
        };
    }
}
