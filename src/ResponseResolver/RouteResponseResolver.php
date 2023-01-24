<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\ResponseResolver;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\ResponseResolverInterface;
use Sunrise\Http\Router\RouteInterface;

/**
 * RouteResponseResolver
 *
 * @since 3.0.0
 */
final class RouteResponseResolver implements ResponseResolverInterface
{

    /**
     * {@inheritdoc}
     */
    public function supportsResponse($response, $context): bool
    {
        if (!($response instanceof RouteInterface)) {
            return false;
        }

        if (!($context instanceof ServerRequestInterface)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveResponse($response, $context): ResponseInterface
    {
        /** @var RouteInterface */
        $response = $response;

        /** @var ServerRequestInterface */
        $context = $context;

        return $response->handle($context);
    }
}
