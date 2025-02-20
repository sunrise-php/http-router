<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI požiadavky je nesprávne naformátovaný a server ho nemôže prijať.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Požadovaný zdroj nebol nájdený pre tento URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Požadovaná metóda nie je povolená pre tento zdroj.',
    ErrorMessage::INVALID_VARIABLE => 'Hodnota premennej {{{ variable_name }}} v URI požiadavky "{{ route_uri }}" je neplatná.',
    ErrorMessage::INVALID_QUERY => 'Parametre požiadavky sú neplatné.',
    ErrorMessage::MISSING_HEADER => 'Hlavička požiadavky "{{ header_name }}" chýba.',
    ErrorMessage::INVALID_HEADER => 'Hlavička požiadavky "{{ header_name }}" je neplatná.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" chýba.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" je neplatné.',
    ErrorMessage::INVALID_BODY => 'Telo požiadavky je neplatné.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Niečo sa pokazilo.',
];
