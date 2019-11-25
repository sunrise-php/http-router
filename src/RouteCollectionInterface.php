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
use IteratorAggregate;
use Countable;

/**
 * RouteCollectionInterface
 */
interface RouteCollectionInterface extends IteratorAggregate, Countable
{

    /**
     * Adds the given route(s) to the collection
     *
     * @param RouteInterface ...$routes
     *
     * @return void
     */
    public function add(RouteInterface ...$routes) : void;

    /**
     * Gets all routes from the collection
     *
     * @return RouteInterface[]
     */
    public function all() : array;
}
