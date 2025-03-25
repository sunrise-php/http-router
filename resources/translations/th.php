<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI ที่ร้องขอผิดรูปแบบและไม่สามารถยอมรับได้โดยเซิร์ฟเวอร์',
    ErrorMessage::RESOURCE_NOT_FOUND => 'ไม่พบทรัพยากรที่ร้องขอสำหรับ URI นี้',
    ErrorMessage::METHOD_NOT_ALLOWED => 'ไม่อนุญาตให้ใช้วิธีการที่ร้องขอสำหรับทรัพยากรนี้',
    ErrorMessage::MISSING_MEDIA_TYPE => 'ไม่มีประเภทสื่อของคำขอ',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'ประเภทสื่อของคำขอไม่รองรับสำหรับทรัพยากรนี้',
    ErrorMessage::INVALID_VARIABLE => 'ค่าของตัวแปร {{{ variable_name }}} ใน URI ที่ร้องขอ "{{ route_uri }}" ไม่ถูกต้อง',
    ErrorMessage::INVALID_QUERY => 'พารามิเตอร์คำค้นหาของคำขอไม่ถูกต้อง',
    ErrorMessage::MISSING_HEADER => 'ไม่มีส่วนหัวของคำขอ "{{ header_name }}"',
    ErrorMessage::INVALID_HEADER => 'ส่วนหัวของคำขอ "{{ header_name }}" ไม่ถูกต้อง',
    ErrorMessage::MISSING_COOKIE => 'ไม่มีคุกกี้ "{{ cookie_name }}"',
    ErrorMessage::INVALID_COOKIE => 'คุกกี้ "{{ cookie_name }}" ไม่ถูกต้อง',
    ErrorMessage::INVALID_BODY => 'เนื้อหาของคำขอไม่ถูกต้อง',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'เกิดข้อผิดพลาดบางประการ',
];
