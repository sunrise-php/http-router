<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'İstek URI\'si hatalı ve sunucu tarafından kabul edilemez.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'İstenen kaynak bu URI için bulunamadı.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'İstenen yöntem bu kaynak için izinli değil.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'İstek medya türü eksik.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'İstek medya türü bu kaynak tarafından desteklenmiyor.',
    ErrorMessage::INVALID_VARIABLE => 'İstek URI\'sindeki "{{ route_uri }}" değişken {{{ variable_name }}} değeri geçersiz.',
    ErrorMessage::INVALID_QUERY => 'İstek sorgu parametreleri geçersiz.',
    ErrorMessage::MISSING_HEADER => 'İstek üst bilgisi "{{ header_name }}" eksik.',
    ErrorMessage::INVALID_HEADER => 'İstek üst bilgisi "{{ header_name }}" geçersiz.',
    ErrorMessage::MISSING_COOKIE => 'Çerez "{{ cookie_name }}" eksik.',
    ErrorMessage::INVALID_COOKIE => 'Çerez "{{ cookie_name }}" geçersiz.',
    ErrorMessage::INVALID_BODY => 'İstek gövdesi geçersiz.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Bir şeyler yanlış gitti.',
];
