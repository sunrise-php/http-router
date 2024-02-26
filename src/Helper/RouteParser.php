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
use Sunrise\Http\Router\Dictionary\Charset;

use function sprintf;

/**
 * @since 3.0.0
 */
final class RouteParser
{
    /**
     * Parses the given route and returns its variables
     *
     * @return list<array{
     *     name: string,
     *     pattern?: string,
     *     optional?: true,
     *     left?: string,
     *     right?: string,
     *     offset: int,
     *     length: int
     * }>
     *
     * @throws InvalidArgumentException If the route isn't valid.
     */
    public static function parseRoute(string $route): array
    {
        $inVariable = 1;
        $inVariableName = 2;
        $inVariablePattern = 8;
        $inOptionalPart = 16;
        $inOccupiedPart = 32;

        $cursor = 0;
        $variable = -1;
        $variables = [];

        $left = $right = '';

        for ($offset = 0; isset($route[$offset]); $offset++) {
            if ($route[$offset] === '(' && !($cursor & $inVariable)) {
                if (($cursor & $inOptionalPart)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open an optional part at position %d has failed ' .
                        'because nested optional parts are not supported.',
                        $route,
                        $offset,
                    ));
                }

                $cursor |= $inOptionalPart;
                continue;
            }
            if ($route[$offset] === ')' && !($cursor & $inVariable)) {
                if (!($cursor & $inOptionalPart)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close an optional part at position %d has failed ' .
                        'because an open optional part was not found.',
                        $route,
                        $offset,
                    ));
                }

                if (($cursor & $inOccupiedPart)) {
                    $cursor &= ~$inOccupiedPart;
                    $variables[$variable]['left'] = $left;
                    $variables[$variable]['right'] = $right;
                }

                $cursor &= ~$inOptionalPart;
                $left = $right = '';
                continue;
            }

            if ($route[$offset] === '{' && !($cursor & $inVariablePattern)) {
                if (($cursor & $inVariable)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable at position %d has failed ' .
                        'because nested variables are not supported.',
                        $route,
                        $offset,
                    ));
                }
                if (($cursor & $inOccupiedPart)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable at position %d has failed ' .
                        'because more than one variable inside an optional part is not supported.',
                        $route,
                        $offset,
                    ));
                }

                $variable++;

                if (($cursor & $inOptionalPart)) {
                    $cursor |= $inOccupiedPart;
                    $variables[$variable]['optional'] = true;
                }

                $cursor |= $inVariable | $inVariableName;
                $variables[$variable]['offset'] = $offset;
                continue;
            }
            if ($route[$offset] === '}' && !($cursor & $inVariablePattern)) {
                if (!($cursor & $inVariable)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable at position %d has failed ' .
                        'because an open variable was not found.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset($variables[$variable]['name'])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable at position %d has failed ' .
                        'because its name is required for its declaration.',
                        $route,
                        $offset,
                    ));
                }

                $cursor &= ~($inVariable | $inVariableName);
                /** @psalm-suppress PossiblyUndefinedArrayOffset */
                $variables[$variable]['length'] = $offset - $variables[$variable]['offset'] + 1;
                continue;
            }

            if ($route[$offset] === '<' && ($cursor & $inVariable)) {
                if (($cursor & $inVariablePattern)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable pattern at position %d has failed ' .
                        'because nested patterns are not supported.',
                        $route,
                        $offset,
                    ));
                }
                if (!($cursor & $inVariableName)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable pattern at position %d has failed ' .
                        'because the pattern must be preceded by the variable name.',
                        $route,
                        $offset,
                    ));
                }

                $cursor = ($cursor | $inVariablePattern) & ~$inVariableName;
                continue;
            }
            if ($route[$offset] === '>' && ($cursor & $inVariable)) {
                if (!($cursor & $inVariablePattern)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable pattern at position %d has failed ' .
                        'because an open pattern was not found.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset($variables[$variable]['pattern'])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable pattern at position %d has failed ' .
                        'because its content is required for its declaration.',
                        $route,
                        $offset,
                    ));
                }

                $cursor &= ~$inVariablePattern;
                continue;
            }

            // (xxx{foo}xxx)
            // ~^^^~~~~~^^^~
            if (($cursor & $inOptionalPart) && !($cursor & $inVariable)) {
                if (!($cursor & $inOccupiedPart)) {
                    $left .= $route[$offset];
                } else {
                    $right .= $route[$offset];
                }

                continue;
            }

            // https://www.pcre.org/original/doc/html/pcrepattern.html#SEC16
            if (($cursor & $inVariableName)) {
                if (!isset($variables[$variable]['name']) && $route[$offset] >= '0' && $route[$offset] <= '9') {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An invalid character was found at position %d. ' .
                        'Please note that variable names cannot start with digits.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset(Charset::PCRE_SUBPATTERN_NAME[$route[$offset]])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An invalid character was found at position %d. ' .
                        'Please note that variable names must consist only of digits, letters and underscores.',
                        $route,
                        $offset,
                    ));
                }
                if (isset($variables[$variable]['name'][31])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An extra character was found at position %d. ' .
                        'Please note that variable names must not exceed 32 characters.',
                        $route,
                        $offset,
                    ));
                }

                $variables[$variable]['name'] ??= '';
                $variables[$variable]['name'] .= $route[$offset];
                continue;
            }

            if (($cursor & $inVariablePattern)) {
                if ($route[$offset] === RouteCompiler::PATTERN_DELIMITER) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An invalid character was found at position %d. ' .
                        'Please note that variable patterns cannot contain the %s character; ' .
                        'use an octal or hexadecimal sequence instead.',
                        $route,
                        $offset,
                        RouteCompiler::PATTERN_DELIMITER,
                    ));
                }

                $variables[$variable]['pattern'] ??= '';
                $variables[$variable]['pattern'] .= $route[$offset];
                continue;
            }

            // {foo<\w+>xxx}
            // ~~~~~~~~~^^^~
            if (($cursor & $inVariable)) {
                throw new InvalidArgumentException(sprintf(
                    'The route %s could not be parsed due to a syntax error. ' .
                    'An unexpected character was found at position %d; a variable at this position must be closed.',
                    $route,
                    $offset,
                ));
            }
        }

        if (($cursor & $inVariable) || ($cursor & $inOptionalPart)) {
            throw new InvalidArgumentException(sprintf(
                'The route %s could not be parsed due to a syntax error. ' .
                'An attempt to parse the route has failed because it contains an unclosed optional part or variable.',
                $route,
            ));
        }

        /**
         * @var list<array{
         *     name: string,
         *     pattern?: string,
         *     optional?: true,
         *     left?: string,
         *     right?: string,
         *     offset: int,
         *     length: int
         * }>
         */
        return $variables;
    }
}
