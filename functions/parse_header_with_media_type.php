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

use Generator;
use Sunrise\Http\Router\Dictionary\Charset;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Exception\InvalidArgumentException;

use function sprintf;

/**
 * Parses the given header that contains media types
 *
 * @param string $header
 *
 * @return Generator<int, MediaType>
 *
 * @throws InvalidArgumentException If one of the media types is invalid.
 *
 * @since 3.0.0
 */
function parse_header_with_media_type(string $header): Generator
{
    $matches = parse_header($header);
    if ($matches === []) {
        return;
    }

    foreach ($matches as $index => $match) {
        $offset = -1;

        $inType = true;
        $inSubtype = false;

        $type = null;
        $subtype = null;

        while (true) {
            $offset++;

            $char = $match[0][$offset] ?? null;
            if ($char === null) {
                break;
            }

            if ($inType && $char === '/') {
                $inType = false;
                $inSubtype = true;
                continue;
            }

            if ($inType && isset(Charset::RFC7230_TOKEN[$char])) {
                $type .= $char;
                continue;
            }
            if ($inSubtype && isset(Charset::RFC7230_TOKEN[$char])) {
                $subtype .= $char;
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'Unexpected character at position %d inside media type with index %d.',
                $offset,
                $index,
            ));
        }

        if ($subtype === null) {
            throw new InvalidArgumentException(sprintf(
                'Missing subtype for media type with index %d.',
                $index,
            ));
        }

        if ($type === '*' && $subtype !== '*') {
            throw new InvalidArgumentException(sprintf(
                'Subtype "*" expected for media type with index %d.',
                $index,
            ));
        }

        $parameters = [];
        if (isset($match[1])) {
            foreach ($match[1] as $param) {
                $parameters[$param[0]] = $param[1] ?? null;
            }
        }

        yield new MediaType($type, $subtype, $parameters);
    }
}
