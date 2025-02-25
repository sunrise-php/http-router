<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Begärans URI är felaktigt formaterad och kan inte accepteras av servern.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Den begärda resursen hittades inte för denna URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Den begärda metoden är inte tillåten för denna resurs.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Begärans mediatyp saknas.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Begärans mediatyp stöds inte för denna resurs.',
    ErrorMessage::INVALID_VARIABLE => 'Värdet på variabeln {{{ variable_name }}} i begärans URI \"{{ route_uri }}\" är ogiltigt.',
    ErrorMessage::INVALID_QUERY => 'Begärans frågeparametrar är ogiltiga.',
    ErrorMessage::MISSING_HEADER => 'Begärans header \"{{ header_name }}\" saknas.',
    ErrorMessage::INVALID_HEADER => 'Begärans header \"{{ header_name }}\" är ogiltig.',
    ErrorMessage::MISSING_COOKIE => 'Begärans cookie \"{{ cookie_name }}\" saknas.',
    ErrorMessage::INVALID_COOKIE => 'Begärans cookie \"{{ cookie_name }}\" är ogiltig.',
    ErrorMessage::INVALID_BODY => 'Begärans kropp är ogiltig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Något gick fel.',
];
