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

use InvalidArgumentException;
use Throwable;

use function ob_end_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;
use function realpath;
use function sprintf;

/**
 * Internal view loader
 *
 * @internal
 */
final class ViewLoader
{

    /**
     * Loads a view by the given filename
     *
     * @param non-empty-string $filename
     * @param object $scope
     *
     * @return string
     *
     * @throws InvalidArgumentException If the view doesn't exist or is inaccessible.
     */
    public static function loadView(string $filename, object $scope): string
    {
        $pathname = realpath(__DIR__ . '/../../resources/views/' . $filename);

        if ($pathname === false) {
            throw new InvalidArgumentException(sprintf(
                'The view {%s} does not exist or is inaccessible.',
                $filename,
            ));
        }

        try {
            ob_start();

            (function (string $pathname): void {
                /** @psalm-suppress UnresolvableInclude */
                include $pathname;
            })->call($scope, $pathname);

            return ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level()) {
                ob_end_clean();
            }

            throw $e;
        }
    }
}
