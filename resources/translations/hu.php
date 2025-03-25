<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'A kérés URI hibás formátumú, és a szerver nem tudja elfogadni.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'A kért erőforrás nem található ezen az URI-n.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'A kért metódus nem megengedett ehhez az erőforráshoz.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'A kérés média típusa hiányzik.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'A kérés média típusát ez az erőforrás nem támogatja.',
    ErrorMessage::INVALID_VARIABLE => 'A {{{ variable_name }}} változó értéke a "{{ route_uri }}" kérés URI-ban érvénytelen.',
    ErrorMessage::INVALID_QUERY => 'A kérés lekérdezési paraméterei érvénytelenek.',
    ErrorMessage::MISSING_HEADER => 'A "{{ header_name }}" kérési fejléc hiányzik.',
    ErrorMessage::INVALID_HEADER => 'A "{{ header_name }}" kérési fejléc érvénytelen.',
    ErrorMessage::MISSING_COOKIE => 'A "{{ cookie_name }}" süti hiányzik.',
    ErrorMessage::INVALID_COOKIE => 'A "{{ cookie_name }}" süti érvénytelen.',
    ErrorMessage::INVALID_BODY => 'A kérés törzse érvénytelen.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Valami hiba történt.',
];
