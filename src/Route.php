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
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Import functions
 */
use function strtoupper;

/**
 * Route
 */
class Route implements RouteInterface
{

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
    public function withAttributes(array $attributes) : RouteInterface
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
    public function buildPath(array $attributes = [], bool $strict = false) : string
    {
        $attributes += $this->attributes;

        return path_uri($this->path, $attributes, $strict);
    }
}
