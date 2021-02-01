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
        string $host = null,
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

        $this->assertValidName();
        $this->assertValidHost();
        $this->assertValidPath();
        $this->assertValidMethods();
        $this->assertValidMiddlewares();
        $this->assertValidTags();
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
     * Throws an exception if the attribute contains invalid a route name
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertValidName() : void
    {
        InvalidDescriptorArgumentException::assertIsNotEmpty(
            $this->name,
            '#[Route.name] must contain a non-empty string.'
        );
    }

    /**
     * Throws an exception if the attribute contains invalid a route host
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertValidHost() : void
    {
        InvalidDescriptorArgumentException::assertIsNotEmpty(
            $this->host,
            '#[Route.host] must contain a non-empty string or null.'
        );
    }

    /**
     * Throws an exception if the attribute contains invalid a route path
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertValidPath() : void
    {
        InvalidDescriptorArgumentException::assertIsNotEmpty(
            $this->path,
            '#[Route.path] must contain a non-empty string.'
        );
    }

    /**
     * Throws an exception if the attribute contains invalid a route methods
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertValidMethods() : void
    {
        InvalidDescriptorArgumentException::assertIsNotEmpty(
            $this->methods,
            '#[Route.methods] must contain at least one element.'
        );

        foreach ($this->methods as $value) {
            InvalidDescriptorArgumentException::assertIsNotEmptyString(
                $value,
                '#[Route.methods] must contain non-empty strings.'
            );
        }
    }

    /**
     * Throws an exception if the attribute contains invalid a route middlewares
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertValidMiddlewares() : void
    {
        if ([] === $this->middlewares) {
            return;
        }

        foreach ($this->middlewares as $value) {
            InvalidDescriptorArgumentException::assertIsSubclassOf(
                $value,
                MiddlewareInterface::class,
                '#[Route.middlewares] must contain the class names of existing middlewares.'
            );
        }
    }

    /**
     * Throws an exception if the attribute contains invalid a route tags
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertValidTags() : void
    {
        if ([] === $this->tags) {
            return;
        }

        foreach ($this->tags as $value) {
            InvalidDescriptorArgumentException::assertIsNotEmptyString(
                $value,
                '#[Route.tags] must contain non-empty strings.'
            );
        }
    }
}
