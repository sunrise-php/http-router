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

use Fig\Http\Message\StatusCodeInterface;
use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiConfigurationAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class EmptyResponseOperationEnricher extends AbstractResponseOperationEnricher implements
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
        ReflectionClass|ReflectionMethod $requestHandler,
        array &$operation,
    ): void {
        if (! $requestHandler instanceof ReflectionMethod) {
            return;
        }

        $responseBodyType = TypeFactory::fromPhpTypeReflection($requestHandler->getReturnType());
        if (!$responseBodyType->is(Type::PHP_TYPE_NAME_VOID)) {
            return;
        }

        $responseStatusCode = $this->getResponseStatusCode($requestHandler)
            ?? StatusCodeInterface::STATUS_NO_CONTENT;

        $operation['responses'][$responseStatusCode] = [
            'description' => $this->openApiConfiguration->successfulResponseDescription,
        ];

        $this->enrichResponseWithHeaders($requestHandler, $operation['responses'][$responseStatusCode]);
    }

    public function getWeight(): int
    {
        return 10;
    }
}
