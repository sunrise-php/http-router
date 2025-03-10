<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI zahtjeva je neispravno oblikovan i server ga ne može prihvatiti.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Traženi resurs nije pronađen za ovaj URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Zahtjevana metoda nije dopuštena za ovaj resurs.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Nedostaje medijski tip zahtjeva.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Medijski tip zahtjeva nije podržan za ovaj resurs.',
    ErrorMessage::INVALID_VARIABLE => 'Vrijednost varijable {{{ variable_name }}} u URI-ju zahtjeva \"{{ route_uri }}\" nije valjana.',
    ErrorMessage::INVALID_QUERY => 'Upitni parametri zahtjeva nisu valjani.',
    ErrorMessage::MISSING_HEADER => 'Zaglavlje zahtjeva \"{{ header_name }}\" nedostaje.',
    ErrorMessage::INVALID_HEADER => 'Zaglavlje zahtjeva \"{{ header_name }}\" nije valjano.',
    ErrorMessage::MISSING_COOKIE => 'Kolačić \"{{ cookie_name }}\" nedostaje.',
    ErrorMessage::INVALID_COOKIE => 'Kolačić \"{{ cookie_name }}\" nije valjan.',
    ErrorMessage::INVALID_BODY => 'Tijelo zahtjeva nije valjano.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Nešto je pošlo po zlu.',
];
