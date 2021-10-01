<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Loader;

/**
 * Import classes
 */
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\RouteCollectionInterface;

/**
 * LoaderInterface
 */
interface LoaderInterface
{

    /**
     * Attaches the given resource to the loader
     *
     * @param mixed $resource
     *
     * @return void
     *
     * @throws InvalidLoaderResourceException
     *         If the given resource isn't valid.
     */
    public function attach($resource) : void;

    /**
     * Attaches the given resources to the loader
     *
     * @param array $resources
     *
     * @return void
     *
     * @throws InvalidLoaderResourceException
     *         If one of the given resources isn't valid.
     */
    public function attachArray(array $resources) : void;

    /**
     * Loads routes from previously attached resources
     *
     * @return RouteCollectionInterface
     */
    public function load() : RouteCollectionInterface;
}
