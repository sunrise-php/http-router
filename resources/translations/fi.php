<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Pyynnön URI on virheellisesti muotoiltu eikä palvelin voi hyväksyä sitä.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Pyydettyä resurssia ei löytynyt tästä URI:sta.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Pyydettyä menetelmää ei sallita tälle resurssille.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Pyynnön mediatyyppi puuttuu.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Pyynnön mediatyyppiä ei tueta tälle resurssille.',
    ErrorMessage::INVALID_VARIABLE => 'Pyynnön URI:ssa \"{{ route_uri }}\" oleva muuttuja {{{ variable_name }}} on virheellinen.',
    ErrorMessage::INVALID_QUERY => 'Pyynnön kyselyparametrit ovat virheelliset.',
    ErrorMessage::MISSING_HEADER => 'Pyynnön otsake \"{{ header_name }}\" puuttuu.',
    ErrorMessage::INVALID_HEADER => 'Pyynnön otsake \"{{ header_name }}\" on virheellinen.',
    ErrorMessage::MISSING_COOKIE => 'Eväste \"{{ cookie_name }}\" puuttuu.',
    ErrorMessage::INVALID_COOKIE => 'Eväste \"{{ cookie_name }}\" on virheellinen.',
    ErrorMessage::INVALID_BODY => 'Pyynnön runko on virheellinen.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Jokin meni pieleen.',
];
