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
use Sunrise\Http\Router\Entity\Encoding;
use Sunrise\Http\Router\Entity\Language;
use Sunrise\Http\Router\Entity\MediaType;

use function preg_match;
use function reset;
use function trim;
use function usort;

/**
 * @since 3.0.0
 */
final class HeaderParser
{
    /**
     * @return Generator<int<0, max>, Encoding>
     */
    public static function parseContentEncodingHeader(string $header): Generator
    {
        $values = self::parseHeader($header);
        if ($values === []) {
            return;
        }

        foreach ($values as $index => [$identifier]) {
            yield $index => new Encoding($identifier);
        }
    }

    public static function parseContentTypeHeader(string $header): ?MediaType
    {
        $values = self::parseHeader($header);
        if ($values === []) {
            return null;
        }

        [$identifier, $parameters] = reset($values);

        if (preg_match('|^([^/*]+)/([^/*]+)$|', $identifier, $matches)) {
            return new MediaType($matches[1], $matches[2], $parameters);
        }

        return null;
    }

    /**
     * @return Generator<int<0, max>, MediaType>
     */
    public static function parseAcceptHeader(string $header): Generator
    {
        $values = self::parseHeader($header);
        if ($values === []) {
            return;
        }

        usort($values, static fn(array $a, array $b): int => (
            (float) ($b[1]['q'] ?? '1') <=> (float) ($a[1]['q'] ?? '1')
        ));

        foreach ($values as $index => [$identifier, $parameters]) {
            if (preg_match('|^([^/]+)/([^/]+)$|', $identifier, $matches)) {
                yield $index => new MediaType($matches[1], $matches[2], $parameters);
            }
        }
    }

    /**
     * @return Generator<int<0, max>, Encoding>
     */
    public static function parseAcceptEncodingHeader(string $header): Generator
    {
        $values = self::parseHeader($header);
        if ($values === []) {
            return;
        }

        usort($values, static fn(array $a, array $b): int => (
            (float) ($b[1]['q'] ?? '1') <=> (float) ($a[1]['q'] ?? '1')
        ));

        foreach ($values as $index => [$identifier, $parameters]) {
            yield $index => new Encoding($identifier, $parameters);
        }
    }

    /**
     * @return Generator<int<0, max>, Language>
     */
    public static function parseAcceptLanguageHeader(string $header): Generator
    {
        $values = self::parseHeader($header);
        if ($values === []) {
            return;
        }

        usort($values, static fn(array $a, array $b): int => (
            (float) ($b[1]['q'] ?? '1') <=> (float) ($a[1]['q'] ?? '1')
        ));

        foreach ($values as $index => [$identifier, $parameters]) {
            if (preg_match('|^(?:i-)?([^-]+)(?:-[^-]+)*$|', $identifier, $matches)) {
                yield $index => new Language($matches[1], $identifier, $parameters);
            }
        }
    }

    /**
     * @return array<int<0, max>, array{0: string, 1: array<string, string>}>
     */
    private static function parseHeader(string $header): array
    {
        $inIdentifier = 1;
        $inParameterName = 2;
        $inParameterValue = 4;
        $inQuotedString = 8;
        $inQuotedPair = 16;

        $cursor = $inIdentifier;
        $value = 0;
        $param = -1;
        $values = [];

        for ($offset = 0; isset($header[$offset]) && $offset < 1024; $offset++) {
            if (!isset(Charset::RFC7230_FIELD_VALUE[$header[$offset]])) {
                continue;
            }

            if ($header[$offset] === ',' && !($cursor & $inQuotedString)) {
                $cursor = $inIdentifier;
                $value++;
                $param = -1;
                continue;
            }
            if ($header[$offset] === ';' && !($cursor & $inQuotedString)) {
                $cursor = $inParameterName;
                $param++;
                continue;
            }
            if ($header[$offset] === '=' && ($cursor & $inParameterName)) {
                $cursor = $inParameterValue;
                continue;
            }
            if ($header[$offset] === '"' && ($cursor & $inParameterValue) && !($cursor & $inQuotedPair)) {
                $cursor ^= $inQuotedString;
                continue;
            }
            if ($header[$offset] === '\\' && ($cursor & $inQuotedString) && !($cursor & $inQuotedPair)) {
                $cursor |= $inQuotedPair;
                continue;
            }

            if ($cursor & $inIdentifier) {
                $values[$value][0] ??= '';
                $values[$value][0] .= $header[$offset];
                continue;
            }
            if ($cursor & $inParameterName) {
                $values[$value][1][$param][0] ??= '';
                $values[$value][1][$param][0] .= $header[$offset];
                continue;
            }
            if ($cursor & $inParameterValue) {
                $values[$value][1][$param][1] ??= '';
                $values[$value][1][$param][1] .= $header[$offset];
                $cursor &= ~$inQuotedPair;
                continue;
            }
        }

        $result = [];
        foreach ($values as $index => $value) {
            unset($values[$index]);

            $value[0] = trim($value[0] ?? '');
            if ($value[0] === '') {
                continue;
            }

            $params = [];
            foreach ($value[1] ?? [] as $param) {
                $param[0] = trim($param[0] ?? '');
                if ($param[0] === '') {
                    continue;
                }

                $params[$param[0]] = trim($param[1] ?? '');
            }

            $result[$index] = [$value[0], $params];
        }

        return $result;
    }
}
