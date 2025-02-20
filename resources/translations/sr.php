<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI zahteva je pogrešno formatirana i server je ne može prihvatiti.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Zatraženi resurs nije pronađen za ovaj URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Zatražena metoda nije dozvoljena za ovaj resurs.',
    ErrorMessage::INVALID_VARIABLE => 'Vrednost promenljive {{{ variable_name }}} u URI zahteva "{{ route_uri }}" je nevalidna.',
    ErrorMessage::INVALID_QUERY => 'Parametri zahteva su nevalidni.',
    ErrorMessage::MISSING_HEADER => 'Nedostaje zaglavlje zahteva "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Zaglavlje zahteva "{{ header_name }}" je nevalidno.',
    ErrorMessage::MISSING_COOKIE => 'Kolačić "{{ cookie_name }}" nedostaje.',
    ErrorMessage::INVALID_COOKIE => 'Kolačić "{{ cookie_name }}" je nevalidan.',
    ErrorMessage::INVALID_BODY => 'Telo zahteva je nevalidno.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Došlo je do greške.',
];
