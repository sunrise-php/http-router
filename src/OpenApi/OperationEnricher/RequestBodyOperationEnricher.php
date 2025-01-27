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
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class RequestBodyOperationEnricher implements
    OpenApiOperationEnricherInterface,
    PhpTypeSchemaResolverManagerAwareInterface
{
    private readonly PhpTypeSchemaResolverManagerInterface $phpTypeSchemaResolverManager;

    public function setPhpTypeSchemaResolverManager(
        PhpTypeSchemaResolverManagerInterface $phpTypeSchemaResolverManager,
    ): void {
        $this->phpTypeSchemaResolverManager = $phpTypeSchemaResolverManager;
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

        $requestBodySchema = null;
        foreach ($requestHandler->getParameters() as $requestHandlerParameter) {
            if ($requestHandlerParameter->getAttributes(RequestBody::class) !== []) {
                $requestBodyType = TypeFactory::fromPhpTypeReflection($requestHandlerParameter->getType());
                $requestBodySchema = $this->phpTypeSchemaResolverManager
                    ->resolvePhpTypeSchema($requestBodyType, $requestHandlerParameter);
                break;
            }
        }

        if ($requestBodySchema === null) {
            return;
        }

        $operation['requestBody']['required'] = true;
        foreach ($route->getConsumedMediaTypes() as $consumedMediaType) {
            $operation['requestBody']['content'][$consumedMediaType->getIdentifier()]['schema'] = $requestBodySchema;
        }
    }

    public function getWeight(): int
    {
        return 0;
    }
}
