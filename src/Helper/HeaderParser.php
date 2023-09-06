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

use Generator;
use Sunrise\Http\Router\Dictionary\Charset;
use Sunrise\Http\Router\Entity\Language;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Exception\InvalidArgumentException;

use function explode;
use function sprintf;

/**
 * Internal header parser
 *
 * @internal
 */
final class HeaderParser
{

    /**
     * @param string $header
     *
     * @return Generator<MediaType>
     *
     * @throws InvalidArgumentException If the header isn't valid.
     */
    public static function parseAcceptHeader(string $header): Generator
    {
        $matches = self::parseHeader($header);
        foreach ($matches as $match) {
            $range = explode('/', $match[0], 2);

            yield new MediaType($range[0], $range[1] ?? '*', $match[1]);
        }
    }

    /**
     * @param string $header
     *
     * @return Generator<Language>
     *
     * @throws InvalidArgumentException If the header isn't valid.
     */
    public static function parseAcceptLanguageHeader(string $header): Generator
    {
        $matches = self::parseHeader($header);
        foreach ($matches as $match) {
            $tags = explode('-', $match[0]);

            $tag = $tags[0];
            unset($tags[0]);

            yield new Language($tag, [...$tags], $match[1]);
        }
    }

    /**
     * @param string $header
     *
     * @return MediaType|null
     *
     * @throws InvalidArgumentException If the header isn't valid.
     */
    public static function parseContentTypeHeader(string $header): ?MediaType
    {
        $matches = self::parseHeader($header);
        if (!$matches->valid()) {
            return null;
        }

        $match = $matches->current();

        $range = explode('/', $match[0], 2);

        return new MediaType($range[0], $range[1] ?? '*', $match[1]);
    }

    /**
     * Parses the given header, such as Accept, Content-Type, etc.
     *
     * @param string $header
     *
     * @return Generator<int, array{0: string, 1: array<string, ?string>}>
     *
     * @throws InvalidArgumentException If the header isn't valid.
     */
    private static function parseHeader(string $header): Generator
    {
        /**
         * Prepares the given match for the return
         *
         * @param array{0: string, 1?: array<int, array{0: string, 1?: string}>} $match
         *
         * @return array{0: string, 1: array<string, ?string>}
         */
        $return = static function (array $match): array {
            $params = [];
            foreach ($match[1] ?? [] as $param) {
                $params[$param[0]] = $param[1] ?? null;
            }

            return [$match[0], $params];
        };

        /** @var array{0?: string, 1?: array<int, array{0: string, 1?: string}>} $match */
        $match = [];

        $inKeyword = true;
        $paramIndex = -1;
        $inParamName = false;
        $inParamValue = false;
        $inQuotedString = false;
        $isQuotedChar = false;

        $offset = -1;
        while (isset($header[++$offset])) {
            // https://tools.ietf.org/html/rfc7230#section-3.2
            if (!isset(Charset::RFC7230_FIELD_VALUE[$header[$offset]])) {
                throw new InvalidArgumentException(sprintf('Invalid character at position %d.', $offset));
            }

            // en-GB; q=0.9[,]...
            // ~~~~~~~~~~~~~^~~~~
            if (!$inQuotedString && $header[$offset] === ',') {
                if (isset($match[0])) {
                    yield $return($match);
                }

                $match = [];
                $inKeyword = true;
                $paramIndex = 0;
                $inParamName = false;
                $inParamValue = false;
                continue;
            }
            // en-GB[;] q=0.9,...
            // ~~~~~~^~~~~~~~~~~~
            if (!$inQuotedString && $header[$offset] === ';') {
                $inKeyword = false;
                $paramIndex++;
                $inParamName = true;
                $inParamValue = false;
                continue;
            }
            // en-GB; q[=]0.9,...
            // ~~~~~~~~~^~~~~~~~~
            if ($inParamName && $header[$offset] === '=') {
                $inParamName = false;
                $inParamValue = true;
                continue;
            }

            // en-GB[ ]; q=0.9,...
            // ~~~~~~^~~~~~~~~~~~~
            if ($inKeyword && isset(Charset::RFC7230_OWS[$header[$offset]]) && isset($match[0])) {
                $inKeyword = false;
                continue;
            }
            // en-GB; q[ ]=0.9,...
            // ~~~~~~~~~^~~~~~~~~~
            if ($inParamName && isset(Charset::RFC7230_OWS[$header[$offset]]) && isset($match[1][$paramIndex][0])) {
                $inParamName = false;
                continue;
            }
            // en-GB; q=0.9[ ],...
            // ~~~~~~~~~~~~~^~~~~~
            // phpcs:ignore Generic.Files.LineLength
            if ($inParamValue && !$inQuotedString && isset(Charset::RFC7230_OWS[$header[$offset]]) && isset($match[1][$paramIndex][1])) {
                $inParamValue = false;
                continue;
            }

            // [ ]en-GB;[ ]q=[ ]0.9,...
            // ~^~~~~~~~~^~~~~^~~~~~~~~
            if (!$inQuotedString && isset(Charset::RFC7230_OWS[$header[$offset]])) {
                continue;
            }

            // en-GB; q=["]0.9",...
            // ~~~~~~~~~~^~~~~~~~~~
            if ($inParamValue && !$inQuotedString && $header[$offset] === '"' && !isset($match[1][$paramIndex][1])) {
                $inQuotedString = true;
                continue;
            }
            // en-US; foo="[\\][\"][\*]",...
            // ~~~~~~~~~~~~~^^~~^^~~^^~~~~~~
            // phpcs:ignore Generic.Files.LineLength
            if ($inQuotedString && !$isQuotedChar && $header[$offset] === '\\' && isset($header[$offset + 1]) && ($header[$offset + 1] === '\\' || $header[$offset + 1] === '"' || isset(Charset::RFC7230_QUOTED_STRING[$header[$offset + 1]]))) {
                $isQuotedChar = true;
                continue;
            }
            // en-GB; q="0.9["],...
            // ~~~~~~~~~~~~~~^~~~~~
            if ($inQuotedString && !$isQuotedChar && $header[$offset] === '"') {
                $inParamValue = false;
                $inQuotedString = false;
                continue;
            }

            // [en-GB]; q=0.9,...
            // ~^^^^^~~~~~~~~~~~~
            if ($inKeyword) { // AS IS
                $match[0] ??= '';
                $match[0] .= $header[$offset];
                continue;
            }
            // en-GB; [q]=0.9,...
            // ~~~~~~~~^~~~~~~~~~
            if ($inParamName && isset(Charset::RFC7230_TOKEN[$header[$offset]]) && isset($match[0])) {
                $match[1][$paramIndex][0] ??= '';
                $match[1][$paramIndex][0] .= $header[$offset];
                continue;
            }
            // en-GB; q=[0.9],...
            // ~~~~~~~~~~^^^~~~~~
            // phpcs:ignore Generic.Files.LineLength
            if ($inParamValue && (isset(Charset::RFC7230_TOKEN[$header[$offset]]) || ($inQuotedString && isset(Charset::RFC7230_QUOTED_STRING[$header[$offset]])) || $isQuotedChar) && isset($match[1][$paramIndex][0])) {
                $match[1][$paramIndex][1] ??= '';
                $match[1][$paramIndex][1] .= $header[$offset];
                $isQuotedChar = false;
                continue;
            }

            throw new InvalidArgumentException(sprintf('Unexpected character at position %d.', $offset));
        }

        if (isset($match[0])) {
            yield $return($match);
        }
    }
}
