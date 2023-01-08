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
     * Resolves the given data to the response instance
     *
     * @param mixed $data
     *
     * @return ResponseInterface
     *
     * @throws LogicException
     *         If the data cannot be resolved to the response.
     */
    public function resolveResponse($data): ResponseInterface;
}
