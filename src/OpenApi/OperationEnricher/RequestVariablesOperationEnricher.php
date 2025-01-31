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
use Sunrise\Http\Router\Annotation\RequestVariable;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Helper\RouteParser;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class RequestVariablesOperationEnricher implements
    OpenApiOperationEnricherInterface,
    OpenApiPhpTypeSchemaResolverManagerAwareInterface
{
    private readonly OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager;

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
        $variables = RouteParser::parseRoute($route->getPath());
        $variablePatterns = $route->getPatterns();

        foreach ($variables as $variable) {
            $variableName = $variable['name'];

            $variablePattern = $variable['pattern']
                ?? $variablePatterns[$variableName]
                ?? RouteCompiler::DEFAULT_VARIABLE_PATTERN;

            $variableSchema = $this->getRequestVariableSchema($requestHandler, $variableName);
            $variableSchema ??= ['type' => Type::OAS_TYPE_NAME_STRING];
            $variableSchema['pattern'] = '^' . $variablePattern . '$';

            $operation['parameters'][] = [
                'in' => 'path',
                'name' => $variableName,
                'schema' => $variableSchema,
                // https://github.com/OAI/OpenAPI-Specification/issues/93
                'required' => true,
            ];
        }
    }

    public function getWeight(): int
    {
        return 40;
    }

    /**
     * @param ReflectionClass<object>|ReflectionMethod $requestHandler
     *
     * @return array<array-key, mixed>|null
     */
    private function getRequestVariableSchema(
        ReflectionClass|ReflectionMethod $requestHandler,
        string $variableName,
    ): ?array {
        if ($requestHandler instanceof ReflectionMethod) {
            foreach ($requestHandler->getParameters() as $requestHandlerParameter) {
                /** @var list<ReflectionAttribute<RequestVariable>> $annotations */
                $annotations = $requestHandlerParameter->getAttributes(RequestVariable::class);
                if (isset($annotations[0])) {
                    $requestVariable = $annotations[0]->newInstance();
                    $requestVariableName = $requestVariable->name ?? $requestHandlerParameter->getName();
                    if ($requestVariableName === $variableName) {
                        $requestVariableType = TypeFactory::fromPhpTypeReflection($requestHandlerParameter->getType());

                        return $this->openApiPhpTypeSchemaResolverManager
                            ->resolvePhpTypeSchema($requestVariableType, $requestHandlerParameter);
                    }
                }
            }
        }

        return null;
    }
}
