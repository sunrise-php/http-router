<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI zahtjeva je neispravno oblikovana i server je ne može prihvatiti.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Zahtijevani resurs nije pronađen za ovaj URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Zatražena metoda nije dopuštena za ovaj resurs.',
    ErrorMessage::INVALID_VARIABLE => 'Vrijednost varijable {{{ variable_name }}} u URI zahtjeva "{{ route_uri }}" je neispravna.',
    ErrorMessage::INVALID_QUERY => 'Parametri zahtjeva su neispravni.',
    ErrorMessage::MISSING_HEADER => 'Nedostaje zaglavlje zahtjeva "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Zaglavlje zahtjeva "{{ header_name }}" je neispravno.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" nedostaje.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" je neispravno.',
    ErrorMessage::INVALID_BODY => 'Tijelo zahtjeva je neispravno.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Došlo je do pogreške.',
];
