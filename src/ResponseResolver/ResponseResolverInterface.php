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
use Sunrise\Http\Router\Exception\ResolvingResponseException;

/**
 * ResponseResolverInterface
 *
 * @since 3.0.0
 */
interface ResponseResolverInterface
{

    /**
     * Checks if the given raw response is supported
     *
     * @param mixed $response
     * @param mixed $context
     *
     * @return bool
     */
    public function supportsResponse(mixed $response, mixed $context): bool;

    /**
     * Resolves the given raw response to the object
     *
     * @param mixed $response
     * @param mixed $context
     *
     * @return ResponseInterface
     *
     * @throws ResolvingResponseException
     *         If the raw response cannot be resolved to the object.
     */
    public function resolveResponse(mixed $response, mixed $context): ResponseInterface;
}