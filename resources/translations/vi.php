<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI yêu cầu bị định dạng không đúng và không thể được máy chủ chấp nhận.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Không tìm thấy tài nguyên yêu cầu cho URI này.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Phương thức yêu cầu không được phép cho tài nguyên này.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Thiếu kiểu phương tiện của yêu cầu.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Kiểu phương tiện của yêu cầu không được hỗ trợ cho tài nguyên này.',
    ErrorMessage::INVALID_VARIABLE => 'Giá trị của biến {{{ variable_name }}} trong URI yêu cầu \"{{ route_uri }}\" không hợp lệ.',
    ErrorMessage::INVALID_QUERY => 'Tham số truy vấn của yêu cầu không hợp lệ.',
    ErrorMessage::MISSING_HEADER => 'Tiêu đề yêu cầu \"{{ header_name }}\" bị thiếu.',
    ErrorMessage::INVALID_HEADER => 'Tiêu đề yêu cầu \"{{ header_name }}\" không hợp lệ.',
    ErrorMessage::MISSING_COOKIE => 'Cookie \"{{ cookie_name }}\" bị thiếu.',
    ErrorMessage::INVALID_COOKIE => 'Cookie \"{{ cookie_name }}\" không hợp lệ.',
    ErrorMessage::INVALID_BODY => 'Nội dung yêu cầu không hợp lệ.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Đã xảy ra sự cố.',
];
