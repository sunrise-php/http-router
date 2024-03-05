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
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
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
    private readonly RequestHandlerResolver $requestHandlerResolver;

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

        $this->requestHandlerResolver = new RequestHandlerResolver(
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

    public function addRoute(Route ...$routes): void
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
     * @throws HttpException
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

            if (!$this->routeSupportsMethod($route, $request->getMethod())) {
                $allowedMethods += array_flip($route->getMethods());
                continue;
            }

            if (!ServerRequest::create($request)->clientProducesMediaType(...$route->getConsumedMediaTypes())) {
                throw HttpExceptionFactory::mediaTypeNotSupported()
                    ->addMessagePlaceholder('{{ media_type }}', ServerRequest::create($request)->getClientProducedMediaType())
                    ->addHeaderField('Accept', ...$route->getConsumedMediaTypes());
            }

            return $route->withAddedAttributes($matches);
        }

        if (!empty($allowedMethods)) {
            throw HttpExceptionFactory::methodNotAllowed()
                ->addMessagePlaceholder('{{ method }}', $request->getMethod())
                ->addHeaderField('Allow', ...array_keys($allowedMethods));
        }

        throw HttpExceptionFactory::resourceNotFound()
            ->addMessagePlaceholder('{{ resource }}', $requestPath);
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

        return $this->routePatterns[$route->getName()] ??= $route->getPattern() ?? RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
    }

    /**
     * @since 3.0.0
     */
    public function routeSupportsMethod(Route|string $route, string $method): bool
    {
        if (! $route instanceof Route) {
            $route = $this->getRoute($route);
        }

        $methods = $route->getMethods();
        if ($methods === []) {
            return true;
        }

        return in_array($method, $methods, true);
    }

    /**
     * @throws LogicException If the route is associated with an unsupported request handler or middleware.
     *
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

        try {
            $this->routeRequestHandlers[$routeName] = $this->requestHandlerResolver->resolveRequestHandler($route->getRequestHandler());
        } catch (InvalidArgumentException $e) {
            throw new LogicException(sprintf(
                'The route %s refers to an unsupported request handler.',
                $routeName,
            ), previous: $e);
        }

        $middlewares = $route->getMiddlewares();
        if ($middlewares === []) {
            return $this->routeRequestHandlers[$routeName];
        }

        $this->routeRequestHandlers[$routeName] = new QueueableRequestHandler($this->routeRequestHandlers[$routeName]);

        foreach ($middlewares as $middleware) {
            try {
                $middleware = $this->requestHandlerResolver->resolveMiddleware($middleware);
            } catch (InvalidArgumentException $e) {
                throw new LogicException(sprintf(
                    'The route %s refers to an unsupported middleware.',
                    $routeName,
                ), previous: $e);
            }

            $this->routeRequestHandlers[$routeName]->enqueue($middleware);
        }

        return $this->routeRequestHandlers[$routeName];
    }

    /**
     * @throws LogicException If the router is associated with an unsupported middleware.
     *
     * @since 3.0.0
     */
    public function getRouterRequestHandler(): RequestHandlerInterface
    {
        if (isset($this->routerRequestHandler)) {
            return $this->routerRequestHandler;
        }

        $this->routerRequestHandler = new CallableRequestHandler(
            fn(ServerRequestInterface $request): ResponseInterface => (
                $this->runRoute($this->match($request), $request)
            )
        );

        if ($this->middlewares === []) {
            return $this->routerRequestHandler;
        }

        $this->routerRequestHandler = new QueueableRequestHandler($this->routerRequestHandler);

        foreach ($this->middlewares as $middleware) {
            try {
                $middleware = $this->requestHandlerResolver->resolveMiddleware($middleware);
            } catch (InvalidArgumentException $e) {
                throw new LogicException('The router refers to an unsupported middleware.', previous: $e);
            }

            $this->routerRequestHandler->enqueue($middleware);
        }

        return $this->routerRequestHandler;
    }
}
