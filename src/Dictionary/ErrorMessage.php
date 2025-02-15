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
final class ErrorMessage
{
    // phpcs:disable Generic.Files.LineLength.TooLong
    public const MALFORMED_URI = 'The request URI is malformed and cannot be accepted by the server.';
    public const RESOURCE_NOT_FOUND = 'The requested resource was not found for this URI.';
    public const METHOD_NOT_ALLOWED = 'The requested method is not allowed for this resource.';
    public const INVALID_VARIABLE = 'The value of the variable {{{ variable_name }}} in the request URI "{{ route_uri }}" is invalid.';
    public const INVALID_QUERY = 'The request parameters are invalid.';
    public const MISSING_HEADER = 'The request header "{{ header_name }}" is missing.';
    public const INVALID_HEADER = 'The request header "{{ header_name }}" is invalid.';
    public const MISSING_COOKIE = 'The cookie "{{ cookie_name }}" is missing.';
    public const INVALID_COOKIE = 'The cookie "{{ cookie_name }}" is invalid.';
    public const INVALID_BODY = 'The request body is invalid.';
    public const INTERNAL_SERVER_ERROR = 'Something went wrong.';
    // phpcs:enable Generic.Files.LineLength.TooLong
}
