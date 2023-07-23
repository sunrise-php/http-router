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

use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\RouteInterface;

/**
 * RouteEvent
 *
 * @since 2.13.0
 */
final class RouteEvent
{

    /**
     * Constructor of the class
     *
     * @param RouteInterface $route
     * @param ServerRequestInterface $request
     */
    public function __construct(private RouteInterface $route, private ServerRequestInterface $request)
    {
    }

    /**
     * @return RouteInterface
     */
    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
}
