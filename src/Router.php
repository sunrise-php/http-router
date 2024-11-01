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
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Event\RoutePostRunEvent;
use Sunrise\Http\Router\Event\RoutePreRunEvent;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\Helper\RouteBuilder;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Helper\RouteMatcher;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use UnexpectedValueException;

use function array_flip;
use function array_keys;
use function array_merge;
use function rawurldecode;
use function sprintf;

final class Router implements RouterInterface
{
    private readonly ReferenceResolverInterface $referenceResolver;

    private ?RequestHandlerInterface $requestHandler = null;

    /** @var array<string, RouteInterface> */
    private array $routes = [];

    /** @var array<string, non-empty-string> */
    private array $routePatterns = [];

    /** @var array<string, RequestHandlerInterface> */
    private array $routeRequestHandlers = [];

    private bool $isLoaded = false;

    /**
     * @since 3.0.0
     */
    public function __construct(
        /** @var array<array-key, LoaderInterface> */
        private readonly array $loaders = [],
        /** @var array<array-key, mixed> */
        private readonly array $middlewares = [],
        /** @var array<array-key, mixed> */
        private readonly array $routeMiddlewares = [],
        ?ReferenceResolverInterface $referenceResolver = null,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
        $this->referenceResolver = $referenceResolver ?? ReferenceResolver::build();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getRoutes(): array
    {
        $this->isLoaded or $this->load();

        return $this->routes;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getRoute(string $name): RouteInterface
    {
        $routes = $this->getRoutes();

        if (!isset($routes[$name])) {
            throw new InvalidArgumentException(sprintf('The route %s does not exist.', $name));
        }

        return $routes[$name];
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function hasRoute(string $name): bool
    {
        $routes = $this->getRoutes();

        return isset($routes[$name]);
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function addRoute(RouteInterface ...$routes): void
    {
        foreach ($routes as $route) {
            $name = $route->getName();

            if (isset($this->routes[$name])) {
                throw new InvalidArgumentException(sprintf('The route %s already exists.', $name));
            }

            $this->routes[$name] = $route;
        }
    }

    /**
     * @inheritDoc
     *
     * @throws HttpExceptionInterface
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function match(ServerRequestInterface $request): RouteInterface
    {
        $routes = $this->getRoutes();

        if ($routes === []) {
            throw new LogicException('The router does not contain any routes.');
        }

        $requestPath = rawurldecode($request->getUri()->getPath());
        $requestMethod = $request->getMethod();
        $allowedMethods = [];

        foreach ($routes as $route) {
            $routeRegex = $this->compileRoute($route);

            try {
                if (!RouteMatcher::matchPattern($route->getPath(), $routeRegex, $requestPath, $matches)) {
                    continue;
                }
            } catch (UnexpectedValueException $e) {
                throw HttpExceptionFactory::malformedUri(previous: $e);
            }

            if (!$route->allowsMethod($requestMethod)) {
                $allowedMethods += array_flip($route->getMethods());
                continue;
            }

            return $route->withAddedAttributes($matches);
        }

        if ($allowedMethods !== []) {
            throw HttpExceptionFactory::methodNotAllowed()
                ->addMessagePlaceholder('{{ request_method }}', $requestMethod)
                ->addHeaderField('Allow', ...array_keys($allowedMethods));
        }

        throw HttpExceptionFactory::resourceNotFound()
            ->addMessagePlaceholder('{{ request_uri }}', $requestPath);
    }

    /**
     * @inheritDoc
     *
     * @throws HttpExceptionInterface
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getRequestHandler()->handle($request);
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function runRoute(RouteInterface|string $route, ServerRequestInterface $request): ResponseInterface
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        /** @var mixed $value */
        foreach ($route->getAttributes() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request->withAttribute(RouteInterface::class, $route);

        if ($this->eventDispatcher !== null) {
            $event = new RoutePreRunEvent($route, $request);
            $this->eventDispatcher->dispatch($event);
            $request = $event->getRequest();
        }

        $response = $this->getRouteRequestHandler($route)->handle($request);

        if ($this->eventDispatcher !== null) {
            $event = new RoutePostRunEvent($route, $request, $response);
            $this->eventDispatcher->dispatch($event);
            $response = $event->getResponse();
        }

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function buildRoute(RouteInterface|string $route, array $values = [], bool $strictly = false): string
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        $result = RouteBuilder::buildRoute($route->getPath(), $values + $route->getAttributes());

        if ($strictly && !RouteMatcher::matchPattern($route->getPath(), $this->compileRoute($route), $result)) {
            throw new InvalidArgumentException(sprintf(
                'The route %s could not be built because one of the values does not match its pattern.',
                $route->getName(),
            ));
        }

        return $result;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function compileRoute(RouteInterface|string $route): string
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->getRoute($route);
        }

        return $this->routePatterns[$route->getName()] ??= $route->getPattern()
            ?? RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
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

        $this->routeRequestHandlers[$name] = $this->referenceResolver
            ->resolveRequestHandler($route->getRequestHandler());

        $middlewares = array_merge($this->routeMiddlewares, $route->getMiddlewares());
        if ($middlewares !== []) {
            $this->routeRequestHandlers[$name] = new QueueableRequestHandler($this->routeRequestHandlers[$name]);
            /** @var mixed $middleware */
            foreach ($middlewares as $middleware) {
                $this->routeRequestHandlers[$name]->enqueue($this->referenceResolver->resolveMiddleware($middleware));
            }
        }

        return $this->routeRequestHandlers[$name];
    }

    /**
     * @throws InvalidArgumentException
     *
     * @since 3.0.0
     */
    private function getRequestHandler(): RequestHandlerInterface
    {
        if ($this->requestHandler !== null) {
            return $this->requestHandler;
        }

        $this->requestHandler = new CallableRequestHandler(
            fn(ServerRequestInterface $request): ResponseInterface
                => $this->runRoute($this->match($request), $request),
        );

        if ($this->middlewares !== []) {
            $this->requestHandler = new QueueableRequestHandler($this->requestHandler);
            /** @var mixed $middleware */
            foreach ($this->middlewares as $middleware) {
                $this->requestHandler->enqueue($this->referenceResolver->resolveMiddleware($middleware));
            }
        }

        return $this->requestHandler;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @since 3.0.0 The method was removed from the contract.
     */
    private function load(): void
    {
        foreach ($this->loaders as $loader) {
            $this->addRoute(...$loader->load());
        }

        $this->isLoaded = true;
    }
}
