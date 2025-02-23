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
use Sunrise\Coder\MediaTypeInterface;

/**
 * @link https://dev.sunrise-studio.io/docs/reference/router-annotations?id=route
 * @since 2.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route implements RequestMethodInterface
{
    public mixed $holder = null;

    /** @var array<array-key, string> */
    public array $namePrefixes = [];

    /** @var array<array-key, string> */
    public array $pathPrefixes = [];

    /** @var non-empty-string|null */
    public ?string $pattern = null;

    public function __construct(
        public string $name,
        public string $path = '',
        /** @var array<string, string> */
        public array $patterns = [],
        /** @var array<array-key, string> */
        public array $methods = [],
        /** @var array<string, mixed> */
        public array $attributes = [],
        /** @var array<array-key, mixed> */
        public array $middlewares = [],
        /** @var array<array-key, MediaTypeInterface> */
        public array $consumes = [],
        /** @var array<array-key, MediaTypeInterface> */
        public array $produces = [],
        /** @var array<array-key, string> */
        public array $tags = [],
        public string $summary = '',
        public string $description = '',
        public bool $isDeprecated = false,
        public bool $isApiRoute = false,
        public int $priority = 0,
    ) {
    }
}
