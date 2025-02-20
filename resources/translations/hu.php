<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'A kérés URI-ja hibásan van formázva, és a szerver nem tudja elfogadni.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'A kért erőforrás nem található ezen az URI-n.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'A kért módszer nem engedélyezett ehhez az erőforráshoz.',
    ErrorMessage::INVALID_VARIABLE => 'A {{{ variable_name }}} változó értéke a kérés URI-jában "{{ route_uri }}" érvénytelen.',
    ErrorMessage::INVALID_QUERY => 'A kérés paraméterei érvénytelenek.',
    ErrorMessage::MISSING_HEADER => 'A kérés fejlécében a "{{ header_name }}" hiányzik.',
    ErrorMessage::INVALID_HEADER => 'A kérés fejlécében a "{{ header_name }}" érvénytelen.',
    ErrorMessage::MISSING_COOKIE => 'A cookie "{{ cookie_name }}" hiányzik.',
    ErrorMessage::INVALID_COOKIE => 'A cookie "{{ cookie_name }}" érvénytelen.',
    ErrorMessage::INVALID_BODY => 'A kérés törzse érvénytelen.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Hiba történt.',
];
