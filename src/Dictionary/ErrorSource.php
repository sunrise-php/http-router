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
 * ErrorSource
 *
 * @since 3.0.0
 */
final class ErrorSource
{
    public const CLIENT_CREDENTIALS = 'client.credentials';
    public const CLIENT_PERMISSIONS = 'client.permissions';
    public const CLIENT_REQUEST = 'client.request';
    public const CLIENT_REQUEST_METHOD = 'client.request.method';
    public const CLIENT_REQUEST_URI = 'client.request.uri';
    public const CLIENT_REQUEST_PATH = 'client.request.path';
    public const CLIENT_REQUEST_QUERY = 'client.request.query';
    public const CLIENT_REQUEST_COOKIE = 'client.request.cookie';
    public const CLIENT_REQUEST_HEADER = 'client.request.header';
    public const CLIENT_REQUEST_BODY = 'client.request.body';
    public const DEPENDENCY = 'dependency';
    public const SERVER = 'server';
}
