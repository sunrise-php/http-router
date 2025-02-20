<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => '요청 URI가 잘못 형성되어 서버에서 수락할 수 없습니다.',
    ErrorMessage::RESOURCE_NOT_FOUND => '요청한 리소스를 이 URI에서 찾을 수 없습니다.',
    ErrorMessage::METHOD_NOT_ALLOWED => '요청한 메서드는 이 리소스에 대해 허용되지 않습니다.',
    ErrorMessage::INVALID_VARIABLE => '요청 URI "{{ route_uri }}"의 {{{ variable_name }}} 변수 값이 잘못되었습니다.',
    ErrorMessage::INVALID_QUERY => '요청 매개변수가 잘못되었습니다.',
    ErrorMessage::MISSING_HEADER => '요청 헤더 "{{ header_name }}"가 없습니다.',
    ErrorMessage::INVALID_HEADER => '요청 헤더 "{{ header_name }}"가 잘못되었습니다.',
    ErrorMessage::MISSING_COOKIE => '쿠키 "{{ cookie_name }}"가 없습니다.',
    ErrorMessage::INVALID_COOKIE => '쿠키 "{{ cookie_name }}"가 잘못되었습니다.',
    ErrorMessage::INVALID_BODY => '요청 본문이 잘못되었습니다.',
    ErrorMessage::INTERNAL_SERVER_ERROR => '문제가 발생했습니다.',
];
