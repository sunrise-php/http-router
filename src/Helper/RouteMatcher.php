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

use ErrorException;
use InvalidArgumentException;

use function is_int;
use function preg_last_error;
use function preg_last_error_msg;
use function preg_match;
use function sprintf;

use const E_WARNING;
use const PREG_UNMATCHED_AS_NULL;

/**
 * @since 3.0.0
 */
final class RouteMatcher
{
    /**
     * @param-out array<string, string> $matches
     *
     * @throws InvalidArgumentException If the route isn't valid or any of the patterns are unsupported.
     */
    public static function matchRoute(string $route, array $patterns, string $subject, ?array &$matches = null): bool
    {
        $pattern = RouteCompiler::compileRoute($route, $patterns);

        return self::matchPattern($route, $pattern, $subject, $matches);
    }

    /**
     * @param non-empty-string $pattern
     * @param-out array<string, string> $matches
     *
     * @throws InvalidArgumentException If the pattern (read: route) isn't valid.
     */
    public static function matchPattern(string $route, string $pattern, string $subject, ?array &$matches = null): bool
    {
        try {
            if (($result = @preg_match($pattern, $subject, $matches, PREG_UNMATCHED_AS_NULL)) === false) {
                throw new ErrorException(preg_last_error_msg(), preg_last_error(), E_WARNING);
            }
        } catch (ErrorException $e) {
            throw new InvalidArgumentException(sprintf(
                'The route %s could not be matched due to a syntax error in one of variable patterns.',
                $route,
            ), previous: $e);
        }

        foreach ($matches as $key => $match) {
            if (is_int($key)) {
                unset($matches[$key]);
                continue;
            }
            if ($match === null) {
                unset($matches[$key]);
                continue;
            }
        }

        /** @var array<string, string> $matches */

        return $result === 1;
    }
}
