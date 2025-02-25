<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => '요청 URI의 형식이 잘못되어 서버에서 수락할 수 없습니다.',
    ErrorMessage::RESOURCE_NOT_FOUND => '이 URI에 대해 요청된 리소스를 찾을 수 없습니다.',
    ErrorMessage::METHOD_NOT_ALLOWED => '이 리소스에 대해 요청된 메서드는 허용되지 않습니다.',
    ErrorMessage::MISSING_MEDIA_TYPE => '요청 미디어 타입이 누락되었습니다.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => '이 리소스는 요청 미디어 타입을 지원하지 않습니다.',
    ErrorMessage::INVALID_VARIABLE => '요청 URI \"{{ route_uri }}\"에 있는 변수 {{{ variable_name }}}의 값이 유효하지 않습니다.',
    ErrorMessage::INVALID_QUERY => '요청 쿼리 매개변수가 유효하지 않습니다.',
    ErrorMessage::MISSING_HEADER => '요청 헤더 \"{{ header_name }}\"가 누락되었습니다.',
    ErrorMessage::INVALID_HEADER => '요청 헤더 \"{{ header_name }}\"가 유효하지 않습니다.',
    ErrorMessage::MISSING_COOKIE => '쿠키 \"{{ cookie_name }}\"가 누락되었습니다.',
    ErrorMessage::INVALID_COOKIE => '쿠키 \"{{ cookie_name }}\"가 유효하지 않습니다.',
    ErrorMessage::INVALID_BODY => '요청 본문이 유효하지 않습니다.',
    ErrorMessage::INTERNAL_SERVER_ERROR => '문제가 발생했습니다.',
];
