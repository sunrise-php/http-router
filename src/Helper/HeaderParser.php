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
     * @return array<int<0, max>, array{0: string, 1: array<string, string>}>
     */
    public static function parseHeader(string $header): array
    {
        $cursor = self::IN_IDENTIFIER;
        $value = 0;
        $param = -1;
        $values = [];

        for ($offset = 0; isset($header[$offset]) && $offset < 1024; $offset++) {
            if (!isset(Charset::RFC7230_FIELD_VALUE[$header[$offset]])) {
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
