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

namespace Sunrise\Http\Router;

use Sunrise\Http\Router\Dictionary\Charset;
use Sunrise\Http\Router\Exception\InvalidArgumentException;

use function sprintf;

/**
 * Parses the given header
 *
 * @param string $header
 *
 * @return T
 *
 * @throws InvalidArgumentException If the given header is invalid.
 *
 * @template T as list<array{0: non-empty-string, 1?: list<array{0: non-empty-string, 1?: non-empty-string}>}>
 *
 * @since 3.0.0
 */
function parse_header(string $header): array
{
    /** @var T $matches */
    $matches = [];

    $offset = -1;
    $inToken = true;
    $inParamName = false;
    $inParamValue = false;
    $inQuotedString = false;
    $isQuotedChar = false;

    $tokenIndex = 0;
    $paramIndex = 0;

    while (true) {
        $offset++;

        $char = $header[$offset] ?? null;
        if ($char === null) {
            break;
        }

        // en-GB; q=0.8[,] ...
        // ~~~~~~~~~~~~~^~~~~~
        if (!$inQuotedString && $char === ',') {
            $inToken = true;
            $inParamName = false;
            $inParamValue = false;
            $tokenIndex++;
            $paramIndex = 0;
            continue;
        }
        // en-GB[;] q=0.8, ...
        // ~~~~~~^~~~~~~~~~~~~
        if (!$inQuotedString && $char === ';') {
            $inToken = false;
            $inParamName = true;
            $inParamValue = false;
            $paramIndex++;
            continue;
        }
        // en-GB; q[=]0.8, ...
        // ~~~~~~~~~^~~~~~~~~~
        if (!$inQuotedString && $char === '=') {
            $inToken = false;
            $inParamName = false;
            $inParamValue = true;
            continue;
        }

        // en-GB[ ]; q=0.8, ...
        // ~~~~~~^~~~~~~~~~~~~~
        if (!$inQuotedString && $inToken && isset(Charset::RFC7230_OWS[$char]) && isset($matches[$tokenIndex][0][0])) {
            // The cursor is no longer inside the token, as it cannot contain OWS characters...
            $inToken = false;
            continue;
        }
        // en-GB; q[ ]=0.8, ...
        // ~~~~~~~~~^~~~~~~~~~~
        // phpcs:ignore Generic.Files.LineLength
        if (!$inQuotedString && $inParamName && isset(Charset::RFC7230_OWS[$char]) && isset($matches[$tokenIndex][1][$paramIndex][0][0])) {
            // The cursor is no longer inside the parameter name, as it cannot contain OWS characters...
            $inParamName = false;
            continue;
        }
        // en-GB; q=0.8[ ], ...
        // ~~~~~~~~~~~~~^~~~~~~
        // phpcs:ignore Generic.Files.LineLength
        if (!$inQuotedString && $inParamValue && isset(Charset::RFC7230_OWS[$char]) && isset($matches[$tokenIndex][1][$paramIndex][1][0])) {
            // The cursor is no longer inside the parameter value, as it cannot contain OWS characters...
            $inParamValue = false;
            continue;
        }

        // Ignore any OWS characters outside the quoted string...
        if (!$inQuotedString && isset(Charset::RFC7230_OWS[$char])) {
            continue;
        }

        // en-GB; q=["]0.8", ...
        // ~~~~~~~~~~^~~~~~~~~~~
        if (!$inQuotedString && $inParamValue && $char === '"' && !isset($matches[$tokenIndex][1][$paramIndex][1][0])) {
            $inQuotedString = true;
            continue;
        }
        // en-GB; q="0.8["], ...
        // ~~~~~~~~~~~~~~^~~~~~~
        if ($inQuotedString && $inParamValue && !$isQuotedChar && $char === '"') {
            $inParamValue = false;
            $inQuotedString = false;
            continue;
        }

        // en-GB; param="foo [\]"bar[\]" [\]\0", ...
        // ~~~~~~~~~~~~~~~~~~~^~~~~~^~~~~~^~~~~~~~~~
        if ($inQuotedString && !$isQuotedChar && $char === '\\') {
            $nextChar = $header[$offset + 1] ?? null;
            // phpcs:ignore Generic.Files.LineLength
            if (isset($nextChar) && ($nextChar === '\\' || $nextChar === '"' || isset(Charset::RFC7230_QUOTED_STRING[$nextChar]))) {
                $isQuotedChar = true;
                continue;
            }
        }

        // [en-GB]; q=0.8, ...
        // ~^^^^^~~~~~~~~~~~~~
        if ($inToken) { // AS IS
            $matches[$tokenIndex][0] ??= '';
            $matches[$tokenIndex][0] .= $char;
            continue;
        }
        // en-GB; [q]=0.8, ...
        // ~~~~~~~~^~~~~~~~~~~
        if ($inParamName && isset(Charset::RFC7230_TOKEN[$char]) && isset($matches[$tokenIndex][0][0])) {
            $matches[$tokenIndex][1][$paramIndex][0] ??= '';
            $matches[$tokenIndex][1][$paramIndex][0] .= $char;
            continue;
        }
        // en-GB; q=[0.8], ...
        // ~~~~~~~~~~^^^~~~~~~
        // phpcs:ignore Generic.Files.LineLength
        if ($inParamValue && (isset(Charset::RFC7230_TOKEN[$char]) || ($inQuotedString && isset(Charset::RFC7230_QUOTED_STRING[$char])) || $isQuotedChar) && isset($matches[$tokenIndex][1][$paramIndex][0][0])) {
            $matches[$tokenIndex][1][$paramIndex][1] ??= '';
            $matches[$tokenIndex][1][$paramIndex][1] .= $char;
            $isQuotedChar = false;
            continue;
        }

        throw new InvalidArgumentException(sprintf('Unexpected character at position %d.', $offset));
    }

    return $matches;
}
