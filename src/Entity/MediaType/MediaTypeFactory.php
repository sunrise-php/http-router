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

namespace Sunrise\Http\Router\Entity\MediaType;

use InvalidArgumentException;

use function preg_match;
use function sprintf;

/**
 * @since 3.0.0
 */
final class MediaTypeFactory
{
    /**
     * @throws InvalidArgumentException See {@see fromString()}.
     */
    public static function create(MediaTypeInterface|string $mediaType): MediaTypeInterface
    {
        if ($mediaType instanceof MediaTypeInterface) {
            return $mediaType;
        }

        return self::fromString($mediaType);
    }

    public static function any(): MediaTypeInterface
    {
        return new MediaType('*', '*');
    }

    public static function json(): MediaTypeInterface
    {
        return new MediaType('application', 'json');
    }

    public static function xml(): MediaTypeInterface
    {
        return new MediaType('application', 'xml');
    }

    public static function html(): MediaTypeInterface
    {
        return new MediaType('text', 'html');
    }

    public static function text(): MediaTypeInterface
    {
        return new MediaType('text', 'plain');
    }

    public static function image(): MediaTypeInterface
    {
        return new MediaType('image', '*');
    }

    /**
     * @throws InvalidArgumentException If the given media type <b>doesn't look</b> like a media type.
     */
    public static function fromString(string $mediaType): MediaTypeInterface
    {
        if ($mediaType === '') {
            throw new InvalidArgumentException('The media type cannot be an empty string.');
        }

        if (!preg_match('|^([^/;]+)/([^/;]+)$|', $mediaType, $matches)) {
            throw new InvalidArgumentException(sprintf(
                'The string %s does not look like a media type (type/subtype).',
                $mediaType,
            ));
        }

        return new MediaType($matches[1], $matches[2]);
    }
}
