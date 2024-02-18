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
use Sunrise\Http\Router\Entity\EncodingInterface;
use Sunrise\Http\Router\Entity\MediaTypeInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Route implements RequestMethodInterface
{
    public mixed $holder;

    /** @var list<string> */
    public array $prefixes = [];

    /** @var non-empty-string|null */
    public ?string $pattern = null;

    public function __construct(
        public string $name,
        public string $path = '/',
        ?string $method = null,
        /** @var list<string> */
        public array $methods = [],
        /** @var list<mixed> */
        public array $middlewares = [],
        /** @var array<string, mixed> */
        public array $attributes = [],
        /** @var array<string, mixed> */
        public array $constraints = [],
        /** @var list<EncodingInterface> */
        public array $consumesEncodings = [],
        /** @var list<EncodingInterface> */
        public array $producesEncodings = [],
        /** @var list<MediaTypeInterface> */
        public array $consumesMediaTypes = [],
        /** @var list<MediaTypeInterface> */
        public array $producesMediaTypes = [],
        public ?string $summary = null,
        public ?string $description = null,
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
