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

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\Http\HttpMethodNotAllowedException;
use Sunrise\Http\Router\Exception\Http\HttpNotFoundException;
use Sunrise\Http\Router\Exception\Http\HttpUnsupportedMediaTypeException;
use Sunrise\Http\Router\Helper\RouteBuilder;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Helper\RouteMatcher;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutioner;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutionerInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutioner;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutionerInterface;

use function array_keys;
use function count;
use function sprintf;

class Router
{
    private readonly ReferenceResolver $referenceResolver;

    /**
     * @var array<string, RouteInterface>
     */
    private array $routes = [];

    /**
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * @var array<string, RequestHandlerInterface>
     */
    private array $requestHandlers = [];

    /**
     * @since 3.0.0
     */
    public function __construct(
        ParameterResolutionerInterface $parameterResolutioner = new ParameterResolutioner(),
        ResponseResolutionerInterface $responseResolutioner = new ResponseResolutioner(),
    ) {
        $this->referenceResolver = new ReferenceResolver($parameterResolutioner, $responseResolutioner);
    }

    /**
     * @return array<string, RouteInterface>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @throws InvalidArgumentException If the route doesn't exist.
     */
    public function getRoute(string $name): RouteInterface
    {
        return $this->routes[$name] ?? throw new InvalidArgumentException(sprintf(
            'The route %s does not exist.',
            $name,
        ));
    }

    public function addRoute(RouteInterface ...$routes) : void
    {
        foreach ($routes as $route) {
            $this->routes[$route->getName()] = $route;
        }
    }

    public function load(LoaderInterface ...$loaders): void
    {
        foreach ($loaders as $loader) {
            foreach ($loader->load() as $route) {
                $this->routes[$route->getName()] = $route;
            }
        }
    }

    public function addMiddleware(mixed ...$middlewares): void
    {
        $middlewares = $this->referenceResolver->resolveMiddlewares($middlewares);
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }
    }

    /**
     * Looks for a route that matches the given request
     *
     * @param ServerRequestInterface $request
     *
     * @return RouteInterface
     *
     * @throws HttpNotFoundException
     *         If the request URI isn't served.
     *
     * @throws HttpMethodNotAllowedException
     *         If the request method isn't allowed.
     *
     * @throws HttpUnsupportedMediaTypeException
     *         If the client not produces required media types.
     */
    public function match(ServerRequestInterface $request): RouteInterface
    {
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $path = $route->getPath();
            $pattern = $route->getPattern();
            if ($pattern === null) {
                $pattern = RouteCompiler::compileRoute($path, $route->getConstraints());
                $route->setPattern($pattern);
            }

            // https://github.com/sunrise-php/http-router/issues/50
            // https://tools.ietf.org/html/rfc7231#section-6.5.5
            if (!RouteMatcher::matchPattern($path, $pattern, $request->getUri()->getPath(), $matches)) {
                continue;
            }

            $routeMethods = [];
            foreach ($route->getMethods() as $routeMethod) {
                $routeMethods[$routeMethod] = true;
                $allowedMethods[$routeMethod] = true;
            }

            if (!isset($routeMethods[$request->getMethod()])) {
                continue;
            }

            $serverConsumedMediaTypes = $route->getConsumedMediaTypes();
            if (!ServerRequest::create($request)->clientProducesMediaType(...$serverConsumedMediaTypes)) {
                throw new HttpUnsupportedMediaTypeException($serverConsumedMediaTypes);
            }

            return $route->withAddedAttributes($matches);
        }

        if (!empty($allowedMethods)) {
            $allowedMethods = array_keys($allowedMethods);

            throw new HttpMethodNotAllowedException($allowedMethods);
        }

        throw new HttpNotFoundException();
    }

    /**
     * @since 2.8.0
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $handler = new CallableRequestHandler(
            fn(ServerRequestInterface $request): ResponseInterface => $this->runRoute($this->match($request), $request)
        );

        if (count($this->middlewares) > 0) {
            $handler = new QueueableRequestHandler($handler, $this->middlewares);
        }

        return $handler->handle($request->withAttribute('@router', $this));
    }

    /**
     * @since 3.0.0
     */
    public function runRoute(RouteInterface|string $route, ServerRequestInterface $request): ResponseInterface
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        $request = $request->withAttribute('@route', $route);
        foreach ($route->getAttributes() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $this->requestHandlers[$route->getName()] ??= new QueueableRequestHandler(
            $this->referenceResolver->resolveRequestHandler($route->getRequestHandler()),
            [...$this->referenceResolver->resolveMiddlewares($route->getMiddlewares())],
        );

        return $this->requestHandlers[$route->getName()]->handle($request);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function generateUri(string $name, array $values = []): string
    {
        $route = $this->getRoute($name);
        $values += $route->getAttributes();

        return RouteBuilder::buildRoute($route->getPath(), $values);
    }
}
