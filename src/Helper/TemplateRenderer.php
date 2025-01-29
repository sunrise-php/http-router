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

namespace Sunrise\Http\Router\Helper;

use Throwable;

use function extract;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;

/**
 * @since 3.0.0
 */
final class TemplateRenderer
{
    /**
     * @param array<string, mixed> $variables
     */
    public static function renderTemplate(string $filename, array $variables): string
    {
        extract($variables);
        ob_start();

        try {
            include $filename;
            /** @var string */
            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}
