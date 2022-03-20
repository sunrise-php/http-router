<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Event;

/**
 * Import classes
 */
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\RouteInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * RouteEvent
 *
 * @since 2.13.0
 */
final class RouteEvent extends Event
{

    /**
     * @var string
     */
    public const NAME = 'router.route';

    /**
     * @var RouteInterface
     */
    private $route;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * Constructor of the class
     *
     * @param RouteInterface $route
     * @param ServerRequestInterface $request
     */
    public function __construct(RouteInterface $route, ServerRequestInterface $request)
    {
        $this->route = $route;
        $this->request = $request;
    }

    /**
     * @return RouteInterface
     */
    public function getRoute() : RouteInterface
    {
        return $this->route;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest() : ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    public function setRequest(ServerRequestInterface $request) : void
    {
        $this->request = $request;
    }
}
