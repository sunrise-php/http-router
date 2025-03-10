<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'A kérés URI-je hibásan van formázva, és a szerver nem fogadja el.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'A kért erőforrás nem található ehhez az URI-hoz.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'A kért metódus nem engedélyezett ehhez az erőforráshoz.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'A kérés média típusa hiányzik.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'A kérés média típusa nem támogatott ehhez az erőforráshoz.',
    ErrorMessage::INVALID_VARIABLE => 'A {{{ variable_name }}} változó értéke a kérés URI-jában \"{{ route_uri }}\" érvénytelen.',
    ErrorMessage::INVALID_QUERY => 'A kérés lekérdezési paraméterei érvénytelenek.',
    ErrorMessage::MISSING_HEADER => 'A kérés fejléc \"{{ header_name }}\" hiányzik.',
    ErrorMessage::INVALID_HEADER => 'A kérés fejléc \"{{ header_name }}\" érvénytelen.',
    ErrorMessage::MISSING_COOKIE => 'A süti \"{{ cookie_name }}\" hiányzik.',
    ErrorMessage::INVALID_COOKIE => 'A süti \"{{ cookie_name }}\" érvénytelen.',
    ErrorMessage::INVALID_BODY => 'A kérés törzse érvénytelen.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Valami rosszul sült el.',
];
