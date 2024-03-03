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
    public const METHOD_NOT_ALLOWED = 'The method {{ method }} is not allowed; please check the Allow header for allowed methods.';
    public const MEDIA_TYPE_NOT_SUPPORTED = 'The media type {{ media_type }} is not supported; please check the Accept header for supported media types.';
    public const JSON_PAYLOAD_EMPTY = 'JSON body cannot be empty.';
    public const JSON_PAYLOAD_INVALID = 'The JSON body is invalid and could not be decoded.';
    public const JSON_PAYLOAD_FORM_INVALID = 'The JSON body must be in the form of an array or an object.';
    public const BODY_INVALID = 'The request body is invalid.';
    public const COOKIE_INVALID = 'The cookie {{ cookie_name }} is invalid.';
    public const COOKIE_MISSED = 'The cookie {{ cookie_name }} must be provided.';
    public const HEADER_INVALID = 'The header {{ header_name }} is invalid.';
    public const HEADER_MISSED = 'The header {{ header_name }} must be provided.';
    public const QUERY_INVALID = 'The request query is invalid.';
    public const QUERY_PARAM_INVALID = 'The query parameter {{ param_name }} is invalid.';
    public const QUERY_PARAM_MISSED = 'The query parameter {{ param_name }} must be provided.';
    public const PATH_VARIABLE_INVALID = 'The variable {{{ variable_name }}} of the path {{ route_path }} is invalid.';
}
