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
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\MiddlewareAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;

/**
 * Import functions
 */
use function sprintf;

/**
 * ExceptionFactory
 */
class ExceptionFactory
{

    /**
     * Used by a routes loader when the given resource wasn't found as a file
     *
     * @param mixed $resource
     * @param array $context
     *
     * @return InvalidLoaderResourceException
     */
    public function invalidLoaderFileResource($resource, array $context = []) : InvalidLoaderResourceException
    {
        $context['resource'] = $resource;

        $message = sprintf('The resource "%s" is not found.', $resource);

        return new InvalidLoaderResourceException($message, $context);
    }

    /**
     * Used when the given method isn't allowed
     *
     * It is recommended to add a request instance to the context.
     *
     * @param string $method
     * @param array $allowed
     * @param array $context
     *
     * @return MethodNotAllowedException
     */
    public function methodNotAllowed(string $method, array $allowed, array $context = []) : MethodNotAllowedException
    {
        $context['method'] = $method;
        $context['allowed'] = $allowed;

        $message = sprintf('The method "%s" is not allowed.', $method);

        return new MethodNotAllowedException($message, $context);
    }

    /**
     * Used when a middleware with the given hash already exists in a stack
     *
     * It is recommended to add a middleware instance to the context.
     *
     * @param string $hash
     * @param array $context
     *
     * @return MiddlewareAlreadyExistsException
     */
    public function middlewareAlreadyExists(string $hash, array $context = []) : MiddlewareAlreadyExistsException
    {
        $context['hash'] = $hash;

        $message = sprintf('A middleware with the hash "%s" already exists.', $hash);

        return new MiddlewareAlreadyExistsException($message, $context);
    }

    /**
     * Used when a route with the given name already exists in a stack
     *
     * It is recommended to add a route instance to the context.
     *
     * @param string $name
     * @param array $context
     *
     * @return RouteAlreadyExistsException
     */
    public function routeAlreadyExists(string $name, array $context = []) : RouteAlreadyExistsException
    {
        $context['name'] = $name;

        $message = sprintf('A route with the name "%s" already exists.', $name);

        return new RouteAlreadyExistsException($message, $context);
    }

    /**
     * Used when trying to find a route by the given name
     *
     * @param string $name
     * @param array $context
     *
     * @return RouteNotFoundException
     */
    public function routeNotFoundByName(string $name, array $context = []) : RouteNotFoundException
    {
        $context['name'] = $name;

        $message = sprintf('No route found for the name "%s".', $name);

        return new RouteNotFoundException($message, $context);
    }

    /**
     * Used when trying to find a route by the given URI
     *
     * It is recommended to add a request instance to the context.
     *
     * @param string $uri
     * @param array $context
     *
     * @return RouteNotFoundException
     */
    public function routeNotFoundByUri(string $uri, array $context = []) : RouteNotFoundException
    {
        $context['uri'] = $uri;

        $message = sprintf('No route found for the URI "%s".', $uri);

        return new RouteNotFoundException($message, $context);
    }
}
