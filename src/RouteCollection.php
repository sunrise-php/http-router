<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * RouteCollection
 */
class RouteCollection implements RouteCollectionInterface
{

    /**
     * The collection routes
     *
     * @var RouteInterface[]
     */
    private $routes;

    /**
     * Constructor of the class
     *
     * @param RouteInterface ...$routes
     */
    public function __construct(RouteInterface ...$routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritDoc}
     */
    public function add(RouteInterface ...$routes) : void
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function all() : array
    {
        return $this->routes;
    }
}
