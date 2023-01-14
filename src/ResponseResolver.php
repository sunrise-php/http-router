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
 * ResponseResolver
 *
 * @since 3.0.0
 */
final class ResponseResolver implements ResponseResolverInterface
{

    /**
     * {@inheritdoc}
     */
    public function resolveResponse($response): ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        throw new LogicException();
    }
}
