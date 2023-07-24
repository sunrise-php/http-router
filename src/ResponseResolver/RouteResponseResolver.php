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
     */
    public function supportsResponse(mixed $response, mixed $context): bool
    {
        if (!($context instanceof ServerRequestInterface)) {
            return false;
        }

        if (!($response instanceof RouteInterface)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function resolveResponse(mixed $response, mixed $context): ResponseInterface
    {
        /** @var RouteInterface $response */
        /** @var ServerRequestInterface $context */

        return $response->handle($context);
    }
}
