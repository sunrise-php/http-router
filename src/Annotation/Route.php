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
use Sunrise\Http\Router\Exception\InvalidAnnotationParameterException;
use Sunrise\Http\Router\Exception\InvalidDescriptorArgumentException;
use Sunrise\Http\Router\RouteDescriptorInterface;

/**
 * Import functions
 */
use function is_array;
use function is_int;
use function is_null;
use function is_string;
use function is_subclass_of;
use function implode;

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
     *
     * @deprecated 2.6.0 The public property will be closed at the near time. Please use getters.
     */
    public $name;

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
     *
     * @deprecated 2.6.0 The public property will be closed at the near time. Please use getters.
     */
    public $path;

    /**
     * A route methods
     *
     * @var array
     *
     * @deprecated 2.6.0 The public property will be closed at the near time. Please use getters.
     */
    public $methods;

    /**
     * A route middlewares
     *
     * @var array
     *
     * @deprecated 2.6.0 The public property will be closed at the near time. Please use getters.
     */
    public $middlewares;

    /**
     * A route attributes
     *
     * @var array
     *
     * @deprecated 2.6.0 The public property will be closed at the near time. Please use getters.
     */
    public $attributes;

    /**
     * A route summary
     *
     * @var string
     *
     * @since 2.4.0
     *
     * @deprecated 2.6.0 The public property will be closed at the near time. Please use getters.
     */
    public $summary;

    /**
     * A route description
     *
     * @var array|string
     *
     * @since 2.4.0
     *
     * @deprecated 2.6.0 The public property will be closed at the near time. Please use getters.
     */
    public $description;

    /**
     * A route tags
     *
     * @var array
     *
     * @since 2.4.0
     *
     * @deprecated 2.6.0 The public property will be closed at the near time. Please use getters.
     */
    public $tags;

    /**
     * A route priority
     *
     * @var int
     *
     * @deprecated 2.6.0 The public property will be closed at the near time. Please use getters.
     */
    public $priority;

    /**
     * Constructor of the annotation
     *
     * @param array $params
     *
     * @throws InvalidDescriptorArgumentException
     */
    public function __construct(array $params)
    {
        $params += [
            'host' => null,
            'middlewares' => [],
            'attributes' => [],
            'summary' => '',
            'description' => '',
            'tags' => [],
            'priority' => 0,
        ];

        $this->assertParamsContainValidName($params);
        $this->assertParamsContainValidHost($params);
        $this->assertParamsContainValidPath($params);
        $this->assertParamsContainValidMethods($params);
        $this->assertParamsContainValidMiddlewares($params);
        $this->assertParamsContainValidAttributes($params);
        $this->assertParamsContainValidSummary($params);
        $this->assertParamsContainValidDescription($params);
        $this->assertParamsContainValidTags($params);
        $this->assertParamsContainValidPriority($params);

        // Opportunity for concatenation...
        if (is_array($params['description'])) {
            $params['description'] = implode($params['description']);
        }

        /** @scrutinizer ignore-deprecated */ $this->name = $params['name'];
        /** @scrutinizer ignore-deprecated */ $this->host = $params['host'];
        /** @scrutinizer ignore-deprecated */ $this->path = $params['path'];
        /** @scrutinizer ignore-deprecated */ $this->methods = $params['methods'];
        /** @scrutinizer ignore-deprecated */ $this->middlewares = $params['middlewares'];
        /** @scrutinizer ignore-deprecated */ $this->attributes = $params['attributes'];
        /** @scrutinizer ignore-deprecated */ $this->summary = $params['summary'];
        /** @scrutinizer ignore-deprecated */ $this->description = $params['description'];
        /** @scrutinizer ignore-deprecated */ $this->tags = $params['tags'];
        /** @scrutinizer ignore-deprecated */ $this->priority = $params['priority'];
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getName() : string
    {
        return /** @scrutinizer ignore-deprecated */ $this->name;
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getHost() : ?string
    {
        return /** @scrutinizer ignore-deprecated */ $this->host;
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getPath() : string
    {
        return /** @scrutinizer ignore-deprecated */ $this->path;
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getMethods() : array
    {
        return /** @scrutinizer ignore-deprecated */ $this->methods;
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getMiddlewares() : array
    {
        return /** @scrutinizer ignore-deprecated */ $this->middlewares;
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getAttributes() : array
    {
        return /** @scrutinizer ignore-deprecated */ $this->attributes;
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getSummary() : string
    {
        return /** @scrutinizer ignore-deprecated */ $this->summary;
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getDescription() : string
    {
        return /** @scrutinizer ignore-deprecated */ $this->description;
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getTags() : array
    {
        return /** @scrutinizer ignore-deprecated */ $this->tags;
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.6.0
     */
    public function getPriority() : int
    {
        return /** @scrutinizer ignore-deprecated */ $this->priority;
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidName(array $params) : void
    {
        if (empty($params['name']) || !is_string($params['name'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.name must be not an empty string.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidHost(array $params) : void
    {
        if (!is_null($params['host']) && !is_string($params['host'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.host must be null or string.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidPath(array $params) : void
    {
        if (empty($params['path']) || !is_string($params['path'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.path must be not an empty string.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidMethods(array $params) : void
    {
        if (empty($params['methods']) || !is_array($params['methods'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.methods must be not an empty array.'
            );
        }

        foreach ($params['methods'] as $method) {
            if (!is_string($method)) {
                throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                    '@Route.methods must contain only strings.'
                );
            }
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidMiddlewares(array $params) : void
    {
        if (!is_array($params['middlewares'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.middlewares must be an array.'
            );
        }

        foreach ($params['middlewares'] as $middleware) {
            if (!is_string($middleware)) {
                throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                    '@Route.middlewares must contain only strings.'
                );
            }

            if (!is_subclass_of($middleware, MiddlewareInterface::class)) {
                throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                    '@Route.middlewares contains a nonexistent or non-middleware class.'
                );
            }
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidAttributes(array $params) : void
    {
        if (!is_array($params['attributes'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.attributes must be an array.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidSummary(array $params) : void
    {
        if (!is_string($params['summary'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.summary must be a string.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidDescription(array $params) : void
    {
        if (!is_array($params['description']) && !is_string($params['description'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.description must be an array or a string.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidTags(array $params) : void
    {
        if (!is_array($params['tags'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.tags must be an array.'
            );
        }

        foreach ($params['tags'] as $middleware) {
            if (!is_string($middleware)) {
                throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                    '@Route.tags must contain only strings.'
                );
            }
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidDescriptorArgumentException
     */
    private function assertParamsContainValidPriority(array $params) : void
    {
        if (!is_int($params['priority'])) {
            throw /** @scrutinizer ignore-deprecated */ new InvalidAnnotationParameterException(
                '@Route.priority must be an integer.'
            );
        }
    }
}
