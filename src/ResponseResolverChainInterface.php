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
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

/**
 * @since 3.0.0
 */
interface ResponseResolverChainInterface
{
    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function resolveResponse(
        mixed $response,
        ReflectionMethod $responder,
        ServerRequestInterface $request,
    ): ResponseInterface;
}
