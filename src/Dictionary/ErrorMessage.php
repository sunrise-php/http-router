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
    public const RESOURCE_NOT_FOUND = 'The resource {{ resource }} was not found.';
    public const METHOD_NOT_ALLOWED = 'The method {{ method }} is not allowed; check the Allow response header for allowed methods.';
    public const MISSING_MEDIA_TYPE = 'The Content-Type header must be provided and cannot be empty; check the Accept response header for supported media types.';
    public const UNSUPPORTED_MEDIA_TYPE = 'The media type {{ media_type }} is not supported; check the Accept response header for supported media types.';
    public const INVALID_PATH_VARIABLE = 'The variable {{ variable_name }} of the route {{ route }} is invalid.';
    public const INVALID_QUERY = 'The query parameters are invalid.';
    public const MISSING_QUERY_PARAM = 'The query parameter {{ param_name }} must be provided.';
    public const INVALID_QUERY_PARAM = 'The query parameter {{ param_name }} is invalid.';
    public const MISSING_HEADER_FIELD = 'The header {{ header_name }} must be provided.';
    public const INVALID_HEADER_FIELD = 'The header {{ header_name }} is invalid.';
    public const MISSING_COOKIE_PARAM = 'The cookie {{ cookie_name }} must be provided.';
    public const INVALID_COOKIE_PARAM = 'The cookie {{ cookie_name }} is invalid.';
    public const INVALID_BODY = 'The request body is invalid.';
    public const EMPTY_JSON_PAYLOAD = 'The JSON payload cannot be empty.';
    public const INVALID_JSON_PAYLOAD = 'The JSON payload is invalid and could not be decoded.';
    public const INVALID_JSON_PAYLOAD_FORM = 'The JSON payload must be in the form of an array or an object.';
}
