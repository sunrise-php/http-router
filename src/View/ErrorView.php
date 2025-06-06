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

use Sunrise\Hydrator\Annotation\Subtype;

/**
 * @since 3.0.0
 */
final class ErrorView
{
    public function __construct(
        public readonly string $message,
        /** @var array<array-key, ViolationView> */
        #[Subtype(ViolationView::class)]
        public readonly array $violations,
    ) {
    }
}
