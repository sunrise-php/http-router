<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'De aanvraag-URI is onjuist opgemaakt en kan niet door de server worden geaccepteerd.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'De opgevraagde bron is niet gevonden voor deze URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'De gevraagde methode is niet toegestaan voor deze bron.',
    ErrorMessage::INVALID_VARIABLE => 'De waarde van de variabele {{{ variable_name }}} in de aanvraag-URI "{{ route_uri }}" is ongeldig.',
    ErrorMessage::INVALID_QUERY => 'De aanvraagparameters zijn ongeldig.',
    ErrorMessage::MISSING_HEADER => 'De aanvraagheader "{{ header_name }}" ontbreekt.',
    ErrorMessage::INVALID_HEADER => 'De aanvraagheader "{{ header_name }}" is ongeldig.',
    ErrorMessage::MISSING_COOKIE => 'De cookie "{{ cookie_name }}" ontbreekt.',
    ErrorMessage::INVALID_COOKIE => 'De cookie "{{ cookie_name }}" is ongeldig.',
    ErrorMessage::INVALID_BODY => 'Het aanvraaglichaam is ongeldig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Er is iets mis gegaan.',
];
