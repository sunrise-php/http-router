<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => '请求的 URI 格式错误，服务器无法接受。',
    ErrorMessage::RESOURCE_NOT_FOUND => '未找到该 URI 的请求资源。',
    ErrorMessage::METHOD_NOT_ALLOWED => '请求的方法不允许该资源。',
    ErrorMessage::INVALID_VARIABLE => '请求 URI 中 {{{ variable_name }}} 变量的值 "{{ route_uri }}" 无效。',
    ErrorMessage::INVALID_QUERY => '请求参数无效。',
    ErrorMessage::MISSING_HEADER => '请求头 "{{ header_name }}" 丢失。',
    ErrorMessage::INVALID_HEADER => '请求头 "{{ header_name }}" 无效。',
    ErrorMessage::MISSING_COOKIE => '缺少 cookie "{{ cookie_name }}"。',
    ErrorMessage::INVALID_COOKIE => 'cookie "{{ cookie_name }}" 无效。',
    ErrorMessage::INVALID_BODY => '请求体无效。',
    ErrorMessage::INTERNAL_SERVER_ERROR => '发生错误。',
];
