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
use Sunrise\Http\Router\Annotation as Routing;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Route
{
    /**
     * The route's request handler.
     */
    public mixed $holder = null;

    /**
     * Use the {@see Routing\Prefix} annotation.
     *
     * @var array<array-key, string>
     */
    public array $prefixes = [];

    /**
     * Use the {@see Routing\Pattern} annotation.
     *
     * @var array<string, string>
     */
    public array $patterns = [];

    /**
     * Use the {@see Routing\Method} annotation.
     *
     * @var array<array-key, string>
     */
    public array $methods = [];

    /**
     * Use the {@see Routing\Attribute} annotation.
     *
     * @var array<string, mixed>
     */
    public array $attributes = [];

    /**
     * Use the {@see Routing\Middleware} annotation.
     *
     * @var array<array-key, mixed>
     */
    public array $middlewares = [];

    /**
     * Use the {@see Routing\Constraint} annotation.
     *
     * @var array<array-key, mixed>
     */
    public array $constraints = [];

    /**
     * Use the {@see Routing\Consumes} annotation.
     *
     * @var array<array-key, MediaTypeInterface>
     */
    public array $consumes = [];

    /**
     * Use the {@see Routing\Produces} annotation.
     *
     * @var array<array-key, MediaTypeInterface>
     */
    public array $produces = [];

    /**
     * Use the {@see Routing\Tag} annotation.
     *
     * @var array<array-key, string>
     */
    public array $tags = [];

    /**
     * Use the {@see Routing\Summary} annotation.
     */
    public string $summary = '';

    /**
     * Use the {@see Routing\Description} annotation.
     */
    public string $description = '';

    /**
     * Use the {@see Routing\Deprecated} annotation.
     */
    public bool $isDeprecated = false;

    /**
     * Use the {@see Routing\Priority} annotation.
     */
    public int $priority = 0;

    /**
     * The route's compilation pattern.
     *
     * @var non-empty-string|null
     */
    public string|null $pattern = null;

    public function __construct(
        public readonly string $name,
        public string $path,
    ) {
    }
}
