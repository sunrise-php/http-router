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

use ArrayAccess;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\Event\OpenApiTypeSchemaResolveEvent;
use Sunrise\Http\Router\Helper\RouteBuilder;
use Sunrise\Http\Router\Helper\RouteParser;
use Sunrise\Http\Router\Helper\RouteSimplifier;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouterInterface;
use Sunrise\Hydrator\Annotation\Alias;
use Sunrise\Hydrator\Annotation\DefaultValue;
use Sunrise\Hydrator\Annotation\Ignore;
use Sunrise\Hydrator\Annotation\Subtype;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function array_merge;
use function class_exists;
use function end;
use function file_put_contents;
use function is_subclass_of;
use function json_encode;
use function strtolower;
use function strtr;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

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
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $document = $this->openApiConfiguration->blankDocument;

        foreach ($this->router->getRoutes() as $route) {
            if (!$route->isApiOperation()) {
                continue;
            }

            $controller = $this->requestHandlerReflector->reflectRequestHandler($route->getRequestHandler());

            self::enrichDocumentWithPaths($route, $controller, $document);
        }

        foreach ($this->phpTypeSchemaResolverChain->getNamedPhpTypeSchemas() as $phpTypeSchemaName => $phpTypeSchema) {
            $document['components']['schemas'][$phpTypeSchemaName] = $phpTypeSchema;
        }

        /** @var string $json */
        $json = json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        file_put_contents($this->openApiConfiguration->documentFilename, $json);

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
        self::enrichOperationWithSerializableResponse($route, $controller, $operation);

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
        ReflectionMethod $controller,
        array &$operation,
    ): void {
        foreach ($controller->getParameters() as $parameter) {
            if ($parameter->getAttributes(RequestBody::class) === []) {
                continue;
            }

            $schema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema(
                TypeFactory::fromPhpTypeReflection($parameter->getType()),
                $parameter,
            );

            foreach ($route->getConsumedMediaTypes() as $mediaType) {
                $operation['requestBody']['content'][$mediaType->getIdentifier()]['schema'] = $schema;
            }

            return;
        }
    }

    private function enrichOperationWithSerializableResponse(
        RouteInterface $route,
        ReflectionMethod $controller,
        array &$operation,
    ): void {
        if ($controller->getAttributes(EncodableResponse::class) === []) {
            return;
        }

        $schema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema(
            TypeFactory::fromPhpTypeReflection($controller->getReturnType()),
            $controller,
        );

        $responseStatusCode = 200;
        $responseStatusPhrase = 'Operation completed successfully.';

        /** @var ReflectionAttribute $annotations */
        $annotations = $controller->getAttributes(ResponseStatus::class);
        if (isset($annotations[0])) {
            $responseStatus = $annotations[0]->newInstance();
            $responseStatusCode = $responseStatus->code;
        }

        foreach ($route->getProducedMediaTypes() as $mediaType) {
            $operation['responses'][$responseStatusCode]['content'][$mediaType->getIdentifier()]['schema'] = $schema;
            $operation['responses'][$responseStatusCode]['description'] = $responseStatusPhrase;
        }
    }
}
