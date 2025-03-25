<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'L\'URI de la sol·licitud no és vàlid i no pot ser acceptat pel servidor.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'No s\'ha trobat el recurs sol·licitat per a aquest URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'El mètode sol·licitat no està permès per a aquest recurs.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Falta el tipus de mèdia de la sol·licitud.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'El tipus de mèdia de la sol·licitud no és compatible amb aquest recurs.',
    ErrorMessage::INVALID_VARIABLE => 'El valor de la variable {{{ variable_name }}} en l\'URI de la sol·licitud "{{ route_uri }}" no és vàlid.',
    ErrorMessage::INVALID_QUERY => 'Els paràmetres de consulta de la sol·licitud no són vàlids.',
    ErrorMessage::MISSING_HEADER => 'Falta la capçalera de la sol·licitud "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'La capçalera de la sol·licitud "{{ header_name }}" no és vàlida.',
    ErrorMessage::MISSING_COOKIE => 'Falta la cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'La cookie "{{ cookie_name }}" no és vàlida.',
    ErrorMessage::INVALID_BODY => 'El cos de la sol·licitud no és vàlid.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Alguna cosa va anar malament.',
];
