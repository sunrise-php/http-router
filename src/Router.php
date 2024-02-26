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

use function array_keys;
use function count;
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
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * @var array<string, RequestHandlerInterface>
     */
    private array $requestHandlers = [];

    /**
     * @var array<string, string>
     */
    private array $routePatterns = [];

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
        $requestPath = $request->getUri()->getPath();
        $requestMethod = $request->getMethod();
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $routeName = $route->getName();
            $routePath = $route->getPath();

            $this->routePatterns[$routeName] ??= $route->getPattern() ?? RouteCompiler::compileRoute($routePath, $route->getPatterns());

            // https://github.com/sunrise-php/http-router/issues/50
            // https://tools.ietf.org/html/rfc7231#section-6.5.5
            if (!RouteMatcher::matchPattern($routePath, $this->routePatterns[$routeName], $requestPath, $matches)) {
                continue;
            }

            $routeMethods = [];
            foreach ($route->getMethods() as $routeMethod) {
                $routeMethods[$routeMethod] = true;
                $allowedMethods[$routeMethod] = true;
            }

            if (!empty($routeMethods) && !isset($routeMethods[$requestMethod])) {
                continue;
            }

            if (!ServerRequest::create($request)->clientProducesMediaType(...$route->getConsumedMediaTypes())) {
                throw new HttpUnsupportedMediaTypeException($route->getConsumedMediaTypes());
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
            $handler = new QueueableRequestHandler($handler);
            foreach ($this->middlewares as $middleware) {
                $handler->enqueue($middleware);
            }
        }

        return $handler->handle(
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

        $request = $request->withAttribute('@route', $route);
        foreach ($route->getAttributes() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $handler = &$this->requestHandlers[$route->getName()];

        if (!isset($handler)) {
            $handler = new QueueableRequestHandler(
                $this->referenceResolver->resolveRequestHandler($route->getRequestHandler())
            );

            foreach ($route->getMiddlewares() as $middleware) {
                $handler->enqueue($this->referenceResolver->resolveMiddleware($middleware));
            }
        }

        return $handler->handle($request);
    }

    /**
     * @param array<string, string> $values
     *
     * @throws InvalidArgumentException
     */
    public function generateUri(string $name, array $values = []): string
    {
        $route = $this->getRoute($name);
        $values += $route->getAttributes();

        return RouteBuilder::buildRoute($route->getPath(), $values);
    }
}
