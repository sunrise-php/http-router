<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI yêu cầu không đúng định dạng và không thể được chấp nhận bởi máy chủ.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Tài nguyên được yêu cầu không tìm thấy cho URI này.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Phương thức được yêu cầu không được phép cho tài nguyên này.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Loại phương tiện yêu cầu bị thiếu.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Loại phương tiện yêu cầu không được hỗ trợ bởi tài nguyên này.',
    ErrorMessage::INVALID_VARIABLE => 'Giá trị của biến {{{ variable_name }}} trong URI yêu cầu "{{ route_uri }}" không hợp lệ.',
    ErrorMessage::INVALID_QUERY => 'Các tham số truy vấn yêu cầu không hợp lệ.',
    ErrorMessage::MISSING_HEADER => 'Thẻ tiêu đề yêu cầu "{{ header_name }}" bị thiếu.',
    ErrorMessage::INVALID_HEADER => 'Thẻ tiêu đề yêu cầu "{{ header_name }}" không hợp lệ.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" bị thiếu.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" không hợp lệ.',
    ErrorMessage::INVALID_BODY => 'Nội dung yêu cầu không hợp lệ.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Đã có lỗi xảy ra.',
];
