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
    private readonly OpenApiPhpTypeSchemaResolverManagerInterface $phpTypeSchemaResolverManager;

    public function setOpenApiConfiguration(OpenApiConfiguration $openApiConfiguration): void
    {
        $this->openApiConfiguration = $openApiConfiguration;
    }

    public function setOpenApiPhpTypeSchemaResolverManager(
        OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager,
    ): void {
        $this->phpTypeSchemaResolverManager = $openApiPhpTypeSchemaResolverManager;
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

        if ($this->openApiConfiguration->unsuccessfulResponseViewName === null) {
            return;
        }

        $operation['responses']['default']['description'] = $this->openApiConfiguration
            ->unsuccessfulResponseDescription;

        $responseType = new Type($this->openApiConfiguration->unsuccessfulResponseViewName, allowsNull: false);
        $responseSchema = $this->phpTypeSchemaResolverManager->resolvePhpTypeSchema($responseType, $requestHandler);

        foreach ($route->getProducedMediaTypes() as $mediaType) {
            $operation['responses']['default']['content'][$mediaType->getIdentifier()]['schema'] = $responseSchema;
        }
    }

    public function getWeight(): int
    {
        return 0;
    }
}
