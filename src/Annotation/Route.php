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
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Route
{
    public mixed $holder = null;

    /** @var list<string> */
    public array $prefixes = [];

    /** @var array<string, string> */
    public array $patterns = [];

    /** @var list<string> */
    public array $methods = [];

    /** @var array<string, mixed> */
    public array $attributes = [];

    /** @var list<mixed> */
    public array $middlewares = [];

    /** @var list<mixed> */
    public array $constraints = [];

    /** @var list<MediaTypeInterface> */
    public array $consumes = [];

    /** @var list<MediaTypeInterface> */
    public array $produces = [];

    /** @var list<string> */
    public array $tags = [];

    public string $summary = '';

    public string $description = '';

    public bool $isDeprecated = false;

    /** @var non-empty-string|null */
    public string|null $pattern = null;

    public int $priority = 0;

    public function __construct(
        public string $name,
        public string $path,
    ) {
    }
}
