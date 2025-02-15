<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI zahteva je oštećen i ne može biti prihvaćen od strane servera.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Traženi resurs nije pronađen za dati URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Traženi metod nije dozvoljen za ovaj resurs.',
    ErrorMessage::INVALID_VARIABLE => 'Vrednost promenljive {{{ variable_name }}} u URI-ju zahteva "{{ route_uri }}" nije validna.',
    ErrorMessage::INVALID_QUERY => 'Parametri zahteva nisu validni.',
    ErrorMessage::MISSING_HEADER => 'Nedostaje zaglavlje zahteva "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Zaglavlje zahteva "{{ header_name }}" nije validno.',
    ErrorMessage::MISSING_COOKIE => 'Nedostaje cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" nije validan.',
    ErrorMessage::INVALID_BODY => 'Telo zahteva nije validno.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Nešto nije u redu.',
];
