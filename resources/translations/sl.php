<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Zahteva URI je napačno oblikovana in jo strežnik ne more sprejeti.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Zahtevana sredstva niso bila najdena za to URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Zahtevana metoda ni dovoljena za ta vir.',
    ErrorMessage::INVALID_VARIABLE => 'Vrednost spremenljivke {{{ variable_name }}} v URI zahteve "{{ route_uri }}" ni veljavna.',
    ErrorMessage::INVALID_QUERY => 'Parametri zahteve niso veljavni.',
    ErrorMessage::MISSING_HEADER => 'Glava zahteve "{{ header_name }}" manjka.',
    ErrorMessage::INVALID_HEADER => 'Glava zahteve "{{ header_name }}" ni veljavna.',
    ErrorMessage::MISSING_COOKIE => 'Piškotek "{{ cookie_name }}" manjka.',
    ErrorMessage::INVALID_COOKIE => 'Piškotek "{{ cookie_name }}" ni veljaven.',
    ErrorMessage::INVALID_BODY => 'Telo zahteve ni veljavno.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Nekaj je šlo narobe.',
];
