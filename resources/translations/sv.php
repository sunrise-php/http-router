<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Begäran URI är felaktig formaterad och kan inte accepteras av servern.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Den begärda resursen kunde inte hittas för denna URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Den begärda metoden är inte tillåten för denna resurs.',
    ErrorMessage::INVALID_VARIABLE => 'Värdet på variabeln {{{ variable_name }}} i begärans URI "{{ route_uri }}" är ogiltigt.',
    ErrorMessage::INVALID_QUERY => 'Begärans parametrar är ogiltiga.',
    ErrorMessage::MISSING_HEADER => 'Begärans header "{{ header_name }}" saknas.',
    ErrorMessage::INVALID_HEADER => 'Begärans header "{{ header_name }}" är ogiltig.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" saknas.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" är ogiltig.',
    ErrorMessage::INVALID_BODY => 'Begärans kropp är ogiltig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Något gick fel.',
];
