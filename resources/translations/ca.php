<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'La URI de la sol·licitud està malformada i no pot ser acceptada pel servidor.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'El recurs sol·licitat no s\'ha trobat per aquesta URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'El mètode sol·licitat no està permès per aquest recurs.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'El tipus de mitjà de la sol·licitud falta.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'El tipus de mitjà de la sol·licitud no és compatible amb aquest recurs.',
    ErrorMessage::INVALID_VARIABLE => 'El valor de la variable {{{ variable_name }}} a la URI de la sol·licitud \"{{ route_uri }}\" no és vàlid.',
    ErrorMessage::INVALID_QUERY => 'Els paràmetres de consulta de la sol·licitud no són vàlids.',
    ErrorMessage::MISSING_HEADER => 'El capçalera de la sol·licitud \"{{ header_name }}\" falta.',
    ErrorMessage::INVALID_HEADER => 'El capçalera de la sol·licitud \"{{ header_name }}\" no és vàlid.',
    ErrorMessage::MISSING_COOKIE => 'La cookie \"{{ cookie_name }}\" falta.',
    ErrorMessage::INVALID_COOKIE => 'La cookie \"{{ cookie_name }}\" no és vàlida.',
    ErrorMessage::INVALID_BODY => 'El cos de la sol·licitud no és vàlid.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Alguna cosa ha anat malament.',
];
