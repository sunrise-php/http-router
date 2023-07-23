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

namespace Sunrise\Http\Router\Loader;

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
     */
    public function attach(mixed $resource): void;

    /**
     * Attaches the given resources to the loader
     *
     * @param array<array-key, mixed> $resources
     *
     * @return void
     */
    public function attachArray(array $resources): void;

    /**
     * Loads routes from previously attached resources
     *
     * @return RouteCollectionInterface
     */
    public function load(): RouteCollectionInterface;
}
