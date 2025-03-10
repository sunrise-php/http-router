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

namespace Sunrise\Http\Router\View;

/**
 * @since 3.0.0
 */
final class ViolationView
{
    public function __construct(
        public readonly string $source,
        public readonly string $message,
        public readonly ?string $code,
    ) {
    }
}
