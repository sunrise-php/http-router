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

namespace Sunrise\Http\Router\ParameterResolving\ParameterResolver;

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ServerRequest;

/**
 * ServerRequestParameterResolver
 *
 * @since 3.0.0
 */
final class ServerRequestParameterResolver implements ParameterResolverInterface
{

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType || $type->getName() <> ServerRequest::class) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        yield ServerRequest::from($context);
    }
}
