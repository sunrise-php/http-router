<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Den forespurte URI er feilformatert og kan ikke aksepteres av serveren.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Den forespurte ressursen ble ikke funnet for denne URI-en.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Den forespurte metoden er ikke tillatt for denne ressursen.',
    ErrorMessage::INVALID_VARIABLE => 'Verdien av variabelen {{{ variable_name }}} i den forespurte URI-en "{{ route_uri }}" er ugyldig.',
    ErrorMessage::INVALID_QUERY => 'Forespørselsparametrene er ugyldige.',
    ErrorMessage::MISSING_HEADER => 'Forespørselsheaderen "{{ header_name }}" mangler.',
    ErrorMessage::INVALID_HEADER => 'Forespørselsheaderen "{{ header_name }}" er ugyldig.',
    ErrorMessage::MISSING_COOKIE => 'Informasjonskapselen "{{ cookie_name }}" mangler.',
    ErrorMessage::INVALID_COOKIE => 'Informasjonskapselen "{{ cookie_name }}" er ugyldig.',
    ErrorMessage::INVALID_BODY => 'Forespørselens kropp er ugyldig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Noe gikk galt.',
];
