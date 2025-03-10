<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI požiadavky je nesprávne formátovaný a server ho nemôže prijať.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Požadovaný zdroj nebol pre toto URI nájdený.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Požadovaná metóda nie je pre tento zdroj povolená.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Chýba mediálny typ požiadavky.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Mediálny typ požiadavky nie je pre tento zdroj podporovaný.',
    ErrorMessage::INVALID_VARIABLE => 'Hodnota premennej {{{ variable_name }}} v URI požiadavky \"{{ route_uri }}\" je neplatná.',
    ErrorMessage::INVALID_QUERY => 'Parametre dopytu požiadavky sú neplatné.',
    ErrorMessage::MISSING_HEADER => 'Chýba hlavička požiadavky \"{{ header_name }}\".',
    ErrorMessage::INVALID_HEADER => 'Hlavička požiadavky \"{{ header_name }}\" je neplatná.',
    ErrorMessage::MISSING_COOKIE => 'Chýba cookie \"{{ cookie_name }}\".',
    ErrorMessage::INVALID_COOKIE => 'Cookie \"{{ cookie_name }}\" je neplatná.',
    ErrorMessage::INVALID_BODY => 'Telo požiadavky je neplatné.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Niečo sa pokazilo.',
];
