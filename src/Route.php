<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use Reflector;

/**
 * Import functions
 */
use function rtrim;
use function strtoupper;

/**
 * Route
 *
 * Use the {@see RouteFactory} factory to create this class.
 */
class Route implements RouteInterface
{

    /**
     * Server Request attribute name for the route
     *
     * @var string
     *
     * @deprecated 2.11.0 Use the RouteInterface::ATTR_ROUTE constant.
     */
    public const ATTR_NAME_FOR_ROUTE = self::ATTR_ROUTE;

    /**
     * Server Request attribute name for the route name
     *
     * @var string
     *
     * @deprecated 2.9.0
     */
    public const ATTR_NAME_FOR_ROUTE_NAME = '@route-name';

    /**
     * The route name
     *
     * @var string
     */
    private $name;

    /**
     * The route host
     *
     * @var string|null
     */
    private $host = null;

    /**
     * The route path
     *
     * @var string
     */
    private $path;

    /**
     * The route methods
     *
     * @var string[]
     */
    private $methods;

    /**
     * The route request handler
     *
     * @var RequestHandlerInterface
     */
    private $requestHandler;

    /**
     * The route middlewares
     *
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /**
     * The route attributes
     *
     * @var array
     */
    private $attributes = [];

    /**
     * The route summary
     *
     * @var string
     */
    private $summary = '';

    /**
     * The route description
     *
     * @var string
     */
    private $description = '';

    /**
     * The route tags
     *
     * @var string[]
     */
    private $tags = [];

    /**
     * Constructor of the class
     *
     * @param string $name
     * @param string $path
     * @param string[] $methods
     * @param RequestHandlerInterface $requestHandler
     * @param MiddlewareInterface[] $middlewares
     * @param array $attributes
     */
    public function __construct(
        string $name,
        string $path,
        array $methods,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) {
        $this->setName($name);
        $this->setPath($path);
        $this->setMethods(...$methods);
        $this->setRequestHandler($requestHandler);
        $this->setMiddlewares(...$middlewares);
        $this->setAttributes($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost() : ?string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestHandler() : RequestHandlerInterface
    {
        return $this->requestHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewares() : array
    {
        return $this->middlewares;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary() : string
    {
        return $this->summary;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags() : array
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getHolder() : Reflector
    {
        $handler = $this->requestHandler;
        if ($handler instanceof CallableRequestHandler) {
            $callback = $handler->getCallback();
            if ($callback instanceof Closure) {
                return new ReflectionFunction($callback);
            }

            /** @var array{0: class-string|object, 1: string} $callback */

            return new ReflectionMethod(...$callback);
        }

        return new ReflectionClass($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name) : RouteInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHost(?string $host) : RouteInterface
    {
        $this->host = $host;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(string $path) : RouteInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMethods(string ...$methods) : RouteInterface
    {
        foreach ($methods as &$method) {
            $method = strtoupper($method);
        }

        $this->methods = $methods;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHandler(RequestHandlerInterface $requestHandler) : RouteInterface
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlewares(MiddlewareInterface ...$middlewares) : RouteInterface
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes) : RouteInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSummary(string $summary) : RouteInterface
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(string $description) : RouteInterface
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags(string ...$tags) : RouteInterface
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPrefix(string $prefix) : RouteInterface
    {
        // https://github.com/sunrise-php/http-router/issues/26
        $prefix = rtrim($prefix, '/');

        $this->path = $prefix . $this->path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSuffix(string $suffix) : RouteInterface
    {
        $this->path .= $suffix;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethod(string ...$methods) : RouteInterface
    {
        foreach ($methods as $method) {
            $this->methods[] = strtoupper($method);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : RouteInterface
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedAttributes(array $attributes) : RouteInterface
    {
        $clone = clone $this;

        foreach ($attributes as $key => $value) {
            $clone->attributes[$key] = $value;
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $request = $request->withAttribute(self::ATTR_ROUTE, $this);

        /** @todo Must be removed from the 3.0.0 version */
        $request = $request->withAttribute(self::ATTR_NAME_FOR_ROUTE_NAME, $this->name);

        foreach ($this->attributes as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        if (empty($this->middlewares)) {
            return $this->requestHandler->handle($request);
        }

        $handler = new QueueableRequestHandler($this->requestHandler);
        $handler->add(...$this->middlewares);

        return $handler->handle($request);
    }
}
