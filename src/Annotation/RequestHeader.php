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

/**
 * @link https://dev.sunrise-studio.io/docs/reference/router-annotations?id=requestheader
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class RequestHeader
{
    /**
     * @param array<string, mixed> $hydratorContext
     */
    public function __construct(
        public readonly string $name,
        public readonly ?int $errorStatusCode = null,
        public readonly ?string $errorMessage = null,
        public readonly array $hydratorContext = [],
        public readonly ?bool $validationEnabled = null,
    ) {
    }
}
