<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Anmodningens URI er forkert formateret og kan ikke accepteres af serveren.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Den anmodede ressource blev ikke fundet for denne URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Den anmodede metode er ikke tilladt for denne ressource.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Anmodningens medietype mangler.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Anmodningens medietype understøttes ikke for denne ressource.',
    ErrorMessage::INVALID_VARIABLE => 'Værdien af variablen {{{ variable_name }}} i anmodningens URI \"{{ route_uri }}\" er ugyldig.',
    ErrorMessage::INVALID_QUERY => 'Forespørgselsparametrene for anmodningen er ugyldige.',
    ErrorMessage::MISSING_HEADER => 'Anmodningens header \"{{ header_name }}\" mangler.',
    ErrorMessage::INVALID_HEADER => 'Anmodningens header \"{{ header_name }}\" er ugyldig.',
    ErrorMessage::MISSING_COOKIE => 'Cookien \"{{ cookie_name }}\" mangler.',
    ErrorMessage::INVALID_COOKIE => 'Cookien \"{{ cookie_name }}\" er ugyldig.',
    ErrorMessage::INVALID_BODY => 'Anmodningens krop er ugyldig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Noget gik galt.',
];
