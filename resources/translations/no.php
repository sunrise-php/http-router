<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Forespørselens URI er feilformatert og kan ikke godtas av serveren.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Den forespurte ressursen ble ikke funnet for denne URI-en.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Den forespurte metoden er ikke tillatt for denne ressursen.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Forespørselens medietype mangler.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Forespørselens medietype støttes ikke for denne ressursen.',
    ErrorMessage::INVALID_VARIABLE => 'Verdien til variabelen {{{ variable_name }}} i forespørselens URI \"{{ route_uri }}\" er ugyldig.',
    ErrorMessage::INVALID_QUERY => 'Forespørselens spørringsparametere er ugyldige.',
    ErrorMessage::MISSING_HEADER => 'Forespørselens header \"{{ header_name }}\" mangler.',
    ErrorMessage::INVALID_HEADER => 'Forespørselens header \"{{ header_name }}\" er ugyldig.',
    ErrorMessage::MISSING_COOKIE => 'Cookie \"{{ cookie_name }}\" mangler.',
    ErrorMessage::INVALID_COOKIE => 'Cookie \"{{ cookie_name }}\" er ugyldig.',
    ErrorMessage::INVALID_BODY => 'Forespørselens kropp er ugyldig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Noe gikk galt.',
];
