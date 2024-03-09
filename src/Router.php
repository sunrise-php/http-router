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
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\Exception\InvalidRouteBuildingValueException;
use Sunrise\Http\Router\Exception\InvalidRouteMatchingSubjectException;
use Sunrise\Http\Router\Exception\InvalidRouteParsingSubjectException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
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

class Router implements RequestHandlerInterface
{
    private readonly RequestProcessorResolver $requestHandlerResolver;

    /**
     * @var array<string, RouteInterface>
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
     * @param LoaderInterface[] $loaders
     *
     * @since 3.0.0
     */
    public function __construct(
        /** @var MiddlewareInterface[] */
        private readonly array $middlewares = [],
        array $parameterResolvers = [],
        array $responseResolvers = [],
        array $loaders = [],
    ) {
        $this->requestHandlerResolver = new RequestProcessorResolver(
            new ParameterResolver($parameterResolvers),
            new ResponseResolver($responseResolvers),
        );

        foreach ($loaders as $loader) {
            $this->load($loader);
        }
    }

    /**
     * @return array<string, RouteInterface>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @throws RouteNotFoundException
     */
    public function getRoute(string $name): RouteInterface
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        return throw new RouteNotFoundException(sprintf(
            'The route %s does not exist.',
            $name,
        ));
    }

    public function addRoute(RouteInterface ...$routes): void
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
    public function match(ServerRequestInterface $request): RouteInterface
    {
        $requestPath = rawurldecode($request->getUri()->getPath());
        $allowedMethods = [];
        foreach ($this->routes as $route) {
            $routePattern = $this->compileRoute($route);

            try {
                if (!RouteMatcher::matchPattern($route->getPath(), $routePattern, $requestPath, $matches)) {
                    continue;
                }
            } catch (InvalidRouteMatchingSubjectException $e) {
                throw new HttpException($e->getMessage(), 400); // TODO
            }

            if (!$route->allowsMethod($request->getMethod())) {
                $allowedMethods += array_flip($route->getMethods());
                continue;
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
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getRouterRequestHandler()->handle($request);
    }

    /**
     * @since 3.0.0
     */
    public function runRoute(RouteInterface|string $route, ServerRequestInterface $request): ResponseInterface
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        foreach ($route->getAttributes() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request->withAttribute('@route', $route);

        return $this->getRouteRequestHandler($route)->handle($request);
    }

    /**
     * @throws InvalidRouteBuildingValueException
     * @throws InvalidRouteParsingSubjectException
     *
     * @since 3.0.0
     */
    public function buildRoute(RouteInterface|string $route, array $values = []): string
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        return RouteBuilder::buildRoute($route->getPath(), $values + $route->getAttributes());
    }

    /**
     * @return non-empty-string
     *
     * @throws InvalidRouteParsingSubjectException
     *
     * @since 3.0.0
     */
    public function compileRoute(RouteInterface|string $route): string
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        return $this->routePatterns[$route->getName()] ??= $route->getPattern() ?? RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
    }

    /**
     * @throws InvalidArgumentException If the route is associated with an unsupported request handler or middleware.
     *
     * @since 3.0.0
     */
    public function getRouteRequestHandler(RouteInterface|string $route): RequestHandlerInterface
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        $routeName = $route->getName();
        if (isset($this->routeRequestHandlers[$routeName])) {
            return $this->routeRequestHandlers[$routeName];
        }

        try {
            $this->routeRequestHandlers[$routeName] = $this->requestHandlerResolver->resolveRequestHandler($route->getRequestHandler());
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(sprintf(
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
                throw new InvalidArgumentException(sprintf(
                    'The route %s refers to an unsupported middleware.',
                    $routeName,
                ), previous: $e);
            }

            $this->routeRequestHandlers[$routeName]->enqueue($middleware);
        }

        return $this->routeRequestHandlers[$routeName];
    }

    /**
     * @throws InvalidArgumentException If the router is associated with an unsupported middleware.
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
                throw new InvalidArgumentException('The router refers to an unsupported middleware.', previous: $e);
            }

            $this->routerRequestHandler->enqueue($middleware);
        }

        return $this->routerRequestHandler;
    }
}
