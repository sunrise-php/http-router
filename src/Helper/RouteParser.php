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

use Sunrise\Http\Router\Dictionary\Charset;
use Sunrise\Http\Router\Exception\InvalidRouteParsingSubjectException;

use function sprintf;

/**
 * @since 3.0.0
 */
final class RouteParser
{
    private const IN_VARIABLE = 1;
    private const IN_VARIABLE_NAME = 2;
    private const IN_VARIABLE_PATTERN = 8;
    private const IN_OPTIONAL_PART = 16;
    private const IN_OCCUPIED_PART = 32;

    /**
     * Parses the given route and returns its variables
     *
     * @return list<array{name: string, pattern?: string, optional?: array{left: string, right: string}, offset: int, length: int}>
     *
     * @throws InvalidRouteParsingSubjectException
     */
    public static function parseRoute(string $route): array
    {
        $cursor = 0;
        $variable = -1;
        $variables = [];

        $left = $right = '';

        for ($offset = 0; isset($route[$offset]); $offset++) {
            if ($route[$offset] === '(' && !($cursor & self::IN_VARIABLE)) {
                if (($cursor & self::IN_OPTIONAL_PART)) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open an optional part at position %d has failed ' .
                        'because nested optional parts are not supported.',
                        $route,
                        $offset,
                    ));
                }

                $cursor |= self::IN_OPTIONAL_PART;
                continue;
            }
            if ($route[$offset] === ')' && !($cursor & self::IN_VARIABLE)) {
                if (!($cursor & self::IN_OPTIONAL_PART)) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close an optional part at position %d has failed ' .
                        'because an open optional part was not found.',
                        $route,
                        $offset,
                    ));
                }

                if (($cursor & self::IN_OCCUPIED_PART)) {
                    $cursor &= ~self::IN_OCCUPIED_PART;
                    $variables[$variable]['optional']['left'] = $left;
                    $variables[$variable]['optional']['right'] = $right;
                }

                $cursor &= ~self::IN_OPTIONAL_PART;
                $left = $right = '';
                continue;
            }

            if ($route[$offset] === '{' && !($cursor & self::IN_VARIABLE_PATTERN)) {
                if (($cursor & self::IN_VARIABLE)) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable at position %d has failed ' .
                        'because nested variables are not supported.',
                        $route,
                        $offset,
                    ));
                }
                if (($cursor & self::IN_OCCUPIED_PART)) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable at position %d has failed ' .
                        'because more than one variable inside an optional part is not supported.',
                        $route,
                        $offset,
                    ));
                }

                $variable++;

                if (($cursor & self::IN_OPTIONAL_PART)) {
                    $cursor |= self::IN_OCCUPIED_PART;
                }

                $cursor |= self::IN_VARIABLE | self::IN_VARIABLE_NAME;
                $variables[$variable]['offset'] = $offset;
                continue;
            }
            if ($route[$offset] === '}' && !($cursor & self::IN_VARIABLE_PATTERN)) {
                if (!($cursor & self::IN_VARIABLE)) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable at position %d has failed ' .
                        'because an open variable was not found.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset($variables[$variable]['name'])) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable at position %d has failed ' .
                        'because its name is required for its declaration.',
                        $route,
                        $offset,
                    ));
                }

                $cursor &= ~(self::IN_VARIABLE | self::IN_VARIABLE_NAME);
                /** @psalm-suppress PossiblyUndefinedArrayOffset */
                $variables[$variable]['length'] = $offset - $variables[$variable]['offset'] + 1;
                continue;
            }

            if ($route[$offset] === '<' && ($cursor & self::IN_VARIABLE)) {
                if (($cursor & self::IN_VARIABLE_PATTERN)) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable pattern at position %d has failed ' .
                        'because nested patterns are not supported.',
                        $route,
                        $offset,
                    ));
                }
                if (!($cursor & self::IN_VARIABLE_NAME)) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable pattern at position %d has failed ' .
                        'because the pattern must be preceded by the variable name.',
                        $route,
                        $offset,
                    ));
                }

                $cursor = ($cursor | self::IN_VARIABLE_PATTERN) & ~self::IN_VARIABLE_NAME;
                continue;
            }
            if ($route[$offset] === '>' && ($cursor & self::IN_VARIABLE)) {
                if (!($cursor & self::IN_VARIABLE_PATTERN)) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable pattern at position %d has failed ' .
                        'because an open pattern was not found.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset($variables[$variable]['pattern'])) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable pattern at position %d has failed ' .
                        'because its content is required for its declaration.',
                        $route,
                        $offset,
                    ));
                }

                $cursor &= ~self::IN_VARIABLE_PATTERN;
                continue;
            }

            // (left{foo}right)
            // ~^^^^~~~~~^^^^^~
            if (($cursor & self::IN_OPTIONAL_PART) && !($cursor & self::IN_VARIABLE)) {
                if (!($cursor & self::IN_OCCUPIED_PART)) {
                    $left .= $route[$offset];
                } else {
                    $right .= $route[$offset];
                }

                continue;
            }

            // https://www.pcre.org/original/doc/html/pcrepattern.html#SEC16
            if (($cursor & self::IN_VARIABLE_NAME)) {
                if (!isset($variables[$variable]['name']) && $route[$offset] >= '0' && $route[$offset] <= '9') {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An invalid character was found at position %d. ' .
                        'Please note that variable names cannot start with digits.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset(Charset::PCRE_SUBPATTERN_NAME[$route[$offset]])) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An invalid character was found at position %d. ' .
                        'Please note that variable names must consist only of digits, letters and underscores.',
                        $route,
                        $offset,
                    ));
                }
                if (isset($variables[$variable]['name'][31])) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
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

            if (($cursor & self::IN_VARIABLE_PATTERN)) {
                if ($route[$offset] === RouteCompiler::PATTERN_DELIMITER) {
                    throw new InvalidRouteParsingSubjectException(sprintf(
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
            if (($cursor & self::IN_VARIABLE)) {
                throw new InvalidRouteParsingSubjectException(sprintf(
                    'The route %s could not be parsed due to a syntax error. ' .
                    'An unexpected character was found at position %d; a variable at this position must be closed.',
                    $route,
                    $offset,
                ));
            }
        }

        if (($cursor & self::IN_VARIABLE) || ($cursor & self::IN_OPTIONAL_PART)) {
            throw new InvalidRouteParsingSubjectException(sprintf(
                'The route %s could not be parsed due to a syntax error. ' .
                'An attempt to parse the route has failed because it contains an unclosed optional part or variable.',
                $route,
            ));
        }

        /** @var list<array{name: string, pattern?: string, optional?: array{left: string, right: string}, offset: int, length: int}> */
        return $variables;
    }
}
