<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Pyyntö-URI on virheellinen eikä palvelin voi hyväksyä sitä.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Pyydetty resurssi ei löytynyt tälle URI:lle.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Pyydettyä metodia ei sallita tälle resurssille.',
    ErrorMessage::INVALID_VARIABLE => 'Muuttujan {{{ variable_name }}} arvo pyyntö-URI:ssa "{{ route_uri }}" on virheellinen.',
    ErrorMessage::INVALID_QUERY => 'Pyyntöparametrit ovat virheellisiä.',
    ErrorMessage::MISSING_HEADER => 'Pyyntöotsikon "{{ header_name }}" puuttuu.',
    ErrorMessage::INVALID_HEADER => 'Pyyntöotsikko "{{ header_name }}" on virheellinen.',
    ErrorMessage::MISSING_COOKIE => 'Eväste "{{ cookie_name }}" puuttuu.',
    ErrorMessage::INVALID_COOKIE => 'Eväste "{{ cookie_name }}" on virheellinen.',
    ErrorMessage::INVALID_BODY => 'Pyyntöruumis on virheellinen.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Tapahtui virhe.',
];
