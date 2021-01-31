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
use function is_array;
use function is_int;
use function is_string;
use function is_subclass_of;

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
        $this->name = $this->extractNameFromParams($params);
        $this->host = $this->extractHostFromParams($params);
        $this->path = $this->extractPathFromParams($params);
        $this->methods = $this->extractMethodsFromParams($params);
        $this->middlewares = $this->extractMiddlewaresFromParams($params);
        $this->attributes = $this->extractAttributesFromParams($params);
        $this->summary = $this->extractSummaryFromParams($params);
        $this->description = $this->extractDescriptionFromParams($params);
        $this->tags = $this->extractTagsFromParams($params);
        $this->priority = $this->extractPriorityFromParams($params);
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
    public function getHost() : ?string
    {
        return $this->host;
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
    public function getSummary() : string
    {
        return $this->summary;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * {@inheritDoc}
     */
    public function getTags() : array
    {
        return $this->tags;
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority() : int
    {
        return $this->priority;
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractNameFromParams(array $params) : string
    {
        $name = $params['name'] ?? '';

        if ('' === $name || !is_string($name)) {
            throw new InvalidDescriptorArgumentException('@Route.name must contain a non-empty string.');
        }

        return $name;
    }

    /**
     * @param array $params
     *
     * @return null|string
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractHostFromParams(array $params) : ?string
    {
        $host = $params['host'] ?? null;

        if (isset($host) && ('' === $host || !is_string($host))) {
            throw new InvalidDescriptorArgumentException('@Route.host must contain a non-empty string.');
        }

        return $host;
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractPathFromParams(array $params) : string
    {
        $path = $params['path'] ?? '';

        if ('' === $path || !is_string($path)) {
            throw new InvalidDescriptorArgumentException('@Route.path must contain a non-empty string.');
        }

        return $path;
    }

    /**
     * @param array $params
     *
     * @return string[]
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractMethodsFromParams(array $params) : array
    {
        $methods = $params['methods'] ?? [];

        if ([] === $methods || !is_array($methods)) {
            throw new InvalidDescriptorArgumentException('@Route.methods must contain a non-empty array.');
        }

        foreach ($methods as $value) {
            if ('' === $value || !is_string($value)) {
                throw new InvalidDescriptorArgumentException('@Route.methods must contain non-empty strings.');
            }
        }

        return $methods;
    }

    /**
     * @param array $params
     *
     * @return string[]
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractMiddlewaresFromParams(array $params) : array
    {
        $middlewares = $params['middlewares'] ?? [];

        if (!is_array($middlewares)) {
            throw new InvalidDescriptorArgumentException('@Route.middlewares must contain an array.');
        }

        foreach ($middlewares as $value) {
            if ('' === $value || !is_string($value) || !is_subclass_of($value, MiddlewareInterface::class)) {
                throw new InvalidDescriptorArgumentException(
                    '@Route.middlewares must contain the class names of existing middlewares.'
                );
            }
        }

        return $middlewares;
    }

    /**
     * @param array $params
     *
     * @return array
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractAttributesFromParams(array $params) : array
    {
        $attributes = $params['attributes'] ?? [];

        if (!is_array($attributes)) {
            throw new InvalidDescriptorArgumentException('@Route.attributes must contain an array.');
        }

        return $attributes;
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractSummaryFromParams(array $params) : string
    {
        $summary = $params['summary'] ?? '';

        if (!is_string($summary)) {
            throw new InvalidDescriptorArgumentException('@Route.summary must contain a string.');
        }

        return $summary;
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractDescriptionFromParams(array $params) : string
    {
        $description = $params['description'] ?? '';

        if (!is_string($description)) {
            throw new InvalidDescriptorArgumentException('@Route.description must contain a string.');
        }

        return $description;
    }

    /**
     * @param array $params
     *
     * @return string[]
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractTagsFromParams(array $params) : array
    {
        $tags = $params['tags'] ?? [];

        if (!is_array($tags)) {
            throw new InvalidDescriptorArgumentException('@Route.tags must contain an array.');
        }

        foreach ($tags as $value) {
            if ('' === $value || !is_string($value)) {
                throw new InvalidDescriptorArgumentException('@Route.tags must contain non-empty strings.');
            }
        }

        return $tags;
    }

    /**
     * @param array $params
     *
     * @return int
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function extractPriorityFromParams(array $params) : int
    {
        $priority = $params['priority'] ?? 0;

        if (!is_int($priority)) {
            throw new InvalidDescriptorArgumentException('@Route.priority must contain an integer.');
        }

        return $priority;
    }
}
