<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\RequestHandler;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RouteInterface;

/**
 * RoutableRequestHandler
 */
class RoutableRequestHandler implements RequestHandlerInterface
{

    /**
     * Server Request attribute name for a route name
     *
     * @var string
     */
    public const ATTR_NAME_FOR_ROUTE_NAME = '@route-name';

    /**
     * The request handler route
     *
     * @var RouteInterface
     */
    private $route;

    /**
     * Constructor of the class
     *
     * @param RouteInterface $route
     */
    public function __construct(RouteInterface $route)
    {
        $this->route = $route;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $request = $request->withAttribute(self::ATTR_NAME_FOR_ROUTE_NAME, $this->route->getName());

        foreach ($this->route->getAttributes() as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $handler = new QueueableRequestHandler($this->route->getRequestHandler());

        $handler->add(...$this->route->getMiddlewares());

        return $handler->handle($request);
    }
}
