<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Anmodnings-URI\'en er ugyldig og kan ikke accepteres af serveren.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Den ønskede ressource blev ikke fundet for denne URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Den ønskede metode er ikke tilladt for denne ressource.',
    ErrorMessage::INVALID_VARIABLE => 'Værdien af variablen {{{ variable_name }}} i anmodnings-URI\'en "{{ route_uri }}" er ugyldig.',
    ErrorMessage::INVALID_QUERY => 'Anmodningsparametrene er ugyldige.',
    ErrorMessage::MISSING_HEADER => 'Anmodningshovedet "{{ header_name }}" mangler.',
    ErrorMessage::INVALID_HEADER => 'Anmodningshovedet "{{ header_name }}" er ugyldigt.',
    ErrorMessage::MISSING_COOKIE => 'Cookien "{{ cookie_name }}" mangler.',
    ErrorMessage::INVALID_COOKIE => 'Cookien "{{ cookie_name }}" er ugyldig.',
    ErrorMessage::INVALID_BODY => 'Anmodningsindholdet er ugyldigt.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Der opstod en fejl.',
];
