<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Annotation;

/**
 * Import classes
 */
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Exception\InvalidDescriptorArgumentException;
use Sunrise\Http\Router\RouteDescriptorInterface;

/**
 * Import functions
 */
use function array_key_exists;

/**
 * Annotation for a route description
 *
 * @Annotation
 *
 * @Target({"CLASS"})
 */
final class Route implements RouteDescriptorInterface
{

    /**
     * A route name
     *
     * @var string
     */
    private $name;

    /**
     * A route host
     *
     * @var null|string
     */
    private $host;

    /**
     * A route path
     *
     * @var string
     */
    private $path;

    /**
     * A route methods
     *
     * @var string[]
     */
    private $methods;

    /**
     * A route middlewares
     *
     * @var string[]
     */
    private $middlewares;

    /**
     * A route attributes
     *
     * @var array
     */
    private $attributes;

    /**
     * A route summary
     *
     * @var string
     */
    private $summary;

    /**
     * A route description
     *
     * @var string
     */
    private $description;

    /**
     * A route tags
     *
     * @var string[]
     */
    private $tags;

    /**
     * A route priority
     *
     * @var int
     */
    private $priority;

    /**
     * Constructor of the annotation
     *
     * @param array $params
     *
     * @throws InvalidDescriptorArgumentException
     */
    public function __construct(array $params)
    {
        $this->name = $this->extractName($params);
        $this->host = $this->extractHost($params);
        $this->path = $this->extractPath($params);
        $this->methods = $this->extractMethods($params);
        $this->middlewares = $this->extractMiddlewares($params);
        $this->attributes = $this->extractAttributes($params);
        $this->summary = $this->extractSummary($params);
        $this->description = $this->extractDescription($params);
        $this->tags = $this->extractTags($params);
        $this->priority = $this->extractPriority($params);
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
    public function getPriority() : int
    {
        return $this->priority;
    }

    /**
     * Extracts a route name from the given parameters
     *
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route name.
     */
    private function extractName(array $params) : string
    {
        $name = $params['name'] ?? '';

        InvalidDescriptorArgumentException::throwIfEmptyString(
            $name,
            '@Route.name must contain a non-empty string.'
        );

        return $name;
    }

    /**
     * Extracts a route host from the given parameters
     *
     * @param array $params
     *
     * @return null|string
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route host.
     */
    private function extractHost(array $params) : ?string
    {
        $host = $params['host'] ?? null;

        // isn't required parameter...
        if (null === $host) {
            return null;
        }

        InvalidDescriptorArgumentException::throwIfEmptyString(
            $host,
            '@Route.host must contain a non-empty string.'
        );

        return $host;
    }

    /**
     * Extracts a route path from the given parameters
     *
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route path.
     */
    private function extractPath(array $params) : string
    {
        $path = $params['path'] ?? '';

        InvalidDescriptorArgumentException::throwIfEmptyString(
            $path,
            '@Route.path must contain a non-empty string.'
        );

        return $path;
    }

    /**
     * Extracts a route method(s) from the given parameters
     *
     * @param array $params
     *
     * @return string[]
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route method(s).
     */
    private function extractMethods(array $params) : array
    {
        if (array_key_exists('method', $params)) {
            $params['methods'][] = $params['method'];
        }

        $methods = $params['methods'] ?? [];

        InvalidDescriptorArgumentException::throwIfEmptyArray(
            $methods,
            '@Route.methods must contain a non-empty array.'
        );

        foreach ($methods as $method) {
            InvalidDescriptorArgumentException::throwIfEmptyString(
                $method,
                '@Route.methods must contain non-empty strings.'
            );
        }

        return $methods;
    }

    /**
     * Extracts a route middlewares from the given parameters
     *
     * @param array $params
     *
     * @return string[]
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route middlewares.
     */
    private function extractMiddlewares(array $params) : array
    {
        $middlewares = $params['middlewares'] ?? null;

        // isn't required parameter...
        if (null === $middlewares) {
            return [];
        }

        InvalidDescriptorArgumentException::throwIfNotArray(
            $middlewares,
            '@Route.middlewares must contain an array.'
        );

        foreach ($middlewares as $middleware) {
            InvalidDescriptorArgumentException::throwIfNotImplemented(
                $middleware,
                MiddlewareInterface::class,
                '@Route.middlewares must contain middlewares.'
            );
        }

        return $middlewares;
    }

    /**
     * Extracts a route attributes from the given parameters
     *
     * @param array $params
     *
     * @return array
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route attributes.
     */
    private function extractAttributes(array $params) : array
    {
        $attributes = $params['attributes'] ?? null;

        // isn't required parameter...
        if (null === $attributes) {
            return [];
        }

        InvalidDescriptorArgumentException::throwIfNotArray(
            $attributes,
            '@Route.attributes must contain an array.'
        );

        return $attributes;
    }

    /**
     * Extracts a route summary from the given parameters
     *
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route summary.
     */
    private function extractSummary(array $params) : string
    {
        $summary = $params['summary'] ?? null;

        // isn't required parameter...
        if (null === $summary) {
            return '';
        }

        InvalidDescriptorArgumentException::throwIfNotString(
            $summary,
            '@Route.summary must contain a string.'
        );

        return $summary;
    }

    /**
     * Extracts a route description from the given parameters
     *
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route description.
     */
    private function extractDescription(array $params) : string
    {
        $description = $params['description'] ?? null;

        // isn't required parameter...
        if (null === $description) {
            return '';
        }

        InvalidDescriptorArgumentException::throwIfNotString(
            $description,
            '@Route.description must contain a string.'
        );

        return $description;
    }

    /**
     * Extracts a route tags from the given parameters
     *
     * @param array $params
     *
     * @return string[]
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route tags.
     */
    private function extractTags(array $params) : array
    {
        $tags = $params['tags'] ?? null;

        // isn't required parameter...
        if (null === $tags) {
            return [];
        }

        InvalidDescriptorArgumentException::throwIfNotArray(
            $tags,
            '@Route.tags must contain an array.'
        );

        foreach ($tags as $tag) {
            InvalidDescriptorArgumentException::throwIfEmptyString(
                $tag,
                '@Route.tags must contain non-empty strings.'
            );
        }

        return $tags;
    }

    /**
     * Extracts a route priority from the given parameters
     *
     * @param array $params
     *
     * @return int
     *
     * @throws InvalidDescriptorArgumentException
     *         If the given parameters contain an invalid route priority.
     */
    private function extractPriority(array $params) : int
    {
        $priority = $params['priority'] ?? null;

        // isn't required parameter...
        if (null === $priority) {
            return 0;
        }

        InvalidDescriptorArgumentException::throwIfNotInteger(
            $priority,
            '@Route.priority must contain an integer.'
        );

        return $priority;
    }
}
