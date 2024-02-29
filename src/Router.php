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
use Sunrise\Http\Router\ParameterResolver\ParameterResolverInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Sunrise\Http\Router\ResponseResolver\ResponseResolverInterface;

use function array_flip;
use function array_keys;
use function in_array;
use function rawurldecode;
use function sprintf;

class Router
{
    private readonly ReferenceResolver $referenceResolver;

    private array $middlewares;

    /**
     * @var array<string, Route>
     */
    private array $routes = [];

    /**
     * @var array<string, non-empty-string>
     */
    private array $routePatterns = [];

    /**
     * @var array<string, RequestHandlerInterface>
     */
    private array $routeRequestHandlers = [];

    private ?RequestHandlerInterface $routerRequestHandler = null;

    /**
     * @param ParameterResolverInterface[] $parameterResolvers
     * @param ResponseResolverInterface[] $responseResolvers
     * @param list<LoaderInterface> $loaders
     *
     * @since 3.0.0
     */
    public function __construct(
        array $middlewares = [],
        array $parameterResolvers = [],
        array $responseResolvers = [],
        array $loaders = [],
    ) {
        $this->middlewares = $middlewares;

        $this->referenceResolver = new ReferenceResolver(
            new ParameterResolver($parameterResolvers),
            new ResponseResolver($responseResolvers),
        );

        $this->load(...$loaders);
    }

    /**
     * @return array<string, Route>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @throws InvalidArgumentException If the route doesn't exist.
     */
    public function getRoute(string $name): Route
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        return throw new InvalidArgumentException(sprintf(
            'The route %s does not exist.',
            $name,
        ));
    }

    public function addRoute(Route ...$routes) : void
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

    /**
     * Looks for a route that matches the given request
     *
     * @param ServerRequestInterface $request
     *
     * @return Route
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
    public function match(ServerRequestInterface $request): Route
    {
        $requestPath = rawurldecode($request->getUri()->getPath());
        $allowedMethods = [];
        foreach ($this->routes as $route) {
            $routePattern = $this->compileRoute($route);

            // https://github.com/sunrise-php/http-router/issues/50
            // https://tools.ietf.org/html/rfc7231#section-6.5.5
            if (!RouteMatcher::matchPattern($route->getPath(), $routePattern, $requestPath, $matches)) {
                continue;
            }

            $routeMethods = $route->getMethods();
            if (!empty($routeMethods) && !in_array($request->getMethod(), $routeMethods, true)) {
                $allowedMethods += array_flip($routeMethods);
                continue;
            }

            if (!ServerRequest::create($request)->clientProducesMediaType(...$route->getConsumedMediaTypes())) {
                throw new HttpUnsupportedMediaTypeException($route->getConsumedMediaTypes());
            }

            return $route->withAddedAttributes($matches);
        }

        if (!empty($allowedMethods)) {
            throw new HttpMethodNotAllowedException(array_keys($allowedMethods));
        }

        throw new HttpNotFoundException();
    }

    /**
     * @since 2.8.0
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getRouterRequestHandler()->handle(
            $request->withAttribute('@router', $this)
        );
    }

    /**
     * @since 3.0.0
     */
    public function runRoute(Route|string $route, ServerRequestInterface $request): ResponseInterface
    {
        if (! $route instanceof Route) {
            $route = $this->getRoute($route);
        }

        foreach ($route->getAttributes() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $this->getRouteRequestHandler($route)->handle(
            $request->withAttribute('@route', $route)
        );
    }

    /**
     * @param array<string, mixed> $values
     *
     * @throws InvalidArgumentException
     */
    public function buildRoute(Route|string $route, array $values = []): string
    {
        if (! $route instanceof Route) {
            $route = $this->getRoute($route);
        }

        return RouteBuilder::buildRoute($route->getPath(), $values + $route->getAttributes());
    }

    /**
     * @return non-empty-string
     *
     * @throws InvalidArgumentException
     */
    public function compileRoute(Route|string $route): string
    {
        if (! $route instanceof Route) {
            $route = $this->getRoute($route);
        }

        // phpcs:ignore Generic.Files.LineLength
        return $this->routePatterns[$route->getName()] ??= $route->getPattern() ?? RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
    }

    /**
     * @since 3.0.0
     */
    public function getRouteRequestHandler(Route|string $route): RequestHandlerInterface
    {
        if (! $route instanceof Route) {
            $route = $this->getRoute($route);
        }

        $routeName = $route->getName();
        if (isset($this->routeRequestHandlers[$routeName])) {
            return $this->routeRequestHandlers[$routeName];
        }

        // phpcs:ignore Generic.Files.LineLength
        $this->routeRequestHandlers[$routeName] = $this->referenceResolver->resolveRequestHandler($route->getRequestHandler());

        $middlewares = $route->getMiddlewares();
        if ($middlewares === []) {
            return $this->routeRequestHandlers[$routeName];
        }

        $this->routeRequestHandlers[$routeName] = new QueueableRequestHandler($this->routeRequestHandlers[$routeName]);
        foreach ($middlewares as $middleware) {
            $this->routeRequestHandlers[$routeName]->enqueue(
                $this->referenceResolver->resolveMiddleware($middleware)
            );
        }

        return $this->routeRequestHandlers[$routeName];
    }

    /**
     * @since 3.0.0
     */
    public function getRouterRequestHandler(): RequestHandlerInterface
    {
        if (isset($this->routerRequestHandler)) {
            return $this->routerRequestHandler;
        }

        $this->routerRequestHandler = new CallableRequestHandler(
            function (ServerRequestInterface $request): ResponseInterface {
                $route = $this->match($request);

                return $this->runRoute($route, $request);
            }
        );

        if ($this->middlewares === []) {
            return $this->routerRequestHandler;
        }

        $this->routerRequestHandler = new QueueableRequestHandler($this->routerRequestHandler);
        foreach ($this->middlewares as $middleware) {
            $this->routerRequestHandler->enqueue(
                $this->referenceResolver->resolveMiddleware($middleware)
            );
        }

        return $this->routerRequestHandler;
    }
}
