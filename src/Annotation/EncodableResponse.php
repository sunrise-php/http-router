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
use Sunrise\Http\Router\MediaTypeInterface;

/**
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class EncodableResponse
{
    /**
     * @param array<array-key, mixed> $codecContext
     */
    public function __construct(
        public readonly ?MediaTypeInterface $defaultMediaType = null,
        public readonly array $codecContext = [],
    ) {
    }
}
