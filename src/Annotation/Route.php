<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\Annotation;

use Attribute;
use Fig\Http\Message\RequestMethodInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Route implements RequestMethodInterface
{

    /**
     * The annotation's holder
     *
     * @var mixed
     *
     * @internal
     */
    public mixed $holder = null;

    /**
     * Constructor of the class
     *
     * @param  non-empty-string                $name         The route's name
     * @param  non-empty-string|null           $host         The route's host
     * @param  non-empty-string                $path         The route's path
     * @param  non-empty-string|null           $method       The route's method
     * @param  list<non-empty-string>          $methods      The route's methods
     * @param  list<non-empty-string>          $consumes     The route's consumed media types
     * @param  list<non-empty-string>          $produces     The route's produced media types
     * @param  list<mixed>                     $middlewares  The route's middlewares
     * @param  array<non-empty-string, mixed>  $attributes   The route's attributes
     * @param  string                          $summary      The route's summary
     * @param  string                          $description  The route's description
     * @param  list<non-empty-string>          $tags         The route's tags
     * @param  int<min, max>                   $priority     The route's priority (default 0)
     */
    public function __construct(
        public string $name,
        public ?string $host = null,
        public string $path = '/',
        ?string $method = null,
        public array $methods = [],
        public array $consumes = [],
        public array $produces = [],
        public array $middlewares = [],
        public array $attributes = [],
        public string $summary = '',
        public string $description = '',
        public array $tags = [],
        public int $priority = 0,
    ) {
        if (isset($method)) {
            $this->methods[] = $method;
        } elseif (empty($this->methods)) {
            $this->methods[] = self::METHOD_GET;
        }
    }
}
