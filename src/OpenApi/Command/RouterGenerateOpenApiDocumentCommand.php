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

namespace Sunrise\Http\Router\OpenApi\Command;

use JsonException;
use Psr\Http\Message\StreamInterface;
use ReflectionAttribute;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Annotation\ResponseHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\Helper\RouteBuilder;
use Sunrise\Http\Router\Helper\RouteParser;
use Sunrise\Http\Router\Helper\RouteSimplifier;
use Sunrise\Http\Router\OpenApi\Annotation\ResponseSchema;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_merge;
use function file_put_contents;
use function json_encode;
use function strtolower;

use const JSON_THROW_ON_ERROR;

/**
 * @since 3.0.0
 */
#[AsCommand('router:generate-open-api-document', 'Generates OpenAPI Document.')]
final class RouterGenerateOpenApiDocumentCommand extends Command
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly OpenApiConfiguration $openApiConfiguration,
        private readonly PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain,
        private readonly RequestHandlerReflectorInterface $requestHandlerReflector,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     *
     * @throws JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $document = $this->openApiConfiguration->blankDocument;

        foreach ($this->router->getRoutes() as $route) {
            if (!$route->isApiOperation()) {
                continue;
            }

            $requestHandler = $this->requestHandlerReflector->reflectRequestHandler($route->getRequestHandler());

            self::enrichDocumentWithPaths($route, $requestHandler, $document);
        }

        foreach ($this->phpTypeSchemaResolverChain->getNamedPhpTypeSchemas() as $phpTypeSchemaName => $phpTypeSchema) {
            $document['components']['schemas'][$phpTypeSchemaName] = $phpTypeSchema;
        }

        file_put_contents(
            $this->openApiConfiguration->getDocumentFilename(),
            json_encode($document, JSON_THROW_ON_ERROR),
        );

        $output->writeln('Done.');

        return self::SUCCESS;
    }

    private function enrichDocumentWithPaths(
        RouteInterface $route,
        ReflectionMethod $controller,
        array &$document,
    ): void {
        $operation = (array) $route->getApiOperationDocFields();
        $operation['operationId'] = $route->getName();
        $operation['tags'] = $route->getTags();
        $operation['summary'] = $route->getSummary();
        $operation['description'] = $route->getDescription();

        if ($route->isDeprecated()) {
            $operation['deprecated'] = true;
        }

        self::enrichOperationWithPathParameters($route, $operation);
        self::enrichOperationWithCookieAndHeaderParameters($controller, $operation);

        self::enrichOperationWithRequestBody($route, $controller, $operation);
        self::enrichOperationWithResponses($route, $controller, $operation);

        $routePath = RouteSimplifier::simplifyRoute($route->getPath());
        foreach ($route->getMethods() as $routeMethod) {
            $routeMethod = strtolower($routeMethod);
            $document['paths'][$routePath][$routeMethod] = $operation;
        }
    }

    private static function enrichOperationWithPathParameters(RouteInterface $route, array &$operation): void
    {
        $routeVars = RouteParser::parseRoute($route->getPath());
        $routePatterns = $route->getPatterns();
        $routeAttributes = $route->getAttributes();

        foreach ($routeVars as $routeVar) {
            $parameter = [];
            $parameter['in'] = 'path';
            $parameter['name'] = $routeVar['name'];
            $parameter['schema']['type'] = 'string';

            $pattern = $routeVar['pattern'] ?? $routePatterns[$routeVar['name']] ?? null;
            if ($pattern !== null) {
                $parameter['schema']['pattern'] = '^' . $pattern . '$';
            }

            if (isset($routeAttributes[$routeVar['name']])) {
                $default = RouteBuilder::stringifyValue($routeAttributes[$routeVar['name']]);
                $parameter['schema']['default'] = $default;
            }

            if (!isset($routeVar['optional_part'])) {
                $parameter['required'] = true;
            }

            $operation['parameters'][] = $parameter;
        }
    }

    private static function enrichOperationWithCookieAndHeaderParameters(
        ReflectionMethod $endpoint,
        array &$operation,
    ): void {
        foreach ($endpoint->getParameters() as $endpointParameter) {
            /** @var ReflectionAttribute $annotations */
            $annotations = array_merge(
                $endpointParameter->getAttributes(RequestCookie::class),
                $endpointParameter->getAttributes(RequestHeader::class),
            );

            foreach ($annotations as $annotation) {
                $annotation = $annotation->newInstance();

                $operationParameter = [];

                $operationParameter['in'] = match ($annotation::class) {
                    RequestCookie::class => 'cookie',
                    RequestHeader::class => 'header',
                };

                $operationParameter['name'] = $annotation->name;
                $operationParameter['schema']['type'] = 'string';

                if (!$endpointParameter->isDefaultValueAvailable()) {
                    $operationParameter['required'] = true;
                }

                $operation['parameters'][] = $operationParameter;
            }
        }
    }

    private function enrichOperationWithRequestBody(
        RouteInterface $route,
        ReflectionMethod $requestHandler,
        array &$operation,
    ): void {
        $requestBodySchema = [];

        foreach ($requestHandler->getParameters() as $parameter) {
            if ($parameter->getAttributes(RequestBody::class) !== []) {
                $requestBodyType = TypeFactory::fromPhpTypeReflection($parameter->getType());
                // phpcs:disable Generic.Files.LineLength.TooLong
                $requestBodySchema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema($requestBodyType, $parameter);
                break;
            }
            if ($parameter->getType()?->getName() === StreamInterface::class) {
                $requestBodySchema['type'] = Type::OAS_TYPE_NAME_STRING;
                $requestBodySchema['format'] = 'binary';
                break;
            }
        }

        $serverConsumedMediaTypes = $route->getConsumedMediaTypes();
        if ($serverConsumedMediaTypes !== []) {
            foreach ($serverConsumedMediaTypes as $mediaType) {
                $operation['requestBody']['content'][$mediaType->getIdentifier()]['schema'] = $requestBodySchema;
            }

            $operation['requestBody']['required'] = true;
        }
    }

    private function enrichOperationWithResponses(
        RouteInterface $route,
        ReflectionMethod $requestHandler,
        array &$operation,
    ): void {
        $responseStatusCode = $this->openApiConfiguration->defaultCompletedOperationStatusCode;
        $responseDescription = $this->openApiConfiguration->defaultCompletedOperationDescription;
        $responseSchema = [];

        if ($requestHandler->getReturnType()?->getName() === Type::PHP_TYPE_NAME_VOID) {
            $responseStatusCode = $this->openApiConfiguration->defaultNullResponseStatusCode;
        } elseif ($requestHandler->getAttributes(EncodableResponse::class) !== []) {
            $responseType = TypeFactory::fromPhpTypeReflection($requestHandler->getReturnType());
            $responseSchema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema($responseType, $requestHandler);
        }

        /** @var list<ReflectionAttribute<ResponseStatus>> $annotations */
        $annotations = $requestHandler->getAttributes(ResponseStatus::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $responseStatusCode = $annotation->code;
        }

        /** @var list<ReflectionAttribute<ResponseSchema>> $annotations */
        $annotations = $requestHandler->getAttributes(ResponseSchema::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $responseSchema = $annotation->value;
        }

        $operation['responses'][$responseStatusCode]['description'] = $responseDescription;

        /** @var ReflectionAttribute<ResponseHeader> $annotation */
        foreach ($requestHandler->getAttributes(ResponseHeader::class) as $annotation) {
            $annotation = $annotation->newInstance();

            $operation['responses'][$responseStatusCode]['headers'][$annotation->name]['schema'] = [
                'type' => 'string',
            ];
        }

        foreach ($route->getProducedMediaTypes() as $mediaType) {
            // phpcs:disable Generic.Files.LineLength.TooLong
            $operation['responses'][$responseStatusCode]['content'][$mediaType->getIdentifier()]['schema'] = $responseSchema;
        }
    }
}
