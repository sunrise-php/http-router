<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'De aanvraag-URI is ongeldig en kan door de server niet worden geaccepteerd.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'De opgevraagde resource is niet gevonden voor deze URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'De aangevraagde methode is niet toegestaan voor deze resource.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Het mediatype van de aanvraag ontbreekt.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Het mediatype van de aanvraag wordt niet ondersteund voor deze resource.',
    ErrorMessage::INVALID_VARIABLE => 'De waarde van de variabele {{{ variable_name }}} in de aanvraag-URI \"{{ route_uri }}\" is ongeldig.',
    ErrorMessage::INVALID_QUERY => 'De queryparameters van de aanvraag zijn ongeldig.',
    ErrorMessage::MISSING_HEADER => 'De aanvraagheader \"{{ header_name }}\" ontbreekt.',
    ErrorMessage::INVALID_HEADER => 'De aanvraagheader \"{{ header_name }}\" is ongeldig.',
    ErrorMessage::MISSING_COOKIE => 'De cookie \"{{ cookie_name }}\" ontbreekt.',
    ErrorMessage::INVALID_COOKIE => 'De cookie \"{{ cookie_name }}\" is ongeldig.',
    ErrorMessage::INVALID_BODY => 'De aanvraagbody is ongeldig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Er is iets misgegaan.',
];
