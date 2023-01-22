<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Annotation;

/**
 * Import classes
 */
use Attribute;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Server\MiddlewareInterface;

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
final class Route implements RequestMethodInterface
{

    /**
     * The descriptor holder
     *
     * @var class-string|array{0: class-string, 1: string}|null
     *
     * @internal
     */
    public $holder = null;

    /**
     * The route name
     *
     * @var string
     */
    public string $name;

    /**
     * The route host
     *
     * @var string|null
     */
    public ?string $host;

    /**
     * The route path
     *
     * @var string
     */
    public string $path;

    /**
     * The route methods
     *
     * @var list<string>
     */
    public array $methods;

    /**
     * The route middlewares
     *
     * @var list<class-string<MiddlewareInterface>>
     */
    public array $middlewares;

    /**
     * The route attributes
     *
     * @var array<string, mixed>
     */
    public array $attributes;

    /**
     * The route summary
     *
     * @var string
     */
    public string $summary;

    /**
     * The route description
     *
     * @var string
     */
    public string $description;

    /**
     * The route tags
     *
     * @var list<string>
     */
    public array $tags;

    /**
     * The route priority
     *
     * @var int
     */
    public int $priority;

    /**
     * Constructor of the class
     *
     * @param  string                                   $name         The route name
     * @param  string|null                              $host         The route host
     * @param  string                                   $path         The route path
     * @param  string|null                              $method       The route method
     * @param  list<string>                             $methods      The route methods
     * @param  list<class-string<MiddlewareInterface>>  $middlewares  The route middlewares
     * @param  array<string, mixed>                     $attributes   The route attributes
     * @param  string                                   $summary      The route summary
     * @param  string                                   $description  The route description
     * @param  list<string>                             $tags         The route tags
     * @param  int                                      $priority     The route priority (default 0)
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
            $methods[] = self::METHOD_GET;
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
