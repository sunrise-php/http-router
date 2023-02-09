<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
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
use ReflectionClass;
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
     * The route name
     *
     * @var string
     */
    private string $name;

    /**
     * The route host
     *
     * @var string|null
     */
    private ?string $host = null;

    /**
     * The route path
     *
     * @var string
     */
    private string $path;

    /**
     * The route methods
     *
     * @var list<string>
     */
    private array $methods = [];

    /**
     * The route's consumed content types
     *
     * @var list<string>
     */
    private array $consumedContentTypes = [];

    /**
     * The route's produced content types
     *
     * @var list<string>
     */
    private array $producedContentTypes = [];

    /**
     * The route request handler
     *
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $requestHandler;

    /**
     * The route middlewares
     *
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * The route attributes
     *
     * @var array<string, mixed>
     */
    private array $attributes = [];

    /**
     * The route summary
     *
     * @var string
     */
    private string $summary = '';

    /**
     * The route description
     *
     * @var string
     */
    private string $description = '';

    /**
     * The route tags
     *
     * @var list<string>
     */
    private array $tags = [];

    /**
     * Constructor of the class
     *
     * @param string $name
     * @param string $path
     * @param list<string> $methods
     * @param RequestHandlerInterface $requestHandler
     * @param list<MiddlewareInterface> $middlewares
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        string $name,
        string $path,
        array $methods,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->setMethods(...$methods);
        $this->requestHandler = $requestHandler;
        $this->setMiddlewares(...$middlewares);
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumedContentTypes(): array
    {
        return $this->consumedContentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducedContentTypes(): array
    {
        return $this->producedContentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestHandler(): RequestHandlerInterface
    {
        return $this->requestHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getHolder(): Reflector
    {
        if ($this->requestHandler instanceof CallableRequestHandler) {
            return $this->requestHandler->getReflection();
        }

        return new ReflectionClass($this->requestHandler);
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): RouteInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHost(?string $host): RouteInterface
    {
        $this->host = $host;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(string $path): RouteInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMethods(string ...$methods): RouteInterface
    {
        $this->methods = [];
        foreach ($methods as $method) {
            $this->methods[] = strtoupper($method);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConsumedContentTypes(string ...$contentTypes): RouteInterface
    {
        $this->consumedContentTypes = [];
        foreach ($contentTypes as $contentType) {
            $this->consumedContentTypes[] = $contentType;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProducedContentTypes(string ...$contentTypes): RouteInterface
    {
        $this->producedContentTypes = [];
        foreach ($contentTypes as $contentType) {
            $this->producedContentTypes[] = $contentType;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHandler(RequestHandlerInterface $requestHandler): RouteInterface
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlewares(MiddlewareInterface ...$middlewares): RouteInterface
    {
        $this->middlewares = [];
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes): RouteInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(string $name, $value): RouteInterface
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSummary(string $summary): RouteInterface
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(string $description): RouteInterface
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags(string ...$tags): RouteInterface
    {
        $this->tags = [];
        foreach ($tags as $tag) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPrefix(string $prefix): RouteInterface
    {
        // https://github.com/sunrise-php/http-router/issues/26
        $prefix = rtrim($prefix, '/');

        $this->path = $prefix . $this->path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSuffix(string $suffix): RouteInterface
    {
        $this->path .= $suffix;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethod(string ...$methods): RouteInterface
    {
        foreach ($methods as $method) {
            $this->methods[] = strtoupper($method);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addConsumedContentType(string ...$contentTypes): RouteInterface
    {
        foreach ($contentTypes as $contentType) {
            $this->consumedContentTypes[] = $contentType;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addProducedContentType(string ...$contentTypes): RouteInterface
    {
        foreach ($contentTypes as $contentType) {
            $this->producedContentTypes[] = $contentType;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): RouteInterface
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPriorityMiddleware(MiddlewareInterface ...$middlewares): RouteInterface
    {
        $newValue = [];

        foreach ($middlewares as $middleware) {
            $newValue[] = $middleware;
        }

        foreach ($this->middlewares as $middleware) {
            $newValue[] = $middleware;
        }

        $this->middlewares = $newValue;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTag(string ...$tags): RouteInterface
    {
        foreach ($tags as $tag) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedAttributes(array $attributes): RouteInterface
    {
        $clone = clone $this;

        /** @psalm-suppress MixedAssignment */
        foreach ($attributes as $key => $value) {
            $clone->attributes[$key] = $value;
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $request = $request->withAttribute(self::ATTR_ROUTE, $this);

        /** @psalm-suppress MixedAssignment */
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
