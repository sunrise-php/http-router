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
 * Internal route parser
 *
 * @since 3.0.0
 */
final class RouteParser
{

    /**
     * Parses the given route and returns its variables
     *
     * @param string $route
     *
     * @return list<array{
     *     name: string,
     *     value?: string,
     *     pattern?: string,
     *     optional?: true,
     *     before?: ?string,
     *     after?: ?string
     * }>
     *
     * @throws InvalidArgumentException If the route isn't valid.
     */
    public static function parseRoute(string $route): array
    {
        // phpcs:ignore Generic.Files.LineLength
        /** @var list<array{name?: string, value?: string, pattern?: string, optional?: true, before?: ?string, after?: ?string}> $matches */
        $matches = [];

        $index = -1;
        $inVariable = false;
        $inVariableName = false;
        $inVariableValue = false;
        $inVariablePattern = false;

        $inOptionalPart = false;
        $optionalPartIsOccupied = false;
        $optionalPartBeforeVariable = null;
        $optionalPartAfterVariable = null;

        $offset = -1;
        while (isset($route[++$offset])) {
            if ($route[$offset] === '(' && !$inVariable) {
                if ($inOptionalPart) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open an optional part at position %d has failed, ' .
                        'because nested optional parts are not supported.' .
                        $route,
                        $offset,
                    ));
                }

                $inOptionalPart = true;
                continue;
            }
            if ($route[$offset] === ')' && !$inVariable) {
                if (!$inOptionalPart) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close an optional part at position %d has failed, ' .
                        'because an open optional part was not found.',
                        $route,
                        $offset,
                    ));
                }
                if ($optionalPartIsOccupied) {
                    $matches[$index]['optional'] = true;
                    $matches[$index]['before'] = $optionalPartBeforeVariable;
                    $matches[$index]['after'] = $optionalPartAfterVariable;
                }

                $inOptionalPart = false;
                $optionalPartIsOccupied = false;
                $optionalPartBeforeVariable = null;
                $optionalPartAfterVariable = null;
                continue;
            }
            if ($route[$offset] === '{' && !$inVariablePattern) {
                if ($inVariable) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable at position %d has failed, ' .
                        'because nested variables are not supported.',
                        $route,
                        $offset,
                    ));
                }
                if ($inOptionalPart && $optionalPartIsOccupied) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a variable at position %d has failed, ' .
                        'because more than one variable inside an optional part is not allowed.',
                        $route,
                        $offset,
                    ));
                }
                if ($inOptionalPart) {
                    $optionalPartIsOccupied = true;
                }

                $index++;
                $inVariable = true;
                $inVariableName = true;
                continue;
            }
            if ($route[$offset] === '}' && !$inVariablePattern) {
                if (!$inVariable) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable at position %d has failed, ' .
                        'because an open variable was not found.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset($matches[$index]['name'])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a variable at position %d has failed, ' .
                        'because its name is required for its declaration.',
                        $route,
                        $offset,
                    ));
                }

                $inVariable = false;
                $inVariableName = false;
                $inVariableValue = false;
                continue;
            }
            if ($route[$offset] === '<' && $inVariable) {
                if ($inVariablePattern) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a pattern at position %d has failed, ' .
                        'because nested patterns are not supported.',
                        $route,
                        $offset,
                    ));
                }
                if (!$inVariableName && !$inVariableValue) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to open a pattern at position %d has failed, ' .
                        'because a variable pattern must be preceded by its name or value.',
                        $route,
                        $offset,
                    ));
                }

                $inVariableName = false;
                $inVariableValue = false;
                $inVariablePattern = true;
                continue;
            }
            if ($route[$offset] === '>' && $inVariable) {
                if (!$inVariablePattern) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a pattern at position %d has failed, ' .
                        'because an open pattern was not found.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset($matches[$index]['pattern'])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to close a pattern at position %d has failed, ' .
                        'because its content is required for its declaration.',
                        $route,
                        $offset,
                    ));
                }

                $inVariablePattern = false;
                continue;
            }
            if ($route[$offset] === '=' && $inVariable && !$inVariablePattern) {
                if (!$inVariableName) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An attempt to assign a value at position %d has failed, ' .
                        'because a variable value must be preceded by its name.',
                        $route,
                        $offset,
                    ));
                }

                $matches[$index]['value'] = '';

                $inVariableName = false;
                $inVariableValue = true;
                continue;
            }

            if ($inVariableName) {
                /** @psalm-suppress InvalidArrayOffset */
                if (!isset($matches[$index]['name']) && isset(Charset::DIGIT[$route[$offset]])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An unallowed character was found at position %d. ' .
                        'Please note that variable names cannot start with digits.',
                        $route,
                        $offset,
                    ));
                }
                if (!isset(Charset::PCRE_SUBPATTERN_NAME[$route[$offset]])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An unallowed character was found at position %d. ' .
                        'Please note that variable names must consist only of digits, letters and underscores.',
                        $route,
                        $offset,
                    ));
                }
                if (isset($matches[$index]['name'][31])) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An extra character was found at position %d. ' .
                        'Please note that variable names must not exceed 32 characters.',
                        $route,
                        $offset,
                    ));
                }

                $matches[$index]['name'] ??= '';
                $matches[$index]['name'] .= $route[$offset];
                continue;
            }

            if ($inVariableValue) {
                $matches[$index]['value'] ??= '';
                $matches[$index]['value'] .= $route[$offset];
                continue;
            }

            if ($inVariablePattern) {
                // the char is reserved as a route regex delimiter...
                if ($route[$offset] === '#') {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be parsed due to a syntax error. ' .
                        'An unallowed character was found at position %d. ' .
                        'Please note that variable patterns cannot contain the hash character; ' .
                        'use an octal or hexadecimal sequence instead.',
                        $route,
                        $offset,
                    ));
                }

                $matches[$index]['pattern'] ??= '';
                $matches[$index]['pattern'] .= $route[$offset];
                continue;
            }

            if ($inVariable) {
                throw new InvalidArgumentException(sprintf(
                    'The route %s could not be parsed due to a syntax error. ' .
                    'An unexpected character was found at position %d; ' .
                    'a variable at this position must be closed.',
                    $route,
                    $offset,
                ));
            }

            if ($inOptionalPart) {
                if (!$optionalPartIsOccupied) {
                    $optionalPartBeforeVariable ??= '';
                    $optionalPartBeforeVariable .= $route[$offset];
                } else {
                    $optionalPartAfterVariable ??= '';
                    $optionalPartAfterVariable .= $route[$offset];
                }
            }
        }

        if ($inVariable || $inOptionalPart) {
            throw new InvalidArgumentException(sprintf(
                'The route %s could not be parsed due to a syntax error. ' .
                'An attempt to parse the route has failed, ' .
                'because it contains an unclosed optional part or variable.',
                $route,
            ));
        }

        // phpcs:ignore Generic.Files.LineLength
        /** @var list<array{name: string, value?: string, pattern?: string, optional?: true, before?: ?string, after?: ?string}> $matches */

        return $matches;
    }

    /**
     * Builds a variable from its given metadata
     *
     * @param array{
     *     name: string,
     *     value?: string,
     *     pattern?: string,
     *     optional?: true,
     *     before?: ?string,
     *     after?: ?string
     * } $metadata
     *
     * @param bool $withOptionalPart
     *
     * @return string
     */
    public static function buildVariable(array $metadata, bool $withOptionalPart = false): string
    {
        $variable = '{' . $metadata['name'];

        if (isset($metadata['value'])) {
            $variable .= '=' . $metadata['value'];
        }
        if (isset($metadata['pattern'])) {
            $variable .= '<' . $metadata['pattern'] . '>';
        }

        $variable .= '}';

        if (isset($metadata['optional']) && $withOptionalPart) {
            $variable = '(' . ($metadata['before'] ?? '') . $variable . ($metadata['after'] ?? '') . ')';
        }

        return $variable;
    }
}
