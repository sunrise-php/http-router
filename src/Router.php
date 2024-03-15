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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\Exception\InvalidReferenceException;
use Sunrise\Http\Router\Exception\InvalidRouteBuildingValueException;
use Sunrise\Http\Router\Exception\InvalidRouteMatchingPatternException;
use Sunrise\Http\Router\Exception\InvalidRouteMatchingSubjectException;
use Sunrise\Http\Router\Exception\InvalidRouteParsingSubjectException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Helper\RouteBuilder;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Helper\RouteMatcher;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;

use function array_flip;
use function array_keys;
use function rawurldecode;
use function sprintf;

class Router implements RequestHandlerInterface
{
    private readonly ReferenceResolverInterface $referenceResolver;

    private array $middlewares;

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

    private ?RequestHandlerInterface $requestHandler = null;

    /**
     * @param LoaderInterface[] $loaders
     * @param MiddlewareInterface[] $middlewares
     *
     * @since 3.0.0
     */
    public function __construct(
        array $loaders = [],
        array $middlewares = [],
        ReferenceResolverInterface $referenceResolver = null,
    ) {
        foreach ($loaders as $loader) {
            $this->load($loader);
        }

        $this->middlewares = $middlewares;

        $this->referenceResolver = $referenceResolver ?? ReferenceResolver::build();
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

        throw new RouteNotFoundException(sprintf('The route %s does not exist.', $name));
    }

    public function hasRoute(string $name): bool
    {
        return isset($this->routes[$name]);
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
            $this->addRoute(...$loader->load());
        }
    }

    /**
     * @throws HttpExceptionInterface
     * @throws InvalidRouteMatchingPatternException
     * @throws InvalidRouteParsingSubjectException
     */
    public function match(ServerRequestInterface $request): RouteInterface
    {
        $requestPath = rawurldecode($request->getUri()->getPath());
        $allowedMethods = [];
        foreach ($this->routes as $route) {
            $routePattern = $this->compileRoute($route);

            try {
                $isRouteMatched = RouteMatcher::matchPattern($route->getPath(), $routePattern, $requestPath, $matches);
            } catch (InvalidRouteMatchingSubjectException $e) {
                throw HttpExceptionFactory::invalidUri(previous: $e);
            }

            if (!$isRouteMatched) {
                continue;
            }

            if (!$route->allowsMethod($request->getMethod())) {
                $allowedMethods += array_flip($route->getMethods());
                continue;
            }

            return $route->withAddedAttributes($matches);
        }

        if (!empty($allowedMethods)) {
            throw HttpExceptionFactory::methodNotAllowed()
                ->addMessagePlaceholder('{{ request_method }}', $request->getMethod())
                ->addHeaderField('Allow', ...array_keys($allowedMethods));
        }

        throw HttpExceptionFactory::resourceNotFound()
            ->addMessagePlaceholder('{{ request_uri }}', $requestPath);
    }

    /**
     * @inheritDoc
     *
     * @throws HttpExceptionInterface
     * @throws InvalidReferenceException
     * @throws InvalidRouteMatchingPatternException
     * @throws InvalidRouteParsingSubjectException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getRequestHandler()->handle($request);
    }

    /**
     * @throws RouteNotFoundException
     * @throws InvalidReferenceException
     *
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

        return $this->getRouteRequestHandler($route)->handle(
            $request->withAttribute('@route', $route)
        );
    }

    /**
     * @throws RouteNotFoundException
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
     * @throws RouteNotFoundException
     * @throws InvalidRouteBuildingValueException
     * @throws InvalidRouteParsingSubjectException
     *
     * @since 3.0.0
     */
    public function strictBuildRoute(RouteInterface|string $route, array $values = []): string
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        $result = $this->buildRoute($route, $values);

        if (!RouteMatcher::matchPattern($route->getPath(), $this->compileRoute($route), $result)) {
            throw new InvalidRouteBuildingValueException(sprintf(
                'The route %s could not be built because one of the values does not match its pattern.',
                $route->getName(),
            ));
        }

        return $result;
    }

    /**
     * @return non-empty-string
     *
     * @throws RouteNotFoundException
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
     * @throws RouteNotFoundException
     * @throws InvalidReferenceException
     *
     * @since 3.0.0
     */
    public function getRouteRequestHandler(RouteInterface|string $route): RequestHandlerInterface
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        $name = $route->getName();
        if (isset($this->routeRequestHandlers[$name])) {
            return $this->routeRequestHandlers[$name];
        }

        $this->routeRequestHandlers[$name] = $this->referenceResolver->resolveRequestHandler($route->getRequestHandler());

        $middlewares = $route->getMiddlewares();
        if (!empty($middlewares)) {
            $this->routeRequestHandlers[$name] = new QueueableRequestHandler($this->routeRequestHandlers[$name]);
            foreach ($middlewares as $middleware) {
                $this->routeRequestHandlers[$name]->enqueue($this->referenceResolver->resolveMiddleware($middleware));
            }
        }

        return $this->routeRequestHandlers[$name];
    }

    /**
     * @throws InvalidReferenceException
     *
     * @since 3.0.0
     */
    public function getRequestHandler(): RequestHandlerInterface
    {
        if (isset($this->requestHandler)) {
            return $this->requestHandler;
        }

        $this->requestHandler = new CallableRequestHandler(
            fn(ServerRequestInterface $request): ResponseInterface => (
                $this->runRoute($this->match($request), $request)
            )
        );

        if (!empty($this->middlewares)) {
            $this->requestHandler = new QueueableRequestHandler($this->requestHandler);
            foreach ($this->middlewares as $middleware) {
                $this->requestHandler->enqueue($this->referenceResolver->resolveMiddleware($middleware));
            }
        }

        return $this->requestHandler;
    }
}
