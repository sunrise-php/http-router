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
use Sunrise\Http\Router\Helper\RouteBuilder;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Helper\RouteParser;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RouteInterface;

use function array_merge;
use function sprintf;

/**
 * @since 3.0.0
 */
final class RouteVariablesOperationEnricher implements
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
        ReflectionMethod|ReflectionClass $requestHandler,
        array &$operation,
    ): void {
        $routeVars = RouteParser::parseRoute($route->getPath());
        $routePatterns = $route->getPatterns();
        $routeAttributes = $route->getAttributes();

        $parameterMap = [];
        $parameterIndex = 0;

        foreach ($routeVars as $routeVar) {
            $parameter = [];
            $parameter['in'] = 'path';
            $parameter['name'] = $routeVar['name'];
            $parameter['schema']['type'] = 'string';

            $pattern = $routeVar['pattern'] ?? $routePatterns[$routeVar['name']] ?? RouteCompiler::DEFAULT_PATTERN;
            $parameter['schema']['pattern'] = sprintf('^%s$', $pattern);

            if (isset($routeAttributes[$routeVar['name']])) {
                $default = RouteBuilder::stringifyValue($routeAttributes[$routeVar['name']]);
                $parameter['schema']['default'] = $default;
            }

            if (!isset($routeVar['optional_part'])) {
                $parameter['required'] = true;
            }

            $operation['parameters'][$parameterIndex] = $parameter;
            $parameterMap[$routeVar['name']] = $parameterIndex;
            $parameterIndex++;
        }

        if (! $requestHandler instanceof ReflectionMethod) {
            return;
        }

        foreach ($requestHandler->getParameters() as $requestHandlerParameter) {
            /** @var list<ReflectionAttribute<RequestVariable>> $annotations */
            $annotations = $requestHandlerParameter->getAttributes(RequestVariable::class);
            if (!isset($annotations[0])) {
                continue;
            }

            $requestVariable = $annotations[0]->newInstance();
            $requestVariableName = $requestVariable->name ?? $requestHandlerParameter->getName();
            if (!isset($parameterMap[$requestVariableName])) {
                continue;
            }

            $variableType = TypeFactory::fromPhpTypeReflection($requestHandlerParameter->getType());

            $operation['parameters'][$parameterMap[$requestVariableName]]['schema'] = array_merge(
                $operation['parameters'][$parameterMap[$requestVariableName]]['schema'],
                $this->phpTypeSchemaResolverManager->resolvePhpTypeSchema($variableType, $requestHandlerParameter),
            );
        }
    }

    public function getWeight(): int
    {
        return 0;
    }
}
