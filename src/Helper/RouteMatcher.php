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
use Sunrise\Http\Router\Exception\InvalidRouteParsingSubjectException;
use Sunrise\Http\Router\Exception\InvalidRouteMatchingPatternException;
use Sunrise\Http\Router\Exception\InvalidRouteMatchingSubjectException;
use Throwable;

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
     * @param-out array<string, string> $matches
     *
     * @throws InvalidRouteMatchingPatternException
     * @throws InvalidRouteMatchingSubjectException
     * @throws InvalidRouteParsingSubjectException
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
     * @throws InvalidRouteMatchingPatternException
     * @throws InvalidRouteMatchingSubjectException
     */
    public static function matchPattern(string $route, string $pattern, string $subject, ?array &$matches = null): bool
    {
        try {
            if (($result = @preg_match($pattern, $subject, $matches, PREG_UNMATCHED_AS_NULL)) === false) {
                throw new ErrorException(preg_last_error_msg(), preg_last_error(), E_WARNING);
            }
        } catch (Throwable $e) {
            // a client side error; must be handled as 4xx error.
            if (preg_last_error() === PREG_BAD_UTF8_ERROR) {
                throw new InvalidRouteMatchingSubjectException(sprintf(
                    'The route %s could not be matched with an invalid subject due to: %s.',
                    RouteSimplifier::simplifyRoute($route),
                    preg_last_error_msg(),
                ), preg_last_error(), $e);
            }

            // a server side error; must be handled as 5xx error.
            throw new InvalidRouteMatchingPatternException(sprintf(
                'The route %s could not be matched due to: %s; ' .
                'most likely, this problem is related to one of the route patterns. ' .
                'Please refer to the official documentation: https://www.php.net/preg_last_error',
                $route,
                preg_last_error_msg(),
            ), preg_last_error(), $e);
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
