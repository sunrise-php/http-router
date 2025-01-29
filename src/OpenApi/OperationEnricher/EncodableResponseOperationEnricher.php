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

use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiConfigurationAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class EncodableResponseOperationEnricher extends AbstractResponseOperationEnricher implements
    OpenApiOperationEnricherInterface,
    OpenApiConfigurationAwareInterface,
    OpenApiPhpTypeSchemaResolverManagerAwareInterface
{
    private readonly OpenApiConfiguration $openApiConfiguration;
    private readonly OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager;

    public function setOpenApiConfiguration(OpenApiConfiguration $openApiConfiguration): void
    {
        $this->openApiConfiguration = $openApiConfiguration;
    }

    public function setOpenApiPhpTypeSchemaResolverManager(
        OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager,
    ): void {
        $this->openApiPhpTypeSchemaResolverManager = $openApiPhpTypeSchemaResolverManager;
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

        if ($requestHandler->getAttributes(EncodableResponse::class) === []) {
            return;
        }

        $responseStatusCode = $this->getResponseStatusCode($requestHandler)
            ?? $this->openApiConfiguration->successfulResponseStatusCode;

        $operation['responses'][$responseStatusCode] = [
            'description' => $this->openApiConfiguration->successfulResponseDescription,
        ];

        $this->enrichResponseWithHeaders($requestHandler, $operation['responses'][$responseStatusCode]);

        $responseBodyType = TypeFactory::fromPhpTypeReflection($requestHandler->getReturnType());
        $responseBodySchema = $this->openApiPhpTypeSchemaResolverManager
            ->resolvePhpTypeSchema($responseBodyType, $requestHandler);

        foreach ($route->getProducedMediaTypes() as $producedMediaType) {
            $operation['responses'][$responseStatusCode]['content'][$producedMediaType->getIdentifier()] = [
                'schema' => $responseBodySchema,
            ];
        }
    }

    public function getWeight(): int
    {
        return 10;
    }
}
