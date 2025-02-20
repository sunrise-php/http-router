<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'İstek URI\'sı hatalı biçimlendirilmiş ve sunucu tarafından kabul edilemez.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'İstenen kaynak bu URI için bulunamadı.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'İstenen yöntem bu kaynak için izinli değil.',
    ErrorMessage::INVALID_VARIABLE => 'İstek URI\'sındaki {{{ variable_name }}} değişkeninin değeri "{{ route_uri }}" geçersiz.',
    ErrorMessage::INVALID_QUERY => 'İstek parametreleri geçersiz.',
    ErrorMessage::MISSING_HEADER => 'İstek başlığı "{{ header_name }}" eksik.',
    ErrorMessage::INVALID_HEADER => 'İstek başlığı "{{ header_name }}" geçersiz.',
    ErrorMessage::MISSING_COOKIE => 'Çerez "{{ cookie_name }}" eksik.',
    ErrorMessage::INVALID_COOKIE => 'Çerez "{{ cookie_name }}" geçersiz.',
    ErrorMessage::INVALID_BODY => 'İstek gövdesi geçersiz.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Bir şeyler yanlış gitti.',
];
