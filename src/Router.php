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

use Psr\Container\ContainerInterface;
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
use Sunrise\Http\Router\ParameterResolver\DefaultValueParameterResolver;
use Sunrise\Http\Router\ParameterResolver\ObjectInjectionParameterResolver;
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
    private readonly RequestHandlerResolverInterface $requestHandlerResolver;

    private readonly MiddlewareResolverInterface $middlewareResolver;

    private ?RequestHandlerInterface $requestHandler = null;

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

    /**
     * @param MiddlewareInterface[] $middlewares
     * @param ParameterResolverInterface[] $parameterResolvers
     * @param ResponseResolverInterface[] $responseResolvers
     *
     * @since 3.0.0
     */
    public function __construct(
        private readonly array $middlewares = [],
        array $parameterResolvers = [],
        array $responseResolvers = [],
        ?ContainerInterface $container = null,
    ) {
        $parameterResolvers[] = new ObjectInjectionParameterResolver($this);
        $parameterResolvers[] = new DefaultValueParameterResolver();

        $parameterResolver = new ParameterResolverChain($parameterResolvers);
        $responseResolver = new ResponseResolverChain($responseResolvers);
        $classResolver = new ClassResolver($parameterResolver, $container);

        $this->requestHandlerResolver = new RequestHandlerResolver($classResolver, $parameterResolver, $responseResolver);
        $this->middlewareResolver = new MiddlewareResolver($classResolver, $parameterResolver, $responseResolver);
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
            foreach ($loader->load() as $route) {
                $this->routes[$route->getName()] = $route;
            }
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

        $this->routeRequestHandlers[$name] = $this->requestHandlerResolver->resolveRequestHandler($route->getRequestHandler());

        $middlewares = $route->getMiddlewares();
        if (!empty($middlewares)) {
            $this->routeRequestHandlers[$name] = new QueueableRequestHandler($this->routeRequestHandlers[$name]);
            foreach ($middlewares as $middleware) {
                $this->routeRequestHandlers[$name]->enqueue($this->middlewareResolver->resolveMiddleware($middleware));
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
                $this->requestHandler->enqueue($this->middlewareResolver->resolveMiddleware($middleware));
            }
        }

        return $this->requestHandler;
    }
}
