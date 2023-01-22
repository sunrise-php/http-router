<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Loader;

/**
 * Import classes
 */
use Sunrise\Http\Router\Exception\InvalidArgumentException;
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
     * @throws InvalidArgumentException
     *         If the given resource isn't valid.
     */
    public function attach($resource): void;

    /**
     * Attaches the given resources to the loader
     *
     * @param array<array-key, mixed> $resources
     *
     * @return void
     *
     * @throws InvalidArgumentException
     *         If one of the given resources isn't valid.
     */
    public function attachArray(array $resources): void;

    /**
     * Loads routes from previously attached resources
     *
     * @return RouteCollectionInterface
     */
    public function load(): RouteCollectionInterface;
}
