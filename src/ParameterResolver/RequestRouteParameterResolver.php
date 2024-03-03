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
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolver;
use Sunrise\Http\Router\Route;

use function sprintf;

/**
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
        if (! $type instanceof ReflectionNamedType || $type->getName() !== Route::class) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        /** @var Route|null $route */
        $route = $context->getAttribute('@route');
        if (! $route instanceof Route && !$parameter->allowsNull()) {
            throw new LogicException(sprintf(
                'At this level of the application, the request does not contain a route. ' .
                'To suppress this error, the parameter %s should be nullable.',
                ParameterResolver::stringifyParameter($parameter),
            ));
        }

        yield $route;
    }
}
