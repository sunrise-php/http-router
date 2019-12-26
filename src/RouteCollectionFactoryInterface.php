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
 * RouteCollectionFactoryInterface
 */
interface RouteCollectionFactoryInterface
{

    /**
     * Creates a route collection with the given route(s)
     *
     * @param RouteInterface ...$routes
     *
     * @return RouteCollectionInterface
     */
    public function createCollection(RouteInterface ...$routes) : RouteCollectionInterface;
}
