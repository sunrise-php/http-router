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
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiConfigurationAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class UnsuccessfulResponseOperationEnricher implements
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

        if ($this->openApiConfiguration->unsuccessfulResponseViewName === null) {
            return;
        }

        $operation['responses']['default'] = [
            'description' => $this->openApiConfiguration->unsuccessfulResponseDescription,
        ];

        $responseBodyType = new Type($this->openApiConfiguration->unsuccessfulResponseViewName, allowsNull: false);
        $responseBodySchema = $this->openApiPhpTypeSchemaResolverManager
            ->resolvePhpTypeSchema($responseBodyType, $requestHandler);

        foreach ($route->getProducedMediaTypes() as $producedMediaType) {
            $operation['responses']['default']['content'][$producedMediaType->getIdentifier()] = [
                'schema' => $responseBodySchema,
            ];
        }
    }

    public function getWeight(): int
    {
        return 0;
    }
}
