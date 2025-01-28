<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\OpenApi;

use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\OpenApi\OperationEnricher\EmptyResponseOperationEnricher;
use Sunrise\Http\Router\OpenApi\OperationEnricher\EncodableResponseOperationEnricher;
use Sunrise\Http\Router\OpenApi\OperationEnricher\RequestBodyOperationEnricher;
use Sunrise\Http\Router\OpenApi\OperationEnricher\RequestCookieOperationEnricher;
use Sunrise\Http\Router\OpenApi\OperationEnricher\RequestHeaderOperationEnricher;
use Sunrise\Http\Router\OpenApi\OperationEnricher\RequestStreamOperationEnricher;
use Sunrise\Http\Router\OpenApi\OperationEnricher\RouteVariablesOperationEnricher;
use Sunrise\Http\Router\OpenApi\OperationEnricher\UnsuccessfulResponseOperationEnricher;
use Sunrise\Http\Router\RouteInterface;

use function usort;

/**
 * @since 3.0.0
 */
final class OpenApiOperationEnricherManager implements OpenApiOperationEnricherManagerInterface
{
    /**
     * @var array<array-key, OpenApiOperationEnricherInterface>
     */
    private array $operationEnrichers;

    private bool $isOperationEnrichersSorted = false;

    /**
     * @param array<array-key, OpenApiOperationEnricherInterface> $operationEnrichers
     */
    public function __construct(
        private readonly OpenApiConfiguration $openApiConfiguration,
        private readonly OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager,
        array $operationEnrichers = [],
    ) {
        $this->setOperationEnrichers(self::getDefaultOperationEnrichers());
        $this->setOperationEnrichers($operationEnrichers);
    }

    /**
     * @inheritDoc
     */
    public function enrichOperation(
        RouteInterface $route,
        ReflectionMethod|ReflectionClass $requestHandler,
        array &$operation,
    ): void {
        $this->isOperationEnrichersSorted or $this->sortOperationEnrichers();

        foreach ($this->operationEnrichers as $operationEnricher) {
            $operationEnricher->enrichOperation($route, $requestHandler, $operation);
        }
    }

    /**
     * @param array<array-key, OpenApiOperationEnricherInterface> $operationEnrichers
     */
    private function setOperationEnrichers(array $operationEnrichers): void
    {
        foreach ($operationEnrichers as $operationEnricher) {
            $this->operationEnrichers[] = $operationEnricher;

            if ($operationEnricher instanceof OpenApiConfigurationAwareInterface) {
                $operationEnricher->setOpenApiConfiguration($this->openApiConfiguration);
            }
            if ($operationEnricher instanceof OpenApiPhpTypeSchemaResolverManagerAwareInterface) {
                $operationEnricher->setOpenApiPhpTypeSchemaResolverManager($this->openApiPhpTypeSchemaResolverManager);
            }
        }
    }

    private function sortOperationEnrichers(): void
    {
        $this->isOperationEnrichersSorted = usort($this->operationEnrichers, static fn(
            OpenApiOperationEnricherInterface $a,
            OpenApiOperationEnricherInterface $b,
        ): int => $b->getWeight() <=> $a->getWeight());
    }

    /**
     * @return array<array-key, OpenApiOperationEnricherInterface>
     */
    private static function getDefaultOperationEnrichers(): array
    {
        return [
            new RouteVariablesOperationEnricher(),
            new RequestCookieOperationEnricher(),
            new RequestHeaderOperationEnricher(),
            new RequestBodyOperationEnricher(),
            new RequestStreamOperationEnricher(),
            new EmptyResponseOperationEnricher(),
            new EncodableResponseOperationEnricher(),
            new UnsuccessfulResponseOperationEnricher(),
        ];
    }
}
