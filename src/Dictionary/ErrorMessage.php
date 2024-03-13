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
    public const INVALID_URI = 'The provided request URI is invalid and cannot be accepted by the server.';
    public const RESOURCE_NOT_FOUND = 'The requested resource could not be found for this URI.';
    public const METHOD_NOT_ALLOWED = 'The requested method is not allowed for this resource; Please check the Allow response header for allowed methods.';
    public const MISSING_MEDIA_TYPE = 'The Content-Type request header must be provided and cannot be empty; Please check the Accept response header for supported media types.';
    public const UNSUPPORTED_MEDIA_TYPE = 'The media type {{ media_type }} is not supported; Check the Accept response header for supported media types.';
    public const INVALID_VARIABLE = 'The provided value of the variable {{{ variable_name }}} in the URI pattern {{ route_uri }} is invalid.';
    public const INVALID_QUERY = 'The provided request query parameters are invalid.';
    public const MISSING_HEADER = 'The request header {{ header_name }} must be provided.';
    public const INVALID_HEADER = 'The provided request header {{ header_name }} is invalid.';
    public const MISSING_COOKIE = 'The cookie {{ cookie_name }} is missing.';
    public const INVALID_COOKIE = 'The cookie {{ cookie_name }} is invalid.';
    public const INVALID_BODY = 'The provided request body is invalid.';
    public const EMPTY_JSON_PAYLOAD = 'The JSON payload cannot be empty.';
    public const INVALID_JSON_PAYLOAD = 'The JSON payload is invalid.';
    public const INVALID_JSON_PAYLOAD_FORM = 'The JSON payload must be in the form of an array or an object.';
}
