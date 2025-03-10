<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => '请求的 URI 格式错误，服务器无法接受。',
    ErrorMessage::RESOURCE_NOT_FOUND => '未找到该 URI 对应的请求资源。',
    ErrorMessage::METHOD_NOT_ALLOWED => '请求的方法不允许用于此资源。',
    ErrorMessage::MISSING_MEDIA_TYPE => '请求的媒体类型缺失。',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => '请求的媒体类型不支持此资源。',
    ErrorMessage::INVALID_VARIABLE => '请求 URI 中 \"{{ route_uri }}\" 的变量 {{{ variable_name }}} 的值无效。',
    ErrorMessage::INVALID_QUERY => '请求的查询参数无效。',
    ErrorMessage::MISSING_HEADER => '请求头 \"{{ header_name }}\" 缺失。',
    ErrorMessage::INVALID_HEADER => '请求头 \"{{ header_name }}\" 无效。',
    ErrorMessage::MISSING_COOKIE => 'Cookie \"{{ cookie_name }}\" 缺失。',
    ErrorMessage::INVALID_COOKIE => 'Cookie \"{{ cookie_name }}\" 无效。',
    ErrorMessage::INVALID_BODY => '请求体无效。',
    ErrorMessage::INTERNAL_SERVER_ERROR => '出了点问题。',
];
