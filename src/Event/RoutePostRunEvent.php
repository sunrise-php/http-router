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

namespace Sunrise\Http\Router\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class RoutePostRunEvent
{
    public function __construct(
        public readonly RouteInterface $route,
        public readonly ServerRequestInterface $request,
        public ResponseInterface $response,
    ) {
    }
}
