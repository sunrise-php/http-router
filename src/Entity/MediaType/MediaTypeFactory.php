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

/**
 * @since 3.0.0
 */
final class MediaTypeFactory
{
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
}
