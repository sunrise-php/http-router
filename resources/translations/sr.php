<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI zahteva je neispravan i server ga ne može prihvatiti.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Traženi resurs nije pronađen za ovaj URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Zahtevana metoda nije dozvoljena za ovaj resurs.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Tip medija zahteva nedostaje.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Tip medija zahteva nije podržan od strane ovog resursa.',
    ErrorMessage::INVALID_VARIABLE => 'Vrednost promenljive {{{ variable_name }}} u URI-ju zahteva "{{ route_uri }}" je neispravna.',
    ErrorMessage::INVALID_QUERY => 'Parametri upita zahteva su neispravni.',
    ErrorMessage::MISSING_HEADER => 'Zaglavlje zahteva "{{ header_name }}" nedostaje.',
    ErrorMessage::INVALID_HEADER => 'Zaglavlje zahteva "{{ header_name }}" je neispravno.',
    ErrorMessage::MISSING_COOKIE => 'Kolačić "{{ cookie_name }}" nedostaje.',
    ErrorMessage::INVALID_COOKIE => 'Kolačić "{{ cookie_name }}" je neispravan.',
    ErrorMessage::INVALID_BODY => 'Telo zahteva je neispravno.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Došlo je do greške.',
];
