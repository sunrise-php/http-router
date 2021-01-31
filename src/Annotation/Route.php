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
        if (!isset($params['name']) || '' === $params['name'] || !is_string($params['name'])) {
            throw new InvalidDescriptorArgumentException('@Route.name must contain a non-empty string.');
        }

        return $params['name'];
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
        if (isset($params['host']) && ('' === $params['host'] || !is_string($params['host']))) {
            throw new InvalidDescriptorArgumentException('@Route.host must contain a non-empty string.');
        }

        return $params['host'] ?? null;
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
        if (!isset($params['path']) || '' === $params['path'] || !is_string($params['path'])) {
            throw new InvalidDescriptorArgumentException('@Route.path must contain a non-empty string.');
        }

        return $params['path'];
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
        if (!isset($params['methods']) || [] === $params['methods'] || !is_array($params['methods'])) {
            throw new InvalidDescriptorArgumentException('@Route.methods must contain a non-empty array.');
        }

        foreach ($params['methods'] as $value) {
            if ('' === $value || !is_string($value)) {
                throw new InvalidDescriptorArgumentException('@Route.methods must contain non-empty strings.');
            }
        }

        return $params['methods'];
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
        if (!isset($params['middlewares'])) {
            return [];
        }

        if (!is_array($params['middlewares'])) {
            throw new InvalidDescriptorArgumentException('@Route.middlewares must contain an array.');
        }

        foreach ($params['middlewares'] as $value) {
            if ('' === $value || !is_string($value) || !is_subclass_of($value, MiddlewareInterface::class)) {
                throw new InvalidDescriptorArgumentException(
                    '@Route.middlewares must contain the class names of existing middlewares.'
                );
            }
        }

        return $params['middlewares'];
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
        if (!isset($params['attributes'])) {
            return [];
        }

        if (!is_array($params['attributes'])) {
            throw new InvalidDescriptorArgumentException('@Route.attributes must contain an array.');
        }

        return $params['attributes'];
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
        if (!isset($params['summary'])) {
            return '';
        }

        if (!is_string($params['summary'])) {
            throw new InvalidDescriptorArgumentException('@Route.summary must contain a string.');
        }

        return $params['summary'];
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
        if (!isset($params['description'])) {
            return '';
        }

        if (!is_string($params['description'])) {
            throw new InvalidDescriptorArgumentException('@Route.description must contain a string.');
        }

        return $params['description'];
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
        if (!isset($params['tags'])) {
            return [];
        }

        if (!is_array($params['tags'])) {
            throw new InvalidDescriptorArgumentException('@Route.tags must contain an array.');
        }

        foreach ($params['tags'] as $value) {
            if ('' === $value || !is_string($value)) {
                throw new InvalidDescriptorArgumentException('@Route.tags must contain non-empty strings.');
            }
        }

        return $params['tags'];
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
        if (!isset($params['priority'])) {
            return 0;
        }

        if (!is_int($params['priority'])) {
            throw new InvalidDescriptorArgumentException('@Route.priority must contain an integer.');
        }

        return $params['priority'];
    }
}
