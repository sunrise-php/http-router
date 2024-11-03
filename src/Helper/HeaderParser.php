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

use function trim;

/**
 * @since 3.0.0
 */
final class HeaderParser
{
    private const IN_IDENTIFIER = 1;
    private const IN_PARAMETER_NAME = 2;
    private const IN_PARAMETER_VALUE = 4;
    private const IN_QUOTED_STRING = 8;
    private const IN_QUOTED_PAIR = 16;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.6
     */
    private const RFC7230_FIELD_VALUE = [
        "\x09" => 1, "\x20" => 1, "\x21" => 1, "\x22" => 1, "\x23" => 1, "\x24" => 1, "\x25" => 1, "\x26" => 1,
        "\x27" => 1, "\x28" => 1, "\x29" => 1, "\x2a" => 1, "\x2b" => 1, "\x2c" => 1, "\x2d" => 1, "\x2e" => 1,
        "\x2f" => 1, "\x30" => 1, "\x31" => 1, "\x32" => 1, "\x33" => 1, "\x34" => 1, "\x35" => 1, "\x36" => 1,
        "\x37" => 1, "\x38" => 1, "\x39" => 1, "\x3a" => 1, "\x3b" => 1, "\x3c" => 1, "\x3d" => 1, "\x3e" => 1,
        "\x3f" => 1, "\x40" => 1, "\x41" => 1, "\x42" => 1, "\x43" => 1, "\x44" => 1, "\x45" => 1, "\x46" => 1,
        "\x47" => 1, "\x48" => 1, "\x49" => 1, "\x4a" => 1, "\x4b" => 1, "\x4c" => 1, "\x4d" => 1, "\x4e" => 1,
        "\x4f" => 1, "\x50" => 1, "\x51" => 1, "\x52" => 1, "\x53" => 1, "\x54" => 1, "\x55" => 1, "\x56" => 1,
        "\x57" => 1, "\x58" => 1, "\x59" => 1, "\x5a" => 1, "\x5b" => 1, "\x5c" => 1, "\x5d" => 1, "\x5e" => 1,
        "\x5f" => 1, "\x60" => 1, "\x61" => 1, "\x62" => 1, "\x63" => 1, "\x64" => 1, "\x65" => 1, "\x66" => 1,
        "\x67" => 1, "\x68" => 1, "\x69" => 1, "\x6a" => 1, "\x6b" => 1, "\x6c" => 1, "\x6d" => 1, "\x6e" => 1,
        "\x6f" => 1, "\x70" => 1, "\x71" => 1, "\x72" => 1, "\x73" => 1, "\x74" => 1, "\x75" => 1, "\x76" => 1,
        "\x77" => 1, "\x78" => 1, "\x79" => 1, "\x7a" => 1, "\x7b" => 1, "\x7c" => 1, "\x7d" => 1, "\x7e" => 1,
        "\x80" => 1, "\x81" => 1, "\x82" => 1, "\x83" => 1, "\x84" => 1, "\x85" => 1, "\x86" => 1, "\x87" => 1,
        "\x88" => 1, "\x89" => 1, "\x8a" => 1, "\x8b" => 1, "\x8c" => 1, "\x8d" => 1, "\x8e" => 1, "\x8f" => 1,
        "\x90" => 1, "\x91" => 1, "\x92" => 1, "\x93" => 1, "\x94" => 1, "\x95" => 1, "\x96" => 1, "\x97" => 1,
        "\x98" => 1, "\x99" => 1, "\x9a" => 1, "\x9b" => 1, "\x9c" => 1, "\x9d" => 1, "\x9e" => 1, "\x9f" => 1,
        "\xa0" => 1, "\xa1" => 1, "\xa2" => 1, "\xa3" => 1, "\xa4" => 1, "\xa5" => 1, "\xa6" => 1, "\xa7" => 1,
        "\xa8" => 1, "\xa9" => 1, "\xaa" => 1, "\xab" => 1, "\xac" => 1, "\xad" => 1, "\xae" => 1, "\xaf" => 1,
        "\xb0" => 1, "\xb1" => 1, "\xb2" => 1, "\xb3" => 1, "\xb4" => 1, "\xb5" => 1, "\xb6" => 1, "\xb7" => 1,
        "\xb8" => 1, "\xb9" => 1, "\xba" => 1, "\xbb" => 1, "\xbc" => 1, "\xbd" => 1, "\xbe" => 1, "\xbf" => 1,
        "\xc0" => 1, "\xc1" => 1, "\xc2" => 1, "\xc3" => 1, "\xc4" => 1, "\xc5" => 1, "\xc6" => 1, "\xc7" => 1,
        "\xc8" => 1, "\xc9" => 1, "\xca" => 1, "\xcb" => 1, "\xcc" => 1, "\xcd" => 1, "\xce" => 1, "\xcf" => 1,
        "\xd0" => 1, "\xd1" => 1, "\xd2" => 1, "\xd3" => 1, "\xd4" => 1, "\xd5" => 1, "\xd6" => 1, "\xd7" => 1,
        "\xd8" => 1, "\xd9" => 1, "\xda" => 1, "\xdb" => 1, "\xdc" => 1, "\xdd" => 1, "\xde" => 1, "\xdf" => 1,
        "\xe0" => 1, "\xe1" => 1, "\xe2" => 1, "\xe3" => 1, "\xe4" => 1, "\xe5" => 1, "\xe6" => 1, "\xe7" => 1,
        "\xe8" => 1, "\xe9" => 1, "\xea" => 1, "\xeb" => 1, "\xec" => 1, "\xed" => 1, "\xee" => 1, "\xef" => 1,
        "\xf0" => 1, "\xf1" => 1, "\xf2" => 1, "\xf3" => 1, "\xf4" => 1, "\xf5" => 1, "\xf6" => 1, "\xf7" => 1,
        "\xf8" => 1, "\xf9" => 1, "\xfa" => 1, "\xfb" => 1, "\xfc" => 1, "\xfd" => 1, "\xfe" => 1, "\xff" => 1,
    ];

    /**
     * @return array<int<0, max>, array{0: string, 1: array<string, string>}>
     */
    public static function parseHeader(string $header): array
    {
        if ($header === '') {
            return [];
        }

        $cursor = self::IN_IDENTIFIER;
        $value = 0;
        $param = -1;

        /** @var array<int<0, max>, array{0?: string, 1?: array<int<0, max>, array{0?: string, 1?: string}>}> $values */
        $values = [];

        for ($offset = 0; isset($header[$offset]) && $offset < 1024; $offset++) {
            if (!isset(self::RFC7230_FIELD_VALUE[$header[$offset]])) {
                continue;
            }

            if ($header[$offset] === ',' && !($cursor & self::IN_QUOTED_STRING)) {
                $cursor = self::IN_IDENTIFIER;
                $value++;
                $param = -1;
                continue;
            }
            if ($header[$offset] === ';' && !($cursor & self::IN_QUOTED_STRING)) {
                $cursor = self::IN_PARAMETER_NAME;
                $param++;
                continue;
            }
            if ($header[$offset] === '=' && ($cursor & self::IN_PARAMETER_NAME)) {
                $cursor = self::IN_PARAMETER_VALUE;
                continue;
            }
            if ($header[$offset] === '"' && ($cursor & self::IN_PARAMETER_VALUE) && !($cursor & self::IN_QUOTED_PAIR)) {
                $cursor ^= self::IN_QUOTED_STRING;
                continue;
            }
            if ($header[$offset] === '\\' && ($cursor & self::IN_QUOTED_STRING) && !($cursor & self::IN_QUOTED_PAIR)) {
                $cursor |= self::IN_QUOTED_PAIR;
                continue;
            }

            if ($cursor & self::IN_IDENTIFIER) {
                $values[$value][0] ??= '';
                $values[$value][0] .= $header[$offset];
                continue;
            }
            if ($cursor & self::IN_PARAMETER_NAME) {
                $values[$value][1][$param][0] ??= '';
                $values[$value][1][$param][0] .= $header[$offset];
                continue;
            }
            if ($cursor & self::IN_PARAMETER_VALUE) {
                $values[$value][1][$param][1] ??= '';
                $values[$value][1][$param][1] .= $header[$offset];
                $cursor &= ~self::IN_QUOTED_PAIR;
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
