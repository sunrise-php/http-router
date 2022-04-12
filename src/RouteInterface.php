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
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use Reflector;

/**
 * RouteInterface
 */
interface RouteInterface extends RequestHandlerInterface
{

    /**
     * Request attribute name for the route instance
     *
     * @var string
     *
     * @since 2.11.0
     */
    public const ATTR_ROUTE = '@route';

    /**
     * Gets the route name
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Gets the route host
     *
     * @return string|null
     *
     * @since 2.6.0
     */
    public function getHost() : ?string;

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
     * Gets the route summary
     *
     * @return string
     *
     * @since 2.4.0
     */
    public function getSummary() : string;

    /**
     * Gets the route description
     *
     * @return string
     *
     * @since 2.4.0
     */
    public function getDescription() : string;

    /**
     * Gets the route tags
     *
     * @return string[]
     *
     * @since 2.4.0
     */
    public function getTags() : array;

    /**
     * Gets the route holder
     *
     * @return ReflectionClass|ReflectionMethod|ReflectionFunction
     *
     * @since 2.14.0
     */
    public function getHolder() : Reflector;

    /**
     * Sets the given name to the route
     *
     * @param string $name
     *
     * @return RouteInterface
     */
    public function setName(string $name) : RouteInterface;

    /**
     * Sets the given host to the route
     *
     * @param string|null $host
     *
     * @return RouteInterface
     *
     * @since 2.6.0
     */
    public function setHost(?string $host) : RouteInterface;

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
     * Sets the given summary to the route
     *
     * @param string $summary
     *
     * @return RouteInterface
     *
     * @since 2.4.0
     */
    public function setSummary(string $summary) : RouteInterface;

    /**
     * Sets the given description to the route
     *
     * @param string $description
     *
     * @return RouteInterface
     *
     * @since 2.4.0
     */
    public function setDescription(string $description) : RouteInterface;

    /**
     * Sets the given tag(s) to the route
     *
     * @param string ...$tags
     *
     * @return RouteInterface
     *
     * @since 2.4.0
     */
    public function setTags(string ...$tags) : RouteInterface;

    /**
     * Adds the given prefix to the route path
     *
     * @param string $prefix
     *
     * @return RouteInterface
     */
    public function addPrefix(string $prefix) : RouteInterface;

    /**
     * Adds the given suffix to the route path
     *
     * @param string $suffix
     *
     * @return RouteInterface
     */
    public function addSuffix(string $suffix) : RouteInterface;

    /**
     * Adds the given method(s) to the route
     *
     * @param string ...$methods
     *
     * @return RouteInterface
     */
    public function addMethod(string ...$methods) : RouteInterface;

    /**
     * Adds the given middleware(s) to the route
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteInterface
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : RouteInterface;

    /**
     * Returns the route clone with the given attributes
     *
     * This method MUST NOT change the state of the object.
     *
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function withAddedAttributes(array $attributes) : RouteInterface;
}
