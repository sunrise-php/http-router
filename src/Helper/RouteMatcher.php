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
use Throwable;
use UnexpectedValueException;

use function is_int;
use function preg_last_error;
use function preg_last_error_msg;
use function preg_match;
use function sprintf;

use const E_WARNING;
use const PREG_BAD_UTF8_ERROR;
use const PREG_UNMATCHED_AS_NULL;

/**
 * @since 3.0.0
 */
final class RouteMatcher
{
    /**
     * @param array<string, string> $patterns
     * @param ?array<string, string> $matches
     * @param-out array<string, string> $matches
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public static function matchRoute(string $route, array $patterns, string $subject, ?array &$matches = null): bool
    {
        $pattern = RouteCompiler::compileRoute($route, $patterns);

        return self::matchPattern($route, $pattern, $subject, $matches);
    }

    /**
     * @param non-empty-string $pattern
     * @param ?array<string, string> $matches
     * @param-out array<string, string> $matches
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public static function matchPattern(string $route, string $pattern, string $subject, ?array &$matches = null): bool
    {
        try {
            if (($result = @preg_match($pattern, $subject, $matches, PREG_UNMATCHED_AS_NULL)) === false) {
                throw new ErrorException(preg_last_error_msg(), preg_last_error(), E_WARNING);
            }
        } catch (Throwable) {
            if (preg_last_error() === PREG_BAD_UTF8_ERROR) {
                throw new UnexpectedValueException(sprintf(
                    'The route %s could not be matched due to an invalid subject: %s.',
                    $route,
                    preg_last_error_msg(),
                ));
            }

            throw new InvalidArgumentException(sprintf(
                'The route %s could not be matched due to: %s. ' .
                'This problem is most likely related to one of the route patterns',
                $route,
                preg_last_error_msg(),
            ), preg_last_error());
        }

        foreach ($matches as $key => $match) {
            if (is_int($key) || $match === null) {
                unset($matches[$key]);
            }
        }

        /** @var array<string, string> $matches */

        return $result === 1;
    }
}
