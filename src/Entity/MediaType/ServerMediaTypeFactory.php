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
final class ServerMediaTypeFactory
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

    /**
     * @throws InvalidArgumentException If the given media type <b>doesn't look</b> like a media type.
     */
    public static function fromString(string $mediaType): MediaTypeInterface
    {
        if (!preg_match('|^([^/;]+)/([^/;]+)$|', $mediaType, $matches)) {
            throw new InvalidArgumentException(sprintf(
                'The string %s does not look like a media type.',
                $mediaType,
            ));
        }

        return new ServerMediaType($matches[1], $matches[2]);
    }

    public static function json(): MediaTypeInterface
    {
        return new ServerMediaType('application', 'json');
    }

    public static function xml(): MediaTypeInterface
    {
        return new ServerMediaType('application', 'xml');
    }

    public static function html(): MediaTypeInterface
    {
        return new ServerMediaType('text', 'html');
    }

    public static function text(): MediaTypeInterface
    {
        return new ServerMediaType('text', 'plain');
    }

    public static function image(): MediaTypeInterface
    {
        return new ServerMediaType('image', '*');
    }
}
