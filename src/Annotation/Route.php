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
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Route implements RequestMethodInterface
{
    public mixed $holder;

    /** @var list<string> */
    public array $prefixes = [];

    /** @var non-empty-string|null */
    public string|null $pattern = null;

    public function __construct(
        public string $name,
        public string $path = '/',
        string|null $method = null,
        /** @var list<string> */
        public array $methods = [],
        /** @var list<mixed> */
        public array $middlewares = [],
        /** @var array<string, mixed> */
        public array $attributes = [],
        /** @var list<mixed> */
        public array $constraints = [],
        /** @var list<MediaTypeInterface> */
        public array $consumes = [],
        /** @var list<MediaTypeInterface> */
        public array $produces = [],
        public string|null $summary = null,
        public string|null $description = null,
        /** @var list<string> */
        public array $tags = [],
        public bool $isDeprecated = false,
        public int $priority = 0,
    ) {
        if (isset($method)) {
            $this->methods[] = $method;
        } elseif (empty($this->methods)) {
            $this->methods[] = self::METHOD_GET;
        }
    }
}
