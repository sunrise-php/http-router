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

namespace Sunrise\Http\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\ResponseResolver\ResponseResolverInterface;

/**
 * ResponseResolutionerInterface
 *
 * @since 3.0.0
 */
interface ResponseResolutionerInterface
{

    /**
     * Adds the given response resolver(s) to the resolutioner
     *
     * @param ResponseResolverInterface ...$resolvers
     *
     * @return void
     */
    public function addResolver(ResponseResolverInterface ...$resolvers): void;

    /**
     * Resolves the given response to PSR-7 response
     *
     * @param mixed $response
     * @param ServerRequestInterface $request
     * @param ReflectionFunction|ReflectionMethod $source
     *
     * @return ResponseInterface
     */
    public function resolveResponse(
        mixed $response,
        ServerRequestInterface $request,
        ReflectionFunction|ReflectionMethod $source,
    ) : ResponseInterface;
}
