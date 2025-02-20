<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI požadavku je neplatný a nemůže být přijat serverem.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Požadovaný prostředek nebyl pro tento URI nalezen.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Požadovaná metoda není pro tento prostředek povolena.',
    ErrorMessage::INVALID_VARIABLE => 'Hodnota proměnné {{{ variable_name }}} v URI požadavku "{{ route_uri }}" je neplatná.',
    ErrorMessage::INVALID_QUERY => 'Parametry požadavku jsou neplatné.',
    ErrorMessage::MISSING_HEADER => 'V požadavku chybí hlavička "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Hlavička požadavku "{{ header_name }}" je neplatná.',
    ErrorMessage::MISSING_COOKIE => 'Chybí cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" je neplatná.',
    ErrorMessage::INVALID_BODY => 'Tělo požadavku je neplatné.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Došlo k chybě na serveru.',
];
