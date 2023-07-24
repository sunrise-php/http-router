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

namespace Sunrise\Http\Router;

/**
 * RouteCollectionFactory
 */
class RouteCollectionFactory implements RouteCollectionFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function createCollection(RouteInterface ...$routes): RouteCollectionInterface
    {
        return new RouteCollection(...$routes);
    }
}
