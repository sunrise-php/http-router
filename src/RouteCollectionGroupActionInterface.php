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
 * Import classes
 */
use Psr\Http\Server\MiddlewareInterface;

/**
 * RouteCollectionGroupActionInterface
 */
interface RouteCollectionGroupActionInterface
{

    /**
     * Adds the given prefix to all routes in the instance collection
     *
     * @param string $prefix
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function addPrefix(string $prefix) : RouteCollectionGroupActionInterface;

    /**
     * Adds the given suffix to all routes in the instance collection
     *
     * @param string $suffix
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function addSuffix(string $suffix) : RouteCollectionGroupActionInterface;

    /**
     * Adds the given method(s) to all routes in the instance collection
     *
     * @param string ...$methods
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function addMethod(string ...$methods) : RouteCollectionGroupActionInterface;

    /**
     * Adds the given middleware(s) to all routes in the instance collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionGroupActionInterface;

    /**
     * Adds the given middleware(s) to the beginning of all routes in the instance collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function unshiftMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionGroupActionInterface;
}
