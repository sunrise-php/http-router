<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'درخواست کا URI خراب ہے اور سرور اسے قبول نہیں کر سکتا۔',
    ErrorMessage::RESOURCE_NOT_FOUND => 'اس URI کے لئے مطلوبہ وسائل نہیں مل سکا۔',
    ErrorMessage::METHOD_NOT_ALLOWED => 'اس وسائل کے لئے درخواست کردہ طریقہ کار کی اجازت نہیں ہے۔',
    ErrorMessage::MISSING_MEDIA_TYPE => 'درخواست کی میڈیا قسم غائب ہے۔',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'اس وسائل کے لئے درخواست کی میڈیا قسم کو معاونت حاصل نہیں ہے۔',
    ErrorMessage::INVALID_VARIABLE => 'درخواست URI "{{ route_uri }}" میں متغیر {{{ variable_name }}} کی قیمت غلط ہے۔',
    ErrorMessage::INVALID_QUERY => 'درخواست کی سوالی پیرامیٹرز غلط ہیں۔',
    ErrorMessage::MISSING_HEADER => 'درخواست کا ہیڈر "{{ header_name }}" غائب ہے۔',
    ErrorMessage::INVALID_HEADER => 'درخواست کا ہیڈر "{{ header_name }}" غلط ہے۔',
    ErrorMessage::MISSING_COOKIE => 'کوکی "{{ cookie_name }}" غائب ہے۔',
    ErrorMessage::INVALID_COOKIE => 'کوکی "{{ cookie_name }}" غلط ہے۔',
    ErrorMessage::INVALID_BODY => 'درخواست کا باڈی غلط ہے۔',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'کچھ غلط ہو گیا۔',
];
