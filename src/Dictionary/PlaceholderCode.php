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

namespace Sunrise\Http\Router\Dictionary;

/**
 * @since 3.0.0
 */
final class PlaceholderCode
{
    public const COOKIE_NAME = '{{ cookie_name }}';
    public const HEADER_NAME = '{{ header_name }}';
    public const MEDIA_TYPE = '{{ media_type }}';
    public const REQUEST_METHOD = '{{ request_method }}';
    public const REQUEST_URI = '{{ request_uri }}';
    public const ROUTE_URI = '{{ route_uri }}';
    public const VARIABLE_NAME = '{{ variable_name }}';
}
