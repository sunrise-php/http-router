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

namespace Sunrise\Http\Router\Command;

use ArrayAccess;
use BackedEnum;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use Sunrise\Http\Message\Response;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\Annotation\SerializableResponse;
use Sunrise\Http\Router\Helper\RouteBuilder;
use Sunrise\Http\Router\Helper\RouteParser;
use Sunrise\Http\Router\Helper\RouteSimplifier;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouterInterface;
use Sunrise\Hydrator\Annotation\Alias;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_merge;
use function class_exists;
use function compact;
use function date;
use function end;
use function in_array;
use function is_a;
use function is_subclass_of;
use function json_encode;
use function sprintf;
use function strtolower;

use function strtr;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const PHP_INT_SIZE;

/**
 * @since 3.0.0
 */
#[AsCommand('router:generate-open-api-document', 'Generates OpenAPI Document.')]
final class RouterGenerateOpenApiDocumentCommand extends Command
{
    private const BUILT_IN_TYPE_BOOL = 'bool';
    private const BUILT_IN_TYPE_INT = 'int';
    private const BUILT_IN_TYPE_FLOAT = 'float';
    private const BUILT_IN_TYPE_STRING = 'string';
    private const BUILT_IN_TYPE_ARRAY = 'array';

    private static array $schemas = [];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestHandlerReflectorInterface $requestHandlerReflector,
        private readonly array $document,
        private readonly string $dateTimeFormat,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $document = $this->document;

        foreach ($this->router->getRoutes() as $route) {
            if (!$route->isApiOperation()) {
                continue;
            }

            $controller = $this->requestHandlerReflector->reflectRequestHandler($route->getRequestHandler());
            if (! $controller instanceof ReflectionMethod) {
                continue;
            }

            self::enrichDocumentWithPaths($route, $controller, $document);
        }

        foreach (self::$schemas as $name => $schema) {
            $document['components']['schemas'][$name] = $schema;
        }

        /** @var string $json */
        $json = json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $output->writeln($json);

        return self::SUCCESS;
    }

    private static function enrichDocumentWithPaths(
        RouteInterface $route,
        ReflectionMethod $controller,
        array &$document,
    ): void {
        $operation = $route->getApiOperationFields();
        $operation['operationId'] = $route->getName();
        $operation['tags'] = $route->getTags();
        $operation['summary'] = $route->getSummary();
        $operation['description'] = $route->getDescription();
        $operation['deprecated'] = $route->isDeprecated();

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
            /** @var list<ReflectionAttribute<RequestCookie|RequestHeader>> $annotations */
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
                $operationParameter['schema'] = self::getStringSchema();

                if (!$endpointParameter->isDefaultValueAvailable()) {
                    $operationParameter['required'] = true;
                }

                $operation['parameters'][] = $operationParameter;
            }
        }
    }

    private static function enrichOperationWithRequestBody(
        RouteInterface $route,
        ReflectionMethod $controller,
        array &$operation,
    ): void {
        foreach ($controller->getParameters() as $parameter) {
            if ($parameter->getAttributes(RequestBody::class) === []) {
                continue;
            }

            $schema = self::getTypeSchema($parameter->getType());

            foreach ($route->getConsumedMediaTypes() as $mediaType) {
                $operation['requestBody']['content'][$mediaType->getIdentifier()]['schema'] = $schema;
            }

            return;
        }
    }

    private static function enrichOperationWithResponses(
        RouteInterface $route,
        ReflectionMethod $controller,
        array &$operation,
    ): void {
        if ($controller->getAttributes(SerializableResponse::class, ReflectionAttribute::IS_INSTANCEOF) === []) {
            return;
        }

        $schema = self::getTypeSchema($controller->getReturnType());

        $responseStatusCode = 200;
        $responseStatusPhrase = 'Ok';

        /** @var list<ReflectionAttribute<ResponseStatus>> $annotations */
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

    /**
     * @throws ReflectionException
     */
    private static function getTypeSchema(?ReflectionType $type): array
    {
        if ($type === null) {
            return [];
        }

        $schema = [];

        $typeName = (string) $type;

        if ($typeName === self::BUILT_IN_TYPE_BOOL) {
            $schema = self::getBoolSchema();
        } elseif ($typeName === self::BUILT_IN_TYPE_INT) {
            $schema = self::getIntSchema();
        } elseif ($typeName === self::BUILT_IN_TYPE_FLOAT) {
            $schema = self::getFloatSchema();
        } elseif ($typeName === self::BUILT_IN_TYPE_STRING) {
            $schema = self::getStringSchema();
        } elseif ($typeName === self::BUILT_IN_TYPE_ARRAY) {
            $schema = self::getArraySchema();
        } elseif (is_subclass_of($typeName, BackedEnum::class)) {
            $schema = self::getEnumSchema($typeName, nullable: $type->allowsNull());
        } elseif (is_subclass_of($typeName, ArrayAccess::class)) {
            $schema = self::getCollectionSchema($typeName);
        } elseif (is_a($typeName, DateTimeImmutable::class, true)) {
            $schema = self::getStringSchema();
            $schema['format'] = 'date-time';
        } elseif (class_exists($typeName)) {
            $schema = self::getClassSchema($typeName);
        }

        if ($type->allowsNull()) {
            $schema['nullable'] = true;
        }

        return $schema;
    }

    private static function getBoolSchema(): array
    {
        return [
            'type' => 'boolean',
        ];
    }

    private static function getIntSchema(): array
    {
        return [
            'type' => 'integer',
            'format' => PHP_INT_SIZE === 4 ? 'int32' : 'int64',
        ];
    }

    private static function getFloatSchema(): array
    {
        return [
            'type' => 'number',
            'format' => 'double',
        ];
    }

    private static function getStringSchema(): array
    {
        return [
            'type' => 'string',
        ];
    }

    /**
     * @return array{oneOf: array{0: array{type: 'array'}, 1: array{type: 'object'}}}
     */
    private static function getArraySchema(): array
    {
        return [
            'oneOf' => [
                [
                    'type' => 'array',
                ],
                [
                    'type' => 'object',
                ],
            ],
        ];
    }

    /**
     * @param class-string<BackedEnum> $enumName
     *
     * @throws ReflectionException
     */
    private static function getEnumSchema(string $enumName, bool $nullable = false): array
    {
        $schemaName = strtr($enumName, '\\', '.');
        $schemaRef = self::getSchemaRef($schemaName);

        if (isset(self::$schemas[$schemaName])) {
            return $schemaRef;
        }

        $schema = match ((string) (new ReflectionEnum($enumName))->getBackingType()) {
            self::BUILT_IN_TYPE_INT => self::getIntSchema(),
            self::BUILT_IN_TYPE_STRING => self::getStringSchema(),
        };

        $schema['enum'] = [];
        foreach ($enumName::cases() as $case) {
            $schema['enum'][] = $case->value;
        }

        // https://swagger.io/docs/specification/data-models/enums/#nullable
        if ($nullable) {
            $schema['enum'][] = null;
        }

        self::$schemas[$schemaName] = $schema;

        return $schemaRef;
    }

    /**
     * @link https://github.com/sunrise-php/hydrator/blob/main/README.md#array
     *
     * @param class-string<ArrayAccess> $className
     *
     * @throws ReflectionException
     */
    private static function getCollectionSchema(string $className): array
    {
        $schemaName = strtr($className, '\\', '.');
        $schemaRef = self::getSchemaRef($schemaName);

        if (isset(self::$schemas[$schemaName])) {
            return $schemaRef;
        }

        $class = new ReflectionClass($className);

        $schema = self::getArraySchema();

        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return $schema;
        }

        $parameters = $constructor->getParameters();
        if ($parameters === []) {
            return $schema;
        }

        /** @var ReflectionParameter $parameter */
        $parameter = end($parameters);
        if (!$parameter->isVariadic()) {
            return $schema;
        }

        $type = $parameter->getType();
        if ($type === null) {
            return $schema;
        }

        $elementSchema = self::getTypeSchema($type);

        $schema['oneOf'][0]['items'] = $elementSchema;
        $schema['oneOf'][1]['additionalProperties'] = $elementSchema;

        self::$schemas[$schemaName] = $schema;

        return $schemaRef;
    }

    /**
     * @param class-string<object> $className
     *
     * @throws ReflectionException
     */
    private static function getClassSchema(string $className): array
    {
        $schemaName = strtr($className, '\\', '.');
        $schemaRef = self::getSchemaRef($schemaName);

        if (isset(self::$schemas[$schemaName])) {
            return $schemaRef;
        }

        $class = new ReflectionClass($className);

        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
        ];

        foreach ($class->getProperties() as $property) {
            $propertyName = self::getPropertyName($property);
            $propertySchema = self::getTypeSchema($property->getType());

            $schema['properties'][$propertyName] = $propertySchema;

            if (!self::isOptionalProperty($property)) {
                $schema['required'][] = $propertyName;
            }
        }

        self::$schemas[$schemaName] = $schema;

        return $schemaRef;
    }

    /**
     * @link https://github.com/sunrise-php/hydrator/blob/main/README.md#property-alias
     */
    private static function getPropertyName(ReflectionProperty $property): string
    {
        /** @var list<ReflectionAttribute<Alias>> $annotations */
        $annotations = $property->getAttributes(Alias::class);
        if (isset($annotations[0])) {
            $alias = $annotations[0]->newInstance();
            return $alias->value;
        }

        return $property->getName();
    }

    /**
     * @link https://github.com/sunrise-php/hydrator/blob/main/README.md#optional
     */
    private static function isOptionalProperty(ReflectionProperty $property): bool
    {
        if ($property->hasDefaultValue()) {
            return true;
        }

        if (!$property->isPromoted()) {
            return false;
        }

        foreach ($property->getDeclaringClass()->getConstructor()?->getParameters() ?? [] as $parameter) {
            if ($parameter->getName() === $property->getName()) {
                return $parameter->isDefaultValueAvailable();
            }
        }

        return false; // will never get here...
    }

    private static function getSchemaRef(string $schemaName): array
    {
        return [
            '$ref' => '#/components/schemas/' . $schemaName,
        ];
    }
}
