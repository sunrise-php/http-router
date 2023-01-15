<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Sunrise\Http\Router\Exception\ResponseResolvingException;

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
    public function supportsResponse($response, $context): bool;

    /**
     * Resolves the given raw response to the object
     *
     * @param mixed $response
     * @param mixed $context
     *
     * @return ResponseInterface
     *
     * @throws ResponseResolvingException
     *         If the raw response cannot be resolved to the object.
     */
    public function resolveResponse($response, $context): ResponseInterface;
}
