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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sunrise\Http\Router\Exception\ResolvingResponseException;
use Sunrise\Http\Router\ResponseResolver\ResponseResolverInterface;

/**
 * ResponseResolutionerInterface
 *
 * @since 3.0.0
 */
interface ResponseResolutionerInterface
{

    /**
     * Creates a new instance of the resolutioner with the given current context
     *
     * Please note that this method MUST NOT change the object state.
     *
     * @param RequestInterface $context
     *
     * @return static
     */
    public function withRequest(RequestInterface $context): static;

    /**
     * Adds the given response resolver(s) to the resolutioner
     *
     * @param ResponseResolverInterface ...$resolvers
     *
     * @return void
     */
    public function addResolver(ResponseResolverInterface ...$resolvers): void;

    /**
     * Resolves the given raw response to the object
     *
     * @param mixed $response
     *
     * @return ResponseInterface
     *
     * @throws ResolvingResponseException
     *         If the raw response cannot be resolved to the object.
     */
    public function resolveResponse($response): ResponseInterface;
}
