<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use InvalidArgumentException;

/**
 * Import functions
 */
use function sprintf;

/**
 * Parses the given path
 *
 * @param string $path
 *
 * @return array
 *
 * @throws InvalidArgumentException
 */
function path_parse(string $path) : array
{
    static $cache = [];

    static $allowedAttributeNameChars = [
        '0' => 1, '1' => 1, '2' => 1, '3' => 1, '4' => 1, '5' => 1, '6' => 1, '7' => 1, '8' => 1, '9' => 1,
        'A' => 1, 'B' => 1, 'C' => 1, 'D' => 1, 'E' => 1, 'F' => 1, 'G' => 1, 'H' => 1, 'I' => 1, 'J' => 1,
        'K' => 1, 'L' => 1, 'M' => 1, 'N' => 1, 'O' => 1, 'P' => 1, 'Q' => 1, 'R' => 1, 'S' => 1, 'T' => 1,
        'U' => 1, 'V' => 1, 'W' => 1, 'X' => 1, 'Y' => 1, 'Z' => 1,
        'a' => 1, 'b' => 1, 'c' => 1, 'd' => 1, 'e' => 1, 'f' => 1, 'g' => 1, 'h' => 1, 'i' => 1, 'j' => 1,
        'k' => 1, 'l' => 1, 'm' => 1, 'n' => 1, 'o' => 1, 'p' => 1, 'q' => 1, 'r' => 1, 's' => 1, 't' => 1,
        'u' => 1, 'v' => 1, 'w' => 1, 'x' => 1, 'y' => 1, 'z' => 1,
        '_' => 1,
    ];

    static $charOptionalPieceStart = '(';
    static $charOptionalPieceEnd = ')';
    static $charAttributeStart = '{';
    static $charAttributeEnd = '}';
    static $charPatternStart = '<';
    static $charPatternEnd = '>';

    if (isset($cache[$path])) {
        return $cache[$path];
    }

    $attributes = [];

    $cursorPosition = -1;
    $cursorInOptionalPiece = false;
    $cursorInAttribute = false;
    $cursorInAttributeName = false;
    $cursorInPattern = false;

    $attributeIndex = -1;
    $attributePrototype = [
        'raw' => null,
        'withParentheses' => null,
        'name' => null,
        'pattern' => null,
        'isOptional' => false,
        'startPosition' => -1,
        'endPosition' => -1,
    ];

    $parenthesesBusy = false;
    $parenthesesLeft = null;
    $parenthesesRight = null;

    while (true) {
        $cursorPosition++;

        if (!isset($path[$cursorPosition])) {
            break;
        }

        $char = $path[$cursorPosition];

        if ($charOptionalPieceStart === $char && !$cursorInAttribute) {
            if ($cursorInOptionalPiece) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] parentheses inside parentheses are not allowed.', $path, $cursorPosition)
                );
            }

            $cursorInOptionalPiece = true;

            continue;
        }

        if ($charAttributeStart === $char && !$cursorInPattern) {
            if ($cursorInAttribute) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] braces inside braces are not allowed.', $path, $cursorPosition)
                );
            }

            if ($parenthesesBusy) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] multiple attributes inside parentheses are not allowed.', $path, $cursorPosition)
                );
            }

            if ($cursorInOptionalPiece) {
                $parenthesesBusy = true;
            }

            $cursorInAttribute = true;
            $cursorInAttributeName = true;
            $attributeIndex++;
            $attributes[$attributeIndex] = $attributePrototype;
            $attributes[$attributeIndex]['raw'] .= $char;
            $attributes[$attributeIndex]['isOptional'] = $cursorInOptionalPiece;
            $attributes[$attributeIndex]['startPosition'] = $cursorPosition;

            continue;
        }

        if ($charPatternStart === $char && $cursorInAttribute) {
            if ($cursorInPattern) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] less than char inside a pattern is not allowed.', $path, $cursorPosition)
                );
            }

            $cursorInPattern = true;
            $cursorInAttributeName = false;
            $attributes[$attributeIndex]['raw'] .= $char;

            continue;
        }

        if ($charPatternEnd === $char && $cursorInAttribute) {
            if (!$cursorInPattern) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] greater than char inside a pattern is not allowed.', $path, $cursorPosition)
                );
            }

            if (null === $attributes[$attributeIndex]['pattern']) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] an attribute pattern is empty.', $path, $cursorPosition)
                );
            }

            $cursorInPattern = false;
            $attributes[$attributeIndex]['raw'] .= $char;

            continue;
        }

        if ($charAttributeEnd === $char && !$cursorInPattern) {
            if (!$cursorInAttribute) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] at position %2$d an extra closing brace was found.', $path, $cursorPosition)
                );
            }

            if (null === $attributes[$attributeIndex]['name']) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] an attribute name is empty.', $path, $cursorPosition)
                );
            }

            $cursorInAttribute = false;
            $cursorInAttributeName = false;
            $attributes[$attributeIndex]['raw'] .= $char;
            $attributes[$attributeIndex]['endPosition'] = $cursorPosition;

            continue;
        }

        if ($charOptionalPieceEnd === $char && !$cursorInAttribute) {
            if (!$cursorInOptionalPiece) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] at position %2$d an extra closing parenthesis was found.', $path, $cursorPosition)
                );
            }

            if ($parenthesesBusy) {
                $attributes[$attributeIndex]['withParentheses'] = '(' . $parenthesesLeft;
                $attributes[$attributeIndex]['withParentheses'] .= $attributes[$attributeIndex]['raw'];
                $attributes[$attributeIndex]['withParentheses'] .= $parenthesesRight . ')';
            }

            $cursorInOptionalPiece = false;
            $parenthesesBusy = false;
            $parenthesesLeft = null;
            $parenthesesRight = null;

            continue;
        }

        if ($cursorInAttribute) {
            $attributes[$attributeIndex]['raw'] .= $char;
        }

        if ($cursorInAttributeName) {
            if (!isset($allowedAttributeNameChars[$char])) {
                throw new InvalidArgumentException(
                    sprintf('[%s:%d] an attribute name contains invalid character.', $path, $cursorPosition)
                );
            }

            $attributes[$attributeIndex]['name'] .= $char;
        }

        if ($cursorInPattern) {
            $attributes[$attributeIndex]['pattern'] .= $char;
        }

        if ($cursorInOptionalPiece && !$cursorInAttribute && !$parenthesesBusy) {
            $parenthesesLeft .= $char;
        }

        if ($cursorInOptionalPiece && !$cursorInAttribute && $parenthesesBusy) {
            $parenthesesRight .= $char;
        }
    }

    if ($cursorInOptionalPiece) {
        throw new InvalidArgumentException(
            sprintf('[%s] the route path contains non-closed parentheses.', $path)
        );
    }

    if ($cursorInAttribute) {
        throw new InvalidArgumentException(
            sprintf('[%s] the route path contains non-closed attribute.', $path)
        );
    }

    if ($cursorInPattern) {
        throw new InvalidArgumentException(
            sprintf('[%s] the route path contains non-closed pattern.', $path)
        );
    }

    $cache[$path] = $attributes;

    return $attributes;
}
