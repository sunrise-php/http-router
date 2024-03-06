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
    /**
     * @return array<int<0, max>, array{0: string, 1: array<string, string>}>
     */
    public static function parseHeader(string $header): array
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
