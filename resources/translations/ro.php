<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI-ul cererii este formatat incorect și nu poate fi acceptat de server.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Resursa solicitată nu a fost găsită pentru acest URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Metoda solicitată nu este permisă pentru această resursă.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Tipul de media al cererii lipsește.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Tipul de media al cererii nu este suportat de această resursă.',
    ErrorMessage::INVALID_VARIABLE => 'Valoarea variabilei {{{ variable_name }}} în URI-ul cererii "{{ route_uri }}" este invalidă.',
    ErrorMessage::INVALID_QUERY => 'Parametrii de interogare ai cererii sunt invalizi.',
    ErrorMessage::MISSING_HEADER => 'Header-ul cererii "{{ header_name }}" lipsește.',
    ErrorMessage::INVALID_HEADER => 'Header-ul cererii "{{ header_name }}" este invalid.',
    ErrorMessage::MISSING_COOKIE => 'Cookie-ul "{{ cookie_name }}" lipsește.',
    ErrorMessage::INVALID_COOKIE => 'Cookie-ul "{{ cookie_name }}" este invalid.',
    ErrorMessage::INVALID_BODY => 'Corpul cererii este invalid.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Ceva a mers prost.',
];
