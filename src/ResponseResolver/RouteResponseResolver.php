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

namespace Sunrise\Http\Router\ResponseResolver;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\RouteInterface;

/**
 * RouteResponseResolver
 *
 * @since 3.0.0
 */
final class RouteResponseResolver implements ResponseResolverInterface
{

    /**
     * @inheritDoc
     *
     * @throws LogicException
     *         If the resolver is used incorrectly.
     */
    public function resolveResponse(mixed $value, mixed $context): ?ResponseInterface
    {
        if (! $value instanceof RouteInterface) {
            return null;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        return $value->handle($context);
    }
}
