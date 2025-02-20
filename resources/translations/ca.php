<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'L\'URI de la sol·licitud és malformat i no pot ser acceptat pel servidor.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'El recurs sol·licitat no s\'ha trobat per aquest URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'El mètode sol·licitat no està permès per a aquest recurs.',
    ErrorMessage::INVALID_VARIABLE => 'El valor de la variable {{{ variable_name }}} a l\'URI de la sol·licitud "{{ route_uri }}" és invàlid.',
    ErrorMessage::INVALID_QUERY => 'Els paràmetres de la sol·licitud són invàlids.',
    ErrorMessage::MISSING_HEADER => 'Falta l\'encapçalament de la sol·licitud "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'L\'encapçalament de la sol·licitud "{{ header_name }}" és invàlid.',
    ErrorMessage::MISSING_COOKIE => 'Falta la galeta "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'La galeta "{{ cookie_name }}" és invàlida.',
    ErrorMessage::INVALID_BODY => 'El cos de la sol·licitud és invàlid.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'S\'ha produït un error.',
];
