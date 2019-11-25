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
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;

/**
 * Import functions
 */
use function rtrim;
use function strtoupper;

/**
 * Route
 */
class Route implements RouteInterface
{

    /**
     * Server Request attribute name for the route name
     *
     * @var string
     */
    public const ATTR_NAME_FOR_ROUTE_NAME = '@route-name';

    /**
     * The route name
     *
     * @var string
     */
    private $name;

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
     * {@inheritDoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestHandler() : RequestHandlerInterface
    {
        return $this->requestHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function getMiddlewares() : array
    {
        return $this->middlewares;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name) : RouteInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPath(string $path) : RouteInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function setRequestHandler(RequestHandlerInterface $requestHandler) : RouteInterface
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMiddlewares(MiddlewareInterface ...$middlewares) : RouteInterface
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setAttributes(array $attributes) : RouteInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addPrefix(string $prefix) : RouteInterface
    {
        // https://github.com/sunrise-php/http-router/issues/26
        $prefix = rtrim($prefix, '/');

        $this->path = $prefix . $this->path;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addSuffix(string $suffix) : RouteInterface
    {
        $this->path .= $suffix;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addMethod(string ...$methods) : RouteInterface
    {
        foreach ($methods as $method) {
            $this->methods[] = strtoupper($method);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : RouteInterface
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $request = $request->withAttribute(self::ATTR_NAME_FOR_ROUTE_NAME, $this->name);

        foreach ($this->attributes as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $handler = new QueueableRequestHandler($this->requestHandler);
        $handler->add(...$this->middlewares);

        return $handler->handle($request);
    }
}
