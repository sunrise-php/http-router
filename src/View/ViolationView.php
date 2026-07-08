<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatolii Nekhai <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatolii Nekhai
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
