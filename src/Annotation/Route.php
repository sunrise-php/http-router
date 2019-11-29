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
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\InvalidAnnotationParameterException;
use Sunrise\Http\Router\Exception\InvalidAnnotationSourceException;

/**
 * Import functions
 */
use function class_exists;
use function is_array;
use function is_int;
use function is_string;
use function is_subclass_of;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Route
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $path;

    /**
     * @var array
     */
    public $methods;

    /**
     * @var array
     */
    public $middlewares;

    /**
     * @var array
     */
    public $attributes;

    /**
     * @var int
     */
    public $priority;

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $params += [
            'middlewares' => [],
            'attributes' => [],
            'priority' => 0,
        ];

        $this->assertParamsContainValidName($params);
        $this->assertParamsContainValidPath($params);
        $this->assertParamsContainValidMethods($params);
        $this->assertParamsContainValidMiddlewares($params);
        $this->assertParamsContainValidAttributes($params);
        $this->assertParamsContainValidPriority($params);

        $this->name = $params['name'];
        $this->path = $params['path'];
        $this->methods = $params['methods'];
        $this->middlewares = $params['middlewares'];
        $this->attributes = $params['attributes'];
        $this->priority = $params['priority'];
    }

    /**
     * @param string $source
     *
     * @return void
     *
     * @throws InvalidAnnotationSourceException
     */
    public static function assertValidSource(string $source) : void
    {
        if (!is_subclass_of($source, RequestHandlerInterface::class)) {
            throw new InvalidAnnotationSourceException(
                sprintf('@Route annotation source %s is not a request handler.', $source)
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidAnnotationParameterException
     */
    private function assertParamsContainValidName(array $params) : void
    {
        if (empty($params['name']) || !is_string($params['name'])) {
            throw new InvalidAnnotationParameterException(
                '@Route.name must be not an empty string.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidAnnotationParameterException
     */
    private function assertParamsContainValidPath(array $params) : void
    {
        if (empty($params['path']) || !is_string($params['path'])) {
            throw new InvalidAnnotationParameterException(
                '@Route.path must be not an empty string.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidAnnotationParameterException
     */
    private function assertParamsContainValidMethods(array $params) : void
    {
        if (empty($params['methods']) || !is_array($params['methods'])) {
            throw new InvalidAnnotationParameterException(
                '@Route.methods must be not an empty array.'
            );
        }

        foreach ($params['methods'] as $method) {
            if (!is_string($method)) {
                throw new InvalidAnnotationParameterException(
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
     * @throws InvalidAnnotationParameterException
     */
    private function assertParamsContainValidMiddlewares(array $params) : void
    {
        if (!is_array($params['middlewares'])) {
            throw new InvalidAnnotationParameterException(
                '@Route.middlewares must be an array.'
            );
        }

        foreach ($params['middlewares'] as $middleware) {
            if (!is_string($middleware)) {
                throw new InvalidAnnotationParameterException(
                    '@Route.middlewares must contain only strings.'
                );
            }

            if (!is_subclass_of($middleware, MiddlewareInterface::class)) {
                throw new InvalidAnnotationParameterException(
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
     * @throws InvalidAnnotationParameterException
     */
    private function assertParamsContainValidAttributes(array $params) : void
    {
        if (!is_array($params['attributes'])) {
            throw new InvalidAnnotationParameterException(
                '@Route.attributes must be an array.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws InvalidAnnotationParameterException
     */
    private function assertParamsContainValidPriority(array $params) : void
    {
        if (!is_int($params['priority'])) {
            throw new InvalidAnnotationParameterException(
                '@Route.priority must be an integer.'
            );
        }
    }
}
