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

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;

/**
 * @since 3.0.0
 */
interface ResponseResolverInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function resolveResponse(
        mixed $response,
        ReflectionMethod|ReflectionFunction $responder,
        ServerRequestInterface $request,
    ): ?ResponseInterface;

    public function getWeight(): int;
}
