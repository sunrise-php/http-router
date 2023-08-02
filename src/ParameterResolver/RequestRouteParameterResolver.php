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

namespace Sunrise\Http\Router\ParameterResolver;

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolutioner;
use Sunrise\Http\Router\RouteInterface;

use function sprintf;

/**
 * RequestRouteParameterResolver
 *
 * @since 3.0.0
 */
final class RequestRouteParameterResolver implements ParameterResolverInterface
{

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        $type = $parameter->getType();

        if (
            ! ($type instanceof ReflectionNamedType) ||
            ! ($type->getName() === RouteInterface::class)
        ) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        /** @var RouteInterface|null $route */
        $route = $context->getAttribute('@route');

        if (! $route instanceof RouteInterface && !$parameter->allowsNull()) {
            throw new LogicException(sprintf(
                'At this level of the application, the current request does not contain a route. ' .
                'To suppress this error, the parameter {%s} should be nullable.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
        }

        yield $route;
    }
}
