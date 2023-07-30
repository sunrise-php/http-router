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
     * Resolves the given value to PSR-7 response
     *
     * @param mixed $value
     * @param mixed $context
     *
     * @return ResponseInterface
     */
    public function resolveResponse(mixed $value, mixed $context): ResponseInterface;
}
