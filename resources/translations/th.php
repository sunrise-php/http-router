<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI ของคำขอมีข้อผิดพลาดและไม่สามารถยอมรับได้โดยเซิร์ฟเวอร์.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'ไม่พบทรัพยากรที่ร้องขอสำหรับ URI นี้.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'วิธีที่ร้องขอไม่ได้รับอนุญาตสำหรับทรัพยากรนี้.',
    ErrorMessage::INVALID_VARIABLE => 'ค่าของตัวแปร {{{ variable_name }}} ใน URI ของคำขอ "{{ route_uri }}" ไม่ถูกต้อง.',
    ErrorMessage::INVALID_QUERY => 'พารามิเตอร์คำขอไม่ถูกต้อง.',
    ErrorMessage::MISSING_HEADER => 'ส่วนหัวของคำขอ "{{ header_name }}" ขาดหายไป.',
    ErrorMessage::INVALID_HEADER => 'ส่วนหัวของคำขอ "{{ header_name }}" ไม่ถูกต้อง.',
    ErrorMessage::MISSING_COOKIE => 'คุกกี้ "{{ cookie_name }}" ขาดหายไป.',
    ErrorMessage::INVALID_COOKIE => 'คุกกี้ "{{ cookie_name }}" ไม่ถูกต้อง.',
    ErrorMessage::INVALID_BODY => 'เนื้อหาของคำขอไม่ถูกต้อง.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'เกิดข้อผิดพลาดบางประการ.',
];
