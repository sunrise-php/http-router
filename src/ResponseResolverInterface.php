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
use Sunrise\Http\Router\Exception\LogicException;

/**
 * ResponseResolverInterface
 *
 * @since 3.0.0
 */
interface ResponseResolverInterface
{

    /**
     * Resolves the given raw response to the response object
     *
     * @param mixed $response
     *
     * @return ResponseInterface
     *
     * @throws LogicException
     *         If the raw response cannot be resolved to the response object.
     */
    public function resolveResponse($response): ResponseInterface;
}
