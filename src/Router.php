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

use Fig\Http\Message\RequestMethodInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Dictionary\ErrorSource;
use Sunrise\Http\Router\Event\RouteMatchedEvent;
use Sunrise\Http\Router\Exception\Http\HttpMethodNotAllowedException;
use Sunrise\Http\Router\Exception\Http\HttpNotFoundException;
use Sunrise\Http\Router\Exception\Http\HttpUnsupportedMediaTypeException;
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutioner;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolving\ParameterResolver\ParameterResolverInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;

use Sunrise\Http\Router\ResponseResolving\ResponseResolutioner;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutionerInterface;
use Sunrise\Http\Router\ResponseResolving\ResponseResolver\ResponseResolverInterface;
use function array_keys;
use function sprintf;

/**
 * Router
 */
class Router implements RequestHandlerInterface, RequestMethodInterface
{

    /**
     * @var array<string, string>
     *
     * @since 2.9.0
     */
    public static array $patterns = [
        '@slug' => '[0-9a-z-]+',
        '@uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
    ];

    /**
     * @var EventDispatcherInterface|null
     */
    private ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @var ReferenceResolverInterface
     */
    private ReferenceResolverInterface $referenceResolver;

    /**
     * @var ParameterResolutionerInterface|null
     */
    private ?ParameterResolutionerInterface $parameterResolutioner;

    /**
     * @var ResponseResolutionerInterface|null
     */
    private ?ResponseResolutionerInterface $responseResolutioner;

    /**
     * @var RouteCollectionInterface
     */
    private RouteCollectionInterface $routes;

    /**
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * Constructor of the class
     *
     * @param RouteCollectionFactoryInterface|null $collectionFactory
     * @param ReferenceResolverInterface|null $referenceResolver
     * @param ParameterResolutionerInterface|null $parameterResolutioner
     * @param ResponseResolutionerInterface|null $responseResolutioner
     *
     * @since 3.0.0
     */
    public function __construct(
        ?RouteCollectionFactoryInterface $collectionFactory = null,
        ?ReferenceResolverInterface $referenceResolver = null,
        ?ParameterResolutionerInterface $parameterResolutioner = null,
        ?ResponseResolutionerInterface $responseResolutioner = null,
    ) {
        $collectionFactory ??= new RouteCollectionFactory();

        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;

        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver(
            $this->parameterResolutioner ??= new ParameterResolutioner(),
            $this->responseResolutioner ??= new ResponseResolutioner(),
        );

        $this->routes = $collectionFactory->createCollection();
    }

    /**
     * Adds the given parameter resolver(s) to the parameter resolutioner
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return void
     *
     * @throws LogicException
     *         If a custom reference resolver has been set,
     *         but a parameter resolutioner has not been set.
     *
     * @since 3.0.0
     */
    public function addParameterResolver(ParameterResolverInterface ...$resolvers): void
    {
        if (!isset($this->parameterResolutioner)) {
            throw new LogicException(
                'The router cannot accept parameter resolvers ' .
                'because a custom reference resolver has been set, ' .
                'but a parameter resolutioner has not been set.'
            );
        }

        $this->parameterResolutioner->addResolver(...$resolvers);
    }

    /**
     * Adds the given response resolver(s) to the response resolutioner
     *
     * @param ResponseResolverInterface ...$resolvers
     *
     * @return void
     *
     * @throws LogicException
     *         If a custom reference resolver has been set,
     *         but a response resolutioner has not been set.
     *
     * @since 3.0.0
     */
    public function addResponseResolver(ResponseResolverInterface ...$resolvers): void
    {
        if (!isset($this->responseResolutioner)) {
            throw new LogicException(
                'The router cannot accept response resolvers ' .
                'because a custom reference resolver has been set, ' .
                'but a response resolutioner has not been set.'
            );
        }

        $this->responseResolutioner->addResolver(...$resolvers);
    }

    /**
     * Loads routes using the given loaders
     *
     * @param LoaderInterface ...$loaders
     *
     * @return void
     */
    public function load(LoaderInterface ...$loaders): void
    {
        foreach ($loaders as $loader) {
            $this->routes->add(...$loader->load()->all());
        }
    }

    /**
     * Gets the router's routes
     *
     * @return RouteCollectionInterface
     */
    public function getRoutes(): RouteCollectionInterface
    {
        return $this->routes;
    }

    /**
     * Gets a route by its given name
     *
     * @param string $name
     *
     * @return RouteInterface
     *
     * @throws InvalidArgumentException If the route doesn't exist.
     */
    public function getRoute(string $name): RouteInterface
    {
        if ($this->routes->has($name)) {
            return $this->routes->get($name);
        }

        throw new InvalidArgumentException(sprintf('The route %s does not exist.', $name));
    }

    /**
     * Gets the router's middlewares
     *
     * @return list<MiddlewareInterface>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Gets the router's event dispatcher
     *
     * @return EventDispatcherInterface|null
     *
     * @since 2.13.0
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * Adds the given patterns to the router
     *
     * @param array<string, string> $patterns
     *
     * @return void
     *
     * @since 2.11.0
     */
    public function addPatterns(array $patterns): void
    {
        foreach ($patterns as $alias => $pattern) {
            self::$patterns[$alias] = $pattern;
        }
    }

    /**
     * Adds the given route(s) to the router
     *
     * @param RouteInterface ...$routes
     *
     * @return void
     */
    public function addRoute(RouteInterface ...$routes) : void
    {
        $this->routes->add(...$routes);
    }

    /**
     * Adds the given middleware(s) to the router
     *
     * @param mixed ...$middlewares
     *
     * @return void
     */
    public function addMiddleware(mixed ...$middlewares): void
    {
        $middlewares = $this->referenceResolver->resolveMiddlewares($middlewares);

        $this->middlewares = [...$this->middlewares, ...$middlewares];
    }

    /**
     * Sets the given event dispatcher to the router
     *
     * @param EventDispatcherInterface|null $eventDispatcher
     *
     * @return void
     *
     * @since 2.13.0
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Generates a URI for the given named route
     *
     * @param string $name
     * @param array<string, string> $attributes
     * @param bool $strict
     *
     * @return string
     */
    public function generateUri(string $name, array $attributes = [], bool $strict = false): string
    {
        $route = $this->routes->get($name);

        $attributes += $route->getAttributes();

        return path_build($route->getPath(), $attributes, $strict);
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
        $requestPath = $request->getUri()->getPath();
        $requestMethod = $request->getMethod();
        $allowedMethods = [];

        foreach ($this->routes->all() as $route) {
            // https://github.com/sunrise-php/http-router/issues/50
            // https://tools.ietf.org/html/rfc7231#section-6.5.5
            if (!path_match($route->getPath(), $requestPath, $attributes)) {
                continue;
            }

            $routeMethods = [];
            foreach ($route->getMethods() as $routeMethod) {
                $routeMethods[$routeMethod] = true;
                $allowedMethods[$routeMethod] = true;
            }

            if (!isset($routeMethods[$requestMethod])) {
                continue;
            }

            $serverConsumesMediaTypes = $route->getConsumesMediaTypes();
            if (!empty($serverConsumesMediaTypes)) {
                ServerRequest::from($request)->clientProducesMediaType(...$serverConsumesMediaTypes) or
                    throw new HttpUnsupportedMediaTypeException($serverConsumesMediaTypes);
            }

            /** @var array<string, string> $attributes */

            return $route->withAddedAttributes($attributes);
        }

        if (!empty($allowedMethods)) {
            $allowedMethods = array_keys($allowedMethods);

            throw new HttpMethodNotAllowedException($allowedMethods);
        }

        throw (new HttpNotFoundException)
            ->setSource(ErrorSource::CLIENT_REQUEST_PATH);
    }

    /**
     * Runs the router
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     *
     * @since 2.8.0
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        // lazy resolving of the given request...
        $routing = new CallableRequestHandler(
            function (ServerRequestInterface $request): ResponseInterface {
                $route = $this->match($request);

                if (isset($this->eventDispatcher)) {
                    $event = new RouteMatchedEvent($route, $request);
                    $this->eventDispatcher->dispatch($event);
                    $request = $event->getRequest();
                }

                return $route->handle($request);
            }
        );

        if (empty($this->middlewares)) {
            return $routing->handle($request);
        }

        $handler = new QueueableRequestHandler($routing, ...$this->middlewares);

        return $handler->handle($request);
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->match($request);

        if (isset($this->eventDispatcher)) {
            $event = new RouteMatchedEvent($route, $request);
            $this->eventDispatcher->dispatch($event);
            $request = $event->getRequest();
        }

        if (empty($this->middlewares)) {
            return $route->handle($request);
        }

        $handler = new QueueableRequestHandler($route, ...$this->middlewares);

        return $handler->handle($request);
    }
}
