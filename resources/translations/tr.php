<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'İstek URI\'si hatalı biçimlendirilmiş ve sunucu tarafından kabul edilemez.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Bu URI için istenen kaynak bulunamadı.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'İstenen yöntem bu kaynak için izin verilmemiştir.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'İstek medya türü eksik.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Bu kaynak için istek medya türü desteklenmiyor.',
    ErrorMessage::INVALID_VARIABLE => 'İstek URI\'si \"{{ route_uri }}\" içindeki {{{ variable_name }}} değişkeninin değeri geçersiz.',
    ErrorMessage::INVALID_QUERY => 'İstek sorgu parametreleri geçersiz.',
    ErrorMessage::MISSING_HEADER => 'İstek başlığı \"{{ header_name }}\" eksik.',
    ErrorMessage::INVALID_HEADER => 'İstek başlığı \"{{ header_name }}\" geçersiz.',
    ErrorMessage::MISSING_COOKIE => 'Çerez \"{{ cookie_name }}\" eksik.',
    ErrorMessage::INVALID_COOKIE => 'Çerez \"{{ cookie_name }}\" geçersiz.',
    ErrorMessage::INVALID_BODY => 'İstek gövdesi geçersiz.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Bir şeyler ters gitti.',
];
