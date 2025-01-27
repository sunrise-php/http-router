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

namespace Sunrise\Http\Router\OpenApi\OperationEnricher;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiConfigurationAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class EmptyResponseOperationEnricher implements
    OpenApiOperationEnricherInterface,
    OpenApiConfigurationAwareInterface
{
    private readonly OpenApiConfiguration $openApiConfiguration;

    public function setOpenApiConfiguration(OpenApiConfiguration $openApiConfiguration): void
    {
        $this->openApiConfiguration = $openApiConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function enrichOperation(
        RouteInterface $route,
        ReflectionMethod|ReflectionClass $requestHandler,
        array &$operation,
    ): void {
        if (! $requestHandler instanceof ReflectionMethod) {
            return;
        }

        if ((string) $requestHandler->getReturnType() !== Type::PHP_TYPE_NAME_VOID) {
            return;
        }

        $responseStatusCode = $this->openApiConfiguration->emptyResponseStatusCode;

        /** @var list<ReflectionAttribute<ResponseStatus>> $annotations */
        $annotations = $requestHandler->getAttributes(ResponseStatus::class);
        if (isset($annotations[0])) {
            $responseStatus = $annotations[0]->newInstance();
            $responseStatusCode = $responseStatus->code;
        }

        $operation['responses'][$responseStatusCode]['description'] = $this->openApiConfiguration
            ->successfulResponseDescription;
    }

    public function getWeight(): int
    {
        return 0;
    }
}
