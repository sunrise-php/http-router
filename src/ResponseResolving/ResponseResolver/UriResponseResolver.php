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

namespace Sunrise\Http\Router\ResponseResolving\ResponseResolver;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use ReflectionFunction;
use ReflectionMethod;

/**
 * UriResponseResolver
 *
 * @since 3.0.0
 */
final class UriResponseResolver implements ResponseResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * @inheritDoc
     */
    public function resolveResponse(
        ServerRequestInterface $request,
        mixed $response,
        ReflectionFunction|ReflectionMethod $responder,
    ) : ?ResponseInterface {
        if (! $response instanceof UriInterface) {
            return null;
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $response->__toString());
    }
}
