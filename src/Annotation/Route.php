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
     * @var mixed
     */
    public $source;

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

        $this->assertValidName($params);
        $this->assertValidPath($params);
        $this->assertValidMethods($params);
        $this->assertValidMiddlewares($params);
        $this->assertValidAttributes($params);
        $this->assertValidPriority($params);

        $this->name = $params['name'];
        $this->path = $params['path'];
        $this->methods = $params['methods'];
        $this->middlewares = $params['middlewares'];
        $this->attributes = $params['attributes'];
        $this->priority = $params['priority'];
    }

    /**
     * @param array $params
     * @return void
     * @throws InvalidAnnotationParameterException
     */
    private function assertValidName(array $params) : void
    {
        if (empty($params['name']) || !is_string($params['name'])) {
            throw new InvalidAnnotationParameterException('@Route.name must be not an empty string.');
        }
    }

    /**
     * @param array $params
     * @return void
     * @throws InvalidAnnotationParameterException
     */
    private function assertValidPath(array $params) : void
    {
        if (empty($params['path']) || !is_string($params['path'])) {
            throw new InvalidAnnotationParameterException('@Route.path must be not an empty string.');
        }
    }

    /**
     * @param array $params
     * @return void
     * @throws InvalidAnnotationParameterException
     */
    private function assertValidMethods(array $params) : void
    {
        if (empty($params['methods']) || !is_array($params['methods'])) {
            throw new InvalidAnnotationParameterException('@Route.methods must be not an empty array.');
        }

        foreach ($params['methods'] as $method) {
            $this->assertValidMethod($method);
        }
    }

    /**
     * @param array $params
     * @return void
     * @throws InvalidAnnotationParameterException
     */
    private function assertValidMiddlewares(array $params) : void
    {
        if (!is_array($params['middlewares'])) {
            throw new InvalidAnnotationParameterException('@Route.middlewares must be an array.');
        }

        foreach ($params['middlewares'] as $middleware) {
            $this->assertValidMiddleware($middleware);
        }
    }

    /**
     * @param array $params
     * @return void
     * @throws InvalidAnnotationParameterException
     */
    private function assertValidAttributes(array $params) : void
    {
        if (!is_array($params['attributes'])) {
            throw new InvalidAnnotationParameterException('@Route.attributes must be an array.');
        }
    }

    /**
     * @param array $params
     * @return void
     * @throws InvalidAnnotationParameterException
     */
    private function assertValidPriority(array $params) : void
    {
        if (!is_int($params['priority'])) {
            throw new InvalidAnnotationParameterException('@Route.priority must be an integer.');
        }
    }

    /**
     * @param mixed $method
     * @return void
     * @throws InvalidAnnotationParameterException
     */
    private function assertValidMethod($method) : void
    {
        if (!is_string($method)) {
            throw new InvalidAnnotationParameterException('@Route.methods must contain only strings.');
        }
    }

    /**
     * @param mixed $middleware
     * @return void
     * @throws InvalidAnnotationParameterException
     */
    private function assertValidMiddleware($middleware) : void
    {
        if (!is_string($middleware)) {
            throw new InvalidAnnotationParameterException('@Route.middlewares must contain only strings.');
        }

        if (!class_exists($middleware)) {
            throw new InvalidAnnotationParameterException('@Route.middlewares contains nonexistent class.');
        }

        if (!is_subclass_of($middleware, MiddlewareInterface::class)) {
            throw new InvalidAnnotationParameterException('@Route.middlewares contains non middleware class.');
        }
    }
}
