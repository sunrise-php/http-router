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
use Psr\Http\Server\RequestHandlerInterface;

/**
 * RouteInterface
 */
interface RouteInterface
{

    /**
     * Gets the route name
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Gets the route path
     *
     * @return string
     */
    public function getPath() : string;

    /**
     * Gets the route methods
     *
     * @return string[]
     */
    public function getMethods() : array;

    /**
     * Gets the route request handler
     *
     * @return RequestHandlerInterface
     */
    public function getRequestHandler() : RequestHandlerInterface;

    /**
     * Gets the route middlewares
     *
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares() : array;

    /**
     * Gets the route attributes
     *
     * @return array
     */
    public function getAttributes() : array;

    /**
     * Sets the given name to the route
     *
     * @param string $name
     *
     * @return RouteInterface
     */
    public function setName(string $name) : RouteInterface;

    /**
     * Sets the given path to the route
     *
     * @param string $path
     *
     * @return RouteInterface
     */
    public function setPath(string $path) : RouteInterface;

    /**
     * Sets the given method(s) to the route
     *
     * @param string ...$methods
     *
     * @return RouteInterface
     */
    public function setMethods(string ...$methods) : RouteInterface;

    /**
     * Sets the given request handler to the route
     *
     * @param RequestHandlerInterface $requestHandler
     *
     * @return RouteInterface
     */
    public function setRequestHandler(RequestHandlerInterface $requestHandler) : RouteInterface;

    /**
     * Sets the given middleware(s) to the route
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteInterface
     */
    public function setMiddlewares(MiddlewareInterface ...$middlewares) : RouteInterface;

    /**
     * Sets the given attributes to the route
     *
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function setAttributes(array $attributes) : RouteInterface;

    /**
     * Returns the route clone with the given attributes
     *
     * This method MUST NOT change the state of the object.
     *
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function withAttributes(array $attributes) : RouteInterface;

    /**
     * Builds the route path for the given attributes
     *
     * @param array $attributes
     * @param bool $strict
     *
     * @return string
     */
    public function buildPath(array $attributes, bool $strict) : string;
}
