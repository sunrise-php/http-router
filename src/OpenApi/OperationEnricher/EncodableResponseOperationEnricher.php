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
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiConfigurationAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class EncodableResponseOperationEnricher implements
    OpenApiOperationEnricherInterface,
    OpenApiConfigurationAwareInterface,
    PhpTypeSchemaResolverManagerAwareInterface
{
    private readonly OpenApiConfiguration $openApiConfiguration;
    private readonly PhpTypeSchemaResolverManagerInterface $phpTypeSchemaResolverManager;

    public function setOpenApiConfiguration(OpenApiConfiguration $openApiConfiguration): void
    {
        $this->openApiConfiguration = $openApiConfiguration;
    }

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
        ReflectionMethod|ReflectionClass $requestHandler,
        array &$operation,
    ): void {
        if (! $requestHandler instanceof ReflectionMethod) {
            return;
        }

        if ($requestHandler->getAttributes(EncodableResponse::class) === []) {
            return;
        }

        $responseStatusCode = $this->openApiConfiguration->successfulResponseStatusCode;

        /** @var list<ReflectionAttribute<ResponseStatus>> $annotations */
        $annotations = $requestHandler->getAttributes(ResponseStatus::class);
        if (isset($annotations[0])) {
            $responseStatus = $annotations[0]->newInstance();
            $responseStatusCode = $responseStatus->code;
        }

        $operation['responses'][$responseStatusCode]['description'] = $this->openApiConfiguration
            ->successfulResponseDescription;

        $responseType = TypeFactory::fromPhpTypeReflection($requestHandler->getReturnType());
        $responseSchema = $this->phpTypeSchemaResolverManager->resolvePhpTypeSchema($responseType, $requestHandler);

        foreach ($route->getProducedMediaTypes() as $mediaType) {
            // phpcs:disable Generic.Files.LineLength.TooLong
            $operation['responses'][$responseStatusCode]['content'][$mediaType->getIdentifier()]['schema'] = $responseSchema;
        }
    }

    public function getWeight(): int
    {
        return 0;
    }
}
