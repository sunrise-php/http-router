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
use InvalidArgumentException;

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
     * @var object
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
    public $middlewares = [];

    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @var int
     */
    public $priority = 0;

    /**
     * @param array $params
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $params)
    {
        if (empty($params['name']) || !is_string($params['name'])) {
            throw new InvalidArgumentException('@Route.name must be not an empty string.');
        }
        if (empty($params['path']) || !is_string($params['path'])) {
            throw new InvalidArgumentException('@Route.path must be not an empty string.');
        }
        if (empty($params['methods']) || !is_array($params['methods'])) {
            throw new InvalidArgumentException('@Route.methods must be not an empty array.');
        }
        if (isset($params['middlewares']) && !is_array($params['middlewares'])) {
            throw new InvalidArgumentException('@Route.middlewares must be an array.');
        }
        if (isset($params['attributes']) && !is_array($params['attributes'])) {
            throw new InvalidArgumentException('@Route.attributes must be an array.');
        }
        if (isset($params['priority']) && !is_int($params['priority'])) {
            throw new InvalidArgumentException('@Route.priority must be an integer.');
        }

        foreach ($params['methods'] as $v) {
            if (!is_string($v)) {
                throw new InvalidArgumentException('@Route.methods must contain only strings.');
            }
        }

        if (isset($params['middlewares'])) {
            foreach ($params['middlewares'] as $v) {
                if (!is_string($v) || !class_exists($v) || !is_subclass_of($v, MiddlewareInterface::class)) {
                    throw new InvalidArgumentException('@Route.middlewares must contain only middlewares.');
                }
            }
        }

        $this->name = $params['name'];
        $this->path = $params['path'];
        $this->methods = $params['methods'];

        if (isset($params['middlewares'])) {
            $this->middlewares = $params['middlewares'];
        }
        if (isset($params['attributes'])) {
            $this->attributes = $params['attributes'];
        }
        if (isset($params['priority'])) {
            $this->priority = $params['priority'];
        }
    }
}
