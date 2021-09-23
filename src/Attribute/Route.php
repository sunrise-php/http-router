<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Attribute;

/**
 * Import classes
 */
use Attribute;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Exception\InvalidDescriptorArgumentException;
use Sunrise\Http\Router\RouteDescriptorInterface;

/**
 * Attribute for a route description
 *
 * @since 2.6.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
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
     * Constructor of the attribute
     *
     * @param  string       $name         A route name
     * @param  null|string  $host         A route host
     * @param  string       $path         A route path
     * @param  string[]     $methods      A route methods
     * @param  string[]     $middlewares  A route middlewares
     * @param  array        $attributes   A route attributes
     * @param  string       $summary      A route summary
     * @param  string       $description  A route description
     * @param  string[]     $tags         A route tags
     * @param  int          $priority     A route priority
     *
     * @throws InvalidDescriptorArgumentException
     */
    public function __construct(
        string $name,
        ?string $host = null,
        string $path,
        array $methods,
        array $middlewares = [],
        array $attributes = [],
        string $summary = '',
        string $description = '',
        array $tags = [],
        int $priority = 0
    ) {
        $this->name = $name;
        $this->host = $host;
        $this->path = $path;
        $this->methods = $methods;
        $this->middlewares = $middlewares;
        $this->attributes = $attributes;
        $this->summary = $summary;
        $this->description = $description;
        $this->tags = $tags;
        $this->priority = $priority;

        $this->validateName();
        $this->validateHost();
        $this->validatePath();
        $this->validateMethods();
        $this->validateMiddlewares();
        $this->validateTags();
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
     * Throws an exception if the attribute contains an invalid route name
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     *         If the attribute contains an invalid route name.
     */
    private function validateName() : void
    {
        InvalidDescriptorArgumentException::throwIfEmpty(
            $this->name,
            '#[Route.name] must contain a non-empty string.'
        );
    }

    /**
     * Throws an exception if the attribute contains an invalid route host
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     *         If the attribute contains an invalid route host.
     */
    private function validateHost() : void
    {
        InvalidDescriptorArgumentException::throwIfEmpty(
            $this->host,
            '#[Route.host] must contain a non-empty string or null.'
        );
    }

    /**
     * Throws an exception if the attribute contains an invalid route path
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     *         If the attribute contains an invalid route path.
     */
    private function validatePath() : void
    {
        InvalidDescriptorArgumentException::throwIfEmpty(
            $this->path,
            '#[Route.path] must contain a non-empty string.'
        );
    }

    /**
     * Throws an exception if the attribute contains an invalid route methods
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     *         If the attribute contains an invalid route methods.
     */
    private function validateMethods() : void
    {
        InvalidDescriptorArgumentException::throwIfEmpty(
            $this->methods,
            '#[Route.methods] must contain at least one element.'
        );

        foreach ($this->methods as $method) {
            InvalidDescriptorArgumentException::throwIfEmptyString(
                $method,
                '#[Route.methods] must contain non-empty strings.'
            );
        }
    }

    /**
     * Throws an exception if the attribute contains an invalid route middlewares
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     *         If the attribute contains an invalid route middlewares.
     */
    private function validateMiddlewares() : void
    {
        if ([] === $this->middlewares) {
            return;
        }

        foreach ($this->middlewares as $middleware) {
            InvalidDescriptorArgumentException::throwIfNotImplemented(
                $middleware,
                MiddlewareInterface::class,
                '#[Route.middlewares] must contain middlewares.'
            );
        }
    }

    /**
     * Throws an exception if the attribute contains an invalid route tags
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     *         If the attribute contains an invalid route tags.
     */
    private function validateTags() : void
    {
        if ([] === $this->tags) {
            return;
        }

        foreach ($this->tags as $tag) {
            InvalidDescriptorArgumentException::throwIfEmptyString(
                $tag,
                '#[Route.tags] must contain non-empty strings.'
            );
        }
    }
}
