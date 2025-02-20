<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI-ul cererii este formatat greșit și nu poate fi acceptat de server.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Resursa solicitată nu a fost găsită pentru acest URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Metoda solicitată nu este permisă pentru această resursă.',
    ErrorMessage::INVALID_VARIABLE => 'Valoarea variabilei {{{ variable_name }}} din URI-ul cererii "{{ route_uri }}" este invalidă.',
    ErrorMessage::INVALID_QUERY => 'Parametrii cererii sunt invalizi.',
    ErrorMessage::MISSING_HEADER => 'Capul cererii "{{ header_name }}" lipsește.',
    ErrorMessage::INVALID_HEADER => 'Capul cererii "{{ header_name }}" este invalid.',
    ErrorMessage::MISSING_COOKIE => 'Cookie-ul "{{ cookie_name }}" lipsește.',
    ErrorMessage::INVALID_COOKIE => 'Cookie-ul "{{ cookie_name }}" este invalid.',
    ErrorMessage::INVALID_BODY => 'Corpul cererii este invalid.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'A apărut o problemă.',
];
