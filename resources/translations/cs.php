<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Požadovaný URI je nesprávně formátován a nemůže být serverem přijat.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Požadovaný prostředek nebyl pro tento URI nalezen.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Požadovaná metoda není pro tento prostředek povolena.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Chybí typ média požadavku.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Typ média požadavku není pro tento prostředek podporován.',
    ErrorMessage::INVALID_VARIABLE => 'Hodnota proměnné {{{ variable_name }}} v URI požadavku "{{ route_uri }}" je neplatná.',
    ErrorMessage::INVALID_QUERY => 'Parametry dotazu požadavku jsou neplatné.',
    ErrorMessage::MISSING_HEADER => 'Chybí hlavička požadavku "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Hlavička požadavku "{{ header_name }}" je neplatná.',
    ErrorMessage::MISSING_COOKIE => 'Chybí cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" je neplatná.',
    ErrorMessage::INVALID_BODY => 'Tělo požadavku je neplatné.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Něco se pokazilo.',
];
