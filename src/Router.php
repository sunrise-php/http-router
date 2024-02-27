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
use Sunrise\Http\Router\Dictionary\AttributeName;
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
use function rawurldecode;
use function sprintf;

class Router
{
    private readonly ParameterResolver $parameterResolver;
    private readonly ResponseResolver $responseResolver;
    private readonly ReferenceResolver $referenceResolver;

    /**
     * @var array<string, Route>
     */
    private array $routes = [];

    /**
     * @var array<string, non-empty-string>
     */
    private array $patterns = [];

    /**
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * @var array<string, RequestHandlerInterface>
     */
    private array $routeRequestHandlers = [];

    private ?RequestHandlerInterface $routerRequestHandler = null;

    /**
     * @since 3.0.0
     */
    public function __construct()
    {
        $this->referenceResolver = new ReferenceResolver(
            $this->parameterResolver = new ParameterResolver(),
            $this->responseResolver = new ResponseResolver(),
        );
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
        return $this->routes[$name] ?? throw new InvalidArgumentException(sprintf(
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

    public function loadRoutes(LoaderInterface ...$loaders): void
    {
        foreach ($loaders as $loader) {
            foreach ($loader->load() as $route) {
                $this->routes[$route->getName()] = $route;
            }
        }
    }

    public function addMiddleware(mixed ...$middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $this->referenceResolver->resolveMiddleware($middleware);
        }
    }

    public function addParameterResolver(ParameterResolverInterface ...$resolvers): void
    {
        $this->parameterResolver->addResolver(...$resolvers);
    }

    public function addResponseResolver(ResponseResolverInterface ...$resolvers): void
    {
        $this->responseResolver->addResolver(...$resolvers);
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
            $routePattern = $this->compileRoutePattern($route);

            // https://github.com/sunrise-php/http-router/issues/50
            // https://tools.ietf.org/html/rfc7231#section-6.5.5
            if (!RouteMatcher::matchPattern($route->getPath(), $routePattern, $requestPath, $matches)) {
                continue;
            }

            if (!$route->listensMethod($request->getMethod())) {
                $allowedMethods += array_flip($route->getMethods());
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
            $request->withAttribute(AttributeName::ROUTER, $this)
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
            $request->withAttribute(AttributeName::ROUTE, $route)
        );
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
                return $this->runRoute($this->match($request), $request);
            }
        );

        if ($this->middlewares === []) {
            return $this->routerRequestHandler;
        }

        $this->routerRequestHandler = new QueueableRequestHandler($this->routerRequestHandler);
        foreach ($this->middlewares as $middleware) {
            $this->routerRequestHandler->enqueue($middleware);
        }

        return $this->routerRequestHandler;
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
            $this->routeRequestHandlers[$routeName]->enqueue($this->referenceResolver->resolveMiddleware($middleware));
        }

        return $this->routeRequestHandlers[$routeName];
    }

    /**
     * @param array<string, mixed> $values
     *
     * @throws InvalidArgumentException
     */
    public function buildRoutePath(Route|string $route, array $values = []): string
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
    public function compileRoutePattern(Route|string $route): string
    {
        if (! $route instanceof Route) {
            $route = $this->getRoute($route);
        }

        return $this->patterns[$route->getName()] ??= $route->getPattern() ?? RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
    }
}
