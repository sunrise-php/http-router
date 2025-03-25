<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Pyynnön URI on väärin muodostettu eikä sitä voida hyväksyä palvelimella.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Pyydettyä resurssia ei löydetty tälle URI:lle.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Pyydetty menetelmä ei ole sallittu tälle resurssille.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Pyynnön media type puuttuu.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Pyynnön media type ei ole tuettu tälle resurssille.',
    ErrorMessage::INVALID_VARIABLE => 'Muuttujan {{{ variable_name }}} arvo pyynnön URI:ssa "{{ route_uri }}" on virheellinen.',
    ErrorMessage::INVALID_QUERY => 'Pyynnön kyselyparametrit ovat virheelliset.',
    ErrorMessage::MISSING_HEADER => 'Pyynnön header "{{ header_name }}" puuttuu.',
    ErrorMessage::INVALID_HEADER => 'Pyynnön header "{{ header_name }}" on virheellinen.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" puuttuu.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" on virheellinen.',
    ErrorMessage::INVALID_BODY => 'Pyynnön body on virheellinen.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Jokin meni pieleen.',
];
