<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Požadované URI je nesprávne a server ho nemôže akceptovať.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Požadovaný zdroj pre toto URI nebol nájdený.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Požadovaná metóda nie je pre tento zdroj povolená.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Chýba typ médií v požiadavke.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Typ médií požiadavky nie je týmto zdrojom podporovaný.',
    ErrorMessage::INVALID_VARIABLE => 'Hodnota premennej {{{ variable_name }}} v požadovanom URI "{{ route_uri }}" je neplatná.',
    ErrorMessage::INVALID_QUERY => 'Parametre dotazu požiadavky sú neplatné.',
    ErrorMessage::MISSING_HEADER => 'V hlavičke požiadavky chýba "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Hlavička požiadavky "{{ header_name }}" je neplatná.',
    ErrorMessage::MISSING_COOKIE => 'Chýba cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" je neplatná.',
    ErrorMessage::INVALID_BODY => 'Obsah tela požiadavky je neplatný.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Niečo sa pokazilo.',
];
