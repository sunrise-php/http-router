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
use Attribute;

/**
 * @Annotation
 *
 * @Target({"CLASS", "METHOD"})
 *
 * @NamedArgumentConstructor
 *
 * @Attributes({
 *   @Attribute("name", type="string", required=true),
 *   @Attribute("host", type="string"),
 *   @Attribute("path", type="string", required=true),
 *   @Attribute("method", type="string"),
 *   @Attribute("methods", type="array<string>"),
 *   @Attribute("middlewares", type="array<string>"),
 *   @Attribute("attributes", type="array"),
 *   @Attribute("summary", type="string"),
 *   @Attribute("description", type="string"),
 *   @Attribute("tags", type="array<string>"),
 *   @Attribute("priority", type="integer"),
 * })
 */
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
final class Route
{

    /**
     * The descriptor holder
     *
     * @var mixed
     *
     * @internal
     */
    public $holder;

    /**
     * A route name
     *
     * @var string
     */
    public $name;

    /**
     * A route host
     *
     * @var string|null
     */
    public $host;

    /**
     * A route path
     *
     * @var string
     */
    public $path;

    /**
     * A route methods
     *
     * @var string[]
     */
    public $methods;

    /**
     * A route middlewares
     *
     * @var string[]
     */
    public $middlewares;

    /**
     * A route attributes
     *
     * @var array
     */
    public $attributes;

    /**
     * A route summary
     *
     * @var string
     */
    public $summary;

    /**
     * A route description
     *
     * @var string
     */
    public $description;

    /**
     * A route tags
     *
     * @var string[]
     */
    public $tags;

    /**
     * A route priority
     *
     * @var int
     */
    public $priority;

    /**
     * Constructor of the class
     *
     * @param  string       $name         The route name
     * @param  string|null  $host         The route host
     * @param  string       $path         The route path
     * @param  string|null  $method       The route method
     * @param  string[]     $methods      The route methods
     * @param  string[]     $middlewares  The route middlewares
     * @param  array        $attributes   The route attributes
     * @param  string       $summary      The route summary
     * @param  string       $description  The route description
     * @param  string[]     $tags         The route tags
     * @param  int          $priority     The route priority (default 0)
     */
    public function __construct(
        string $name,
        ?string $host = null,
        string $path = '/',
        ?string $method = null,
        array $methods = [],
        array $middlewares = [],
        array $attributes = [],
        string $summary = '',
        string $description = '',
        array $tags = [],
        int $priority = 0
    ) {
        if (isset($method)) {
            $methods[] = $method;
        }

        // if no methods are specified,
        // such a route is a GET route.
        if (empty($methods)) {
            $methods[] = 'GET';
        }

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
    }
}
