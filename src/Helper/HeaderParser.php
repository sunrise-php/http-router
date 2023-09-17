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
     * Parses the given "Accept" header or any other semantically similar header, e.g. "Content-Type"
     *
     * @param string $header
     * @param bool $allowRange
     *
     * @return Generator<int, MediaType>
     *
     * @throws InvalidArgumentException If the header isn't valid.
     */
    public static function parseAcceptHeader(string $header, bool $allowRange = true): Generator
    {
        $matches = self::parseAcceptLikeHeader($header);
        foreach ($matches as $index => [$keyword, $parameterNames, $parameterValues]) {
            $range = explode('/', $keyword, 3);

            if ($range[0] === '' || !isset($range[1]) || $range[1] === '' || isset($range[2])) {
                throw new InvalidArgumentException(sprintf(
                    'The media type with index %d has an incorrect format.',
                    $index,
                ));
            }

            if ($range[0] === '*' && $range[1] !== '*') {
                throw new InvalidArgumentException(sprintf(
                    'The media type with index %d has an incorrect range.',
                    $index,
                ));
            }

            if (!$allowRange && ($range[0] === '*' || $range[1] === '*')) {
                throw new InvalidArgumentException(sprintf(
                    'The media type with index %d cannot be a range.',
                    $index,
                ));
            }

            $parameters = [];
            foreach ($parameterNames as $parameterIndex => $parameterName) {
                $parameters[$parameterName] = $parameterValues[$parameterIndex] ?? null;
            }

            yield new MediaType($range[0], $range[1], $parameters);
        }
    }

    /**
     * Parses the given "Accept-Language" header or any other semantically similar header
     *
     * @param string $header
     *
     * @return Generator<int, Language>
     *
     * @throws InvalidArgumentException If the header isn't valid.
     */
    public static function parseAcceptLanguageHeader(string $header): Generator
    {
        $matches = self::parseAcceptLikeHeader($header);
        foreach ($matches as $index => [$keyword, $parameterNames, $parameterValues]) {
            $tags = explode('-', $keyword);

            foreach ($tags as $key => $tag) {
                if ($tag === '') {
                    throw new InvalidArgumentException(sprintf(
                        'The language with index %d has an empty tag with index %d.',
                        $index,
                        $key,
                    ));
                }
            }

            $code = $tags[0];
            unset($tags[0]);

            $parameters = [];
            foreach ($parameterNames as $parameterIndex => $parameterName) {
                $parameters[$parameterName] = $parameterValues[$parameterIndex] ?? null;
            }

            yield new Language($code, [...$tags], $parameters);
        }
    }

    /**
     * Parses the given header, which is semantically similar to the "Accept" header
     *
     * @param string $header
     *
     * @return Generator<int, array{0: string, 1: string[], 2: string[]}>
     *
     * @throws InvalidArgumentException If the header isn't valid.
     */
    private static function parseAcceptLikeHeader(string $header): Generator
    {
        /** @var array{0: ?string, 1: string[], 2: string[]} $match */
        $match = [null, [], []];

        $offset = -1;
        $inKeyword = true;
        $parameterIndex = -1;
        $inParameterName = false;
        $inParameterValue = false;
        $inQuotedString = false;
        $isQuotedChar = false;

        while (isset($header[++$offset])) {
            if (!isset(Charset::RFC7230_FIELD_VALUE[$header[$offset]])) {
                throw new InvalidArgumentException(sprintf(
                    'Unallowed character at position %d.',
                    $offset,
                ));
            }

            if (!$inQuotedString) {
                if ($header[$offset] === ',') {
                    if (!isset($match[0])) {
                        throw new InvalidArgumentException(sprintf(
                            'The character "%s" at position %d must be preceded by a non-empty keyword.',
                            $header[$offset],
                            $offset,
                        ));
                    }

                    yield $match;
                    $match = [null, [], []];
                    $inKeyword = true;
                    $parameterIndex = -1;
                    $inParameterName = false;
                    $inParameterValue = false;
                    continue;
                }
                if ($header[$offset] === ';') {
                    if (!isset($match[0]) || !isset($match[1][$parameterIndex]) && $parameterIndex > -1) {
                        throw new InvalidArgumentException(sprintf(
                            'The character "%s" at position %d must be preceded by a non-empty keyword or parameter.',
                            $header[$offset],
                            $offset,
                        ));
                    }

                    $inKeyword = false;
                    $parameterIndex++;
                    $inParameterName = true;
                    $inParameterValue = false;
                    continue;
                }
                if ($header[$offset] === '=') {
                    if (!isset($match[1][$parameterIndex]) || $inParameterValue || isset($match[2][$parameterIndex])) {
                        throw new InvalidArgumentException(sprintf(
                            'The character "%s" at position %d must be preceded by a non-empty parameter name.',
                            $header[$offset],
                            $offset,
                        ));
                    }

                    $inParameterName = false;
                    $inParameterValue = true;
                    continue;
                }
                if ($header[$offset] === '"') {
                    if (!$inParameterValue || isset($match[2][$parameterIndex])) {
                        throw new InvalidArgumentException(sprintf(
                            'The character "%s" at position %d must be the first in a parameter value.',
                            $header[$offset],
                            $offset,
                        ));
                    }

                    $inQuotedString = true;
                    continue;
                }

                if (isset(Charset::RFC7230_OWS[$header[$offset]])) {
                    if ($inKeyword && isset($match[0])) {
                        $inKeyword = false;
                        continue;
                    }
                    if ($inParameterName && isset($match[1][$parameterIndex])) {
                        $inParameterName = false;
                        continue;
                    }
                    if ($inParameterValue && isset($match[2][$parameterIndex])) {
                        $inParameterValue = false;
                        continue;
                    }

                    // any whitespaces must be ignored...
                    continue;
                }
            }

            if ($inQuotedString && !$isQuotedChar) {
                if ($header[$offset] === '\\') {
                    $isQuotedChar = true;
                    continue;
                }
                if ($header[$offset] === '"') {
                    $inQuotedString = false;
                    $inParameterValue = false;
                    continue;
                }
            }

            if ($inKeyword) {
                $match[0] ??= '';
                $match[0] .= $header[$offset];
                continue;
            }
            if ($inParameterName && isset(Charset::RFC7230_TOKEN[$header[$offset]])) {
                $match[1][$parameterIndex] ??= '';
                $match[1][$parameterIndex] .= $header[$offset];
                continue;
            }
            if ($inParameterValue && (isset(Charset::RFC7230_TOKEN[$header[$offset]])) || $inQuotedString) {
                $match[2][$parameterIndex] ??= '';
                $match[2][$parameterIndex] .= $header[$offset];
                $isQuotedChar = false;
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'Unexpected character at position %d.',
                $offset,
            ));
        }

        if (isset($match[0])) {
            yield $match;
        }
    }
}
