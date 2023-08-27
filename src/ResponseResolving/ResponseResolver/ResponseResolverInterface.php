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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;

/**
 * ResponseResolverInterface
 *
 * @since 3.0.0
 */
interface ResponseResolverInterface
{

    /**
     * Resolves the given response to PSR-7 response
     *
     * @param ReflectionFunction|ReflectionMethod $source
     * @param ServerRequestInterface $request
     * @param mixed $response
     *
     * @return ResponseInterface|null
     */
    public function resolveResponse(
        ReflectionFunction|ReflectionMethod $source,
        ServerRequestInterface $request,
        mixed $response,
    ) : ?ResponseInterface;
}
