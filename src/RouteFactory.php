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
use Psr\Http\Server\RequestHandlerInterface;

/**
 * RouteFactory
 */
class RouteFactory implements RouteFactoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function createRoute(
        string $name,
        string $path,
        array $methods,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return new Route(
            $name,
            $path,
            $methods,
            $requestHandler,
            $middlewares,
            $attributes
        );
    }
}
