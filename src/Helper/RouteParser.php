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
use Sunrise\Http\Router\Router;

use function sprintf;

/**
 * Internal route parser
 *
 * @internal
 */
final class RouteParser
{

    /**
     * Parses the given route
     *
     * ```
     * (/{locale=en<[a-z]{2}>})/post/{id<[1-9][0-9]*>}
     * ```
     *
     * @param string $route
     *
     * @return list<TMatch>
     *
     * @throws InvalidArgumentException If the route isn't valid.
     *
     * @template TMatch as array{
     *     name: string,
     *     value?: string,
     *     pattern?: string,
     *     isOptional?: true,
     *     optionalLeftPart?: ?string,
     *     optionalRightPart?: ?string
     * }
     */
    public static function parseRoute(string $route): array
    {
        /**
         * @var list<TRawMatch> $matches
         *
         * @template TRawMatch as array{
         *     name?: string,
         *     value?: string,
         *     pattern?: string,
         *     isOptional?: true,
         *     optionalLeftPart?: ?string,
         *     optionalRightPart?: ?string
         * }
         */
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
            if (!$inVariable) {
                if ($route[$offset] === '(') {
                    if ($inOptionalPart) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to open an optional part at position %d has failed, ' .
                            'because nested optional parts are not supported.',
                            $offset,
                        ));
                    }

                    $inOptionalPart = true;
                    continue;
                }
                if ($route[$offset] === ')') {
                    if (!$inOptionalPart) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to close an optional part at position %d has failed, ' .
                            'because an open optional part was not found.',
                            $offset,
                        ));
                    }

                    if ($optionalPartIsOccupied) {
                        $matches[$index]['isOptional'] = true;
                        $matches[$index]['optionalLeftPart'] = $optionalPartBeforeVariable;
                        $matches[$index]['optionalRightPart'] = $optionalPartAfterVariable;
                    }

                    $inOptionalPart = false;
                    $optionalPartIsOccupied = false;
                    $optionalPartBeforeVariable = null;
                    $optionalPartAfterVariable = null;
                    continue;
                }
            }

            if (!$inVariablePattern) {
                if ($route[$offset] === '{') {
                    if ($inVariable) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to open a variable at position %d has failed, ' .
                            'because nested variables are not supported.',
                            $offset,
                        ));
                    }
                    if ($inOptionalPart && $optionalPartIsOccupied) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to open a variable at position %d has failed, ' .
                            'because more than one variable inside an optional part is not allowed',
                            $offset,
                        ));
                    }

                    $index++;
                    $inVariable = true;
                    $inVariableName = true;
                    if ($inOptionalPart) {
                        $optionalPartIsOccupied = true;
                    }
                    continue;
                }
                if ($route[$offset] === '}') {
                    if (!$inVariable) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to close a variable at position %d has failed, ' .
                            'because an open variable was not found.',
                            $offset,
                        ));
                    }
                    if (!isset($matches[$index]['name'])) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to close a variable at position %d has failed, ' .
                            'because its name is required for its declaration.',
                            $offset,
                        ));
                    }

                    $inVariable = false;
                    $inVariableName = false;
                    $inVariableValue = false;
                    continue;
                }
            }

            if ($inVariable) {
                if (!$inVariablePattern) {
                    if ($route[$offset] === '=') {
                        if (!$inVariableName) {
                            throw new InvalidArgumentException(sprintf(
                                'An attempt to assign a value at position %d has failed, ' .
                                'because a variable value must be preceded by its name.',
                                $offset,
                            ));
                        }

                        $matches[$index]['value'] = '';

                        $inVariableName = false;
                        $inVariableValue = true;
                        continue;
                    }
                }
                if ($route[$offset] === '<') {
                    if ($inVariablePattern) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to open a pattern at position %d has failed, ' .
                            'because nested patterns are not supported.',
                            $offset,
                        ));
                    }
                    if (isset($matches[$index]['pattern'])) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to open a pattern at position %d has failed, ' .
                            'because more than one pattern inside a variable is not allowed.',
                            $offset,
                        ));
                    }

                    $inVariableName = false;
                    $inVariableValue = false;
                    $inVariablePattern = true;
                    continue;
                }
                if ($route[$offset] === '>') {
                    if (!$inVariablePattern) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to close a pattern at position %d has failed, ' .
                            'because an open pattern was not found.',
                            $offset,
                        ));
                    }
                    if (!isset($matches[$index]['pattern'])) {
                        throw new InvalidArgumentException(sprintf(
                            'An attempt to close a pattern at position %d has failed, ' .
                            'because its content is required for its declaration.',
                            $offset,
                        ));
                    }

                    if ($matches[$index]['pattern'][0] === '@') {
                        if (!isset(Router::$patterns[$matches[$index]['pattern']])) {
                            throw new InvalidArgumentException(sprintf(
                                'An attempt to close a pattern at position %2$d has failed, ' .
                                'because the named pattern "%1$s" was not found.',
                                $matches[$index]['pattern'],
                                $offset,
                            ));
                        }

                        $matches[$index]['pattern'] = Router::$patterns[$matches[$index]['pattern']];
                    }

                    $inVariablePattern = false;
                    continue;
                }
            }

            if ($inVariable) {
                if ($inVariableName) {
                    if (isset($matches[$index]['name'][31])) {
                        throw new InvalidArgumentException(sprintf(
                            'An extra character was found at position %d. ' .
                            'Please note that variable names must not exceed 32 characters.',
                            $offset,
                        ));
                    }
                    if (!isset(Charset::PCRE_SUBPATTERN_NAME[$route[$offset]])) {
                        throw new InvalidArgumentException(sprintf(
                            'An invalid character was found at position %d. ' .
                            'Please note that variable names must consist only of digits, letters and underscores.',
                            $offset,
                        ));
                    }
                    /** @psalm-suppress InvalidArrayOffset */
                    if (!isset($matches[$index]['name']) && isset(Charset::DIGIT[$route[$offset]])) {
                        throw new InvalidArgumentException(sprintf(
                            'An invalid character was found at position %d. ' .
                            'Please note that variable names cannot start with digits.',
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
                    if ($route[$offset] === '#') {
                        throw new InvalidArgumentException(sprintf(
                            'An unallowed character was found at position %d. ' .
                            'Please note that variable patterns cannot contain the hash character; ' .
                            'use an octal or hexadecimal sequence instead.',
                            $offset,
                        ));
                    }

                    $matches[$index]['pattern'] ??= '';
                    $matches[$index]['pattern'] .= $route[$offset];
                    continue;
                }

                throw new InvalidArgumentException(sprintf(
                    'An unexpected character was found at position %d; ' .
                    'a variable at this position must be closed.',
                    $offset,
                ));
            }

            if ($inOptionalPart) {
                if ($optionalPartIsOccupied) {
                    $optionalPartAfterVariable ??= '';
                    $optionalPartAfterVariable .= $route[$offset];
                } else {
                    $optionalPartBeforeVariable ??= '';
                    $optionalPartBeforeVariable .= $route[$offset];
                }

                continue;
            }
        }

        if ($inOptionalPart) {
            throw new InvalidArgumentException(
                'An attempt to parse the route has failed, ' .
                'because it contains an unclosed optional part.'
            );
        }
        if ($inVariable) {
            throw new InvalidArgumentException(
                'An attempt to parse the route has failed, ' .
                'because it contains an unclosed variable.'
            );
        }

        /** @var list<TMatch> */
        return $matches;
    }
}
