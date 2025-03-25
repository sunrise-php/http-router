<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => '请求的URI格式不正确，服务器无法接受。',
    ErrorMessage::RESOURCE_NOT_FOUND => '在此URI上未找到请求的资源。',
    ErrorMessage::METHOD_NOT_ALLOWED => '请求的方法不允许用于此资源。',
    ErrorMessage::MISSING_MEDIA_TYPE => '请求的媒体类型缺失。',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => '请求的媒体类型不受此资源支持。',
    ErrorMessage::INVALID_VARIABLE => '请求URI "{{ route_uri }}" 中变量 {{{ variable_name }}} 的值无效。',
    ErrorMessage::INVALID_QUERY => '请求的查询参数无效。',
    ErrorMessage::MISSING_HEADER => '请求头 "{{ header_name }}" 缺失。',
    ErrorMessage::INVALID_HEADER => '请求头 "{{ header_name }}" 无效。',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" 缺失。',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" 无效。',
    ErrorMessage::INVALID_BODY => '请求体无效。',
    ErrorMessage::INTERNAL_SERVER_ERROR => '出了点问题。',
];
