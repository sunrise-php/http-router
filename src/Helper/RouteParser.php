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
     * @link https://www.pcre.org/original/doc/html/pcrepattern.html#SEC16
     */
    private const PCRE_SUBPATTERN_NAME_CHARSET = [
        "\x30" => 1, "\x31" => 1, "\x32" => 1, "\x33" => 1, "\x34" => 1, "\x35" => 1, "\x36" => 1, "\x37" => 1,
        "\x38" => 1, "\x39" => 1, "\x41" => 1, "\x42" => 1, "\x43" => 1, "\x44" => 1, "\x45" => 1, "\x46" => 1,
        "\x47" => 1, "\x48" => 1, "\x49" => 1, "\x4a" => 1, "\x4b" => 1, "\x4c" => 1, "\x4d" => 1, "\x4e" => 1,
        "\x4f" => 1, "\x50" => 1, "\x51" => 1, "\x52" => 1, "\x53" => 1, "\x54" => 1, "\x55" => 1, "\x56" => 1,
        "\x57" => 1, "\x58" => 1, "\x59" => 1, "\x5a" => 1, "\x5f" => 1, "\x61" => 1, "\x62" => 1, "\x63" => 1,
        "\x64" => 1, "\x65" => 1, "\x66" => 1, "\x67" => 1, "\x68" => 1, "\x69" => 1, "\x6a" => 1, "\x6b" => 1,
        "\x6c" => 1, "\x6d" => 1, "\x6e" => 1, "\x6f" => 1, "\x70" => 1, "\x71" => 1, "\x72" => 1, "\x73" => 1,
        "\x74" => 1, "\x75" => 1, "\x76" => 1, "\x77" => 1, "\x78" => 1, "\x79" => 1, "\x7a" => 1,
    ];

    /**
     * Parses the given route and returns its variables
     *
     * @return list<array{statement: string, name: string, pattern?: string, optional_part?: string}>
     *
     * @throws InvalidArgumentException
     */
    public static function parseRoute(string $route): array
    {
        $cursor = 0;
        $variable = -1;

        /** @var list<array{statement?: string, name?: string, pattern?: string, optional_part?: string}> $variables */
        $variables = [];

        /** @var array<string, true> $names */
        $names = [];

        $left = $right = '';

        for ($offset = 0; isset($route[$offset]); $offset++) {
            if ($route[$offset] === '(' && !($cursor & self::IN_VARIABLE)) {
                if (($cursor & self::IN_OPTIONAL_PART)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to open an optional part at position %d failed ' .
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
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to close an optional part at position %d failed ' .
                        'because an open optional part was not found.',
                        $route,
                        $offset,
                    ));
                }

                if (($cursor & self::IN_OCCUPIED_PART)) {
                    $cursor &= ~self::IN_OCCUPIED_PART;
                    // phpcs:ignore Generic.Files.LineLength.TooLong
                    $variables[$variable]['optional_part'] = '(' . $left . ($variables[$variable]['statement'] ?? '') . $right . ')';
                }

                $cursor &= ~self::IN_OPTIONAL_PART;
                $left = $right = '';
                continue;
            }

            if ($route[$offset] === '{' && !($cursor & self::IN_VARIABLE_PATTERN)) {
                if (($cursor & self::IN_VARIABLE)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to open a variable at position %d failed ' .
                        'because nested variables are not supported.',
                        $route,
                        $offset,
                    ));
                }
                if (($cursor & self::IN_OCCUPIED_PART)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to open a variable at position %d failed ' .
                        'because more than one variable inside an optional part is not supported.',
                        $route,
                        $offset,
                    ));
                }

                if (($cursor & self::IN_OPTIONAL_PART)) {
                    $cursor |= self::IN_OCCUPIED_PART;
                }

                $cursor |= self::IN_VARIABLE | self::IN_VARIABLE_NAME;
                $variable++;
                continue;
            }
            if ($route[$offset] === '}' && !($cursor & self::IN_VARIABLE_PATTERN)) {
                if (!($cursor & self::IN_VARIABLE)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to close a variable at position %d failed ' .
                        'because an open variable was not found.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset($variables[$variable]['name'])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to close a variable at position %d failed ' .
                        'because its name is required for its declaration.',
                        $route,
                        $offset,
                    ));
                }
                if (isset($names[$variables[$variable]['name']])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" at position %d could not be parsed ' .
                        'because the variable name "%s" is already in use.',
                        $route,
                        $offset,
                        $variables[$variable]['name'],
                    ));
                }

                $cursor &= ~(self::IN_VARIABLE | self::IN_VARIABLE_NAME);
                $variables[$variable]['statement'] = '{' . ($variables[$variable]['statement'] ?? '') . '}';
                $names[$variables[$variable]['name']] = true; // @phpstan-ignore-line
                continue;
            }

            if (($cursor & self::IN_VARIABLE)) {
                $variables[$variable]['statement'] ??= '';
                $variables[$variable]['statement'] .= $route[$offset];
            }

            if ($route[$offset] === '<' && ($cursor & self::IN_VARIABLE)) {
                if (($cursor & self::IN_VARIABLE_PATTERN)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to open a variable pattern at position %d failed ' .
                        'because nested patterns are not supported.',
                        $route,
                        $offset,
                    ));
                }
                if (!($cursor & self::IN_VARIABLE_NAME)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to open a variable pattern at position %d failed ' .
                        'because the pattern must be preceded by the variable name.',
                        $route,
                        $offset,
                    ));
                }

                $cursor = $cursor & ~self::IN_VARIABLE_NAME | self::IN_VARIABLE_PATTERN;
                continue;
            }
            if ($route[$offset] === '>' && ($cursor & self::IN_VARIABLE)) {
                if (!($cursor & self::IN_VARIABLE_PATTERN)) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to close a variable pattern at position %d failed ' .
                        'because an open pattern was not found.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset($variables[$variable]['pattern'])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'The attempt to close a variable pattern at position %d failed ' .
                        'because its content is required for its declaration.',
                        $route,
                        $offset,
                    ));
                }

                $cursor &= ~self::IN_VARIABLE_PATTERN;
                continue;
            }

            // (left{var}right)
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
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'An invalid character was found at position %d. ' .
                        'Please note that variable names cannot start with digits.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset(self::PCRE_SUBPATTERN_NAME_CHARSET[$route[$offset]])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'An invalid character was found at position %d. ' .
                        'Please note that variable names must consist only of digits, letters and underscores.',
                        $route,
                        $offset,
                    ));
                }
                if (isset($variables[$variable]['name'][31])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
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
                if ($route[$offset] === RouteCompiler::EXPRESSION_DELIMITER) {
                    throw new InvalidArgumentException(sprintf(
                        'The route "%s" could not be parsed due to a syntax error. ' .
                        'An invalid character was found at position %d. ' .
                        'Please note that variable patterns cannot contain the character "%s"; ' .
                        'use an octal or hexadecimal sequence instead.',
                        $route,
                        $offset,
                        RouteCompiler::EXPRESSION_DELIMITER,
                    ));
                }

                $variables[$variable]['pattern'] ??= '';
                $variables[$variable]['pattern'] .= $route[$offset];
                continue;
            }

            // {var<\w+>xxx}
            // ~~~~~~~~~^^^~
            if (($cursor & self::IN_VARIABLE)) {
                throw new InvalidArgumentException(sprintf(
                    'The route "%s" could not be parsed due to a syntax error. ' .
                    'An unexpected character was found at position %d; ' .
                    'a variable at this position must be closed.',
                    $route,
                    $offset,
                ));
            }
        }

        if (($cursor & self::IN_VARIABLE) || ($cursor & self::IN_OPTIONAL_PART)) {
            throw new InvalidArgumentException(sprintf(
                'The route "%s" could not be parsed due to a syntax error. ' .
                'The attempt to parse the route failed ' .
                'because it contains an unclosed variable or optional part.',
                $route,
            ));
        }

        /** @var list<array{statement: string, name: string, pattern?: string, optional_part?: string}> */
        return $variables;
    }
}
