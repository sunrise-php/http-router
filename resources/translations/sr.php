<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    // phpcs:disable Generic.Files.LineLength.TooLong
    ErrorMessage::MALFORMED_URI => 'URI zahtev je nevažeći i ne može biti prihvaćen od strane servera.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Zatraženi resurs nije pronađen za ovaj URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Zatražena metoda nije dozvoljena za ovaj resurs; Proverite zaglavlje odgovora "Allow" za dozvoljene metode.',
    ErrorMessage::MISSING_CONTENT_TYPE => 'Zaglavlje zahteva Content-Type mora biti navedeno i ne može biti prazno; Proverite zaglavlje odgovora "Accept" za podržane tipove medija.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Tip medija {{ media_type }} nije podržan; Proverite zaglavlje odgovora "Accept" za podržane tipove medija.',
    ErrorMessage::INVALID_VARIABLE => 'Vrednost promenljive {{{ variable_name }}} u URI zahtevu {{ route_uri }} nije validna.',
    ErrorMessage::INVALID_QUERY => 'Parametri upita zahteva nisu validni.',
    ErrorMessage::MISSING_HEADER => 'Zaglavlje zahteva {{ header_name }} mora biti navedeno.',
    ErrorMessage::INVALID_HEADER => 'Zaglavlje zahteva {{ header_name }} nije validno.',
    ErrorMessage::MISSING_COOKIE => 'Kolačić {{ cookie_name }} nedostaje.',
    ErrorMessage::INVALID_COOKIE => 'Kolačić {{ cookie_name }} nije validan.',
    ErrorMessage::INVALID_BODY => 'Telo zahteva nije validno.',
    ErrorMessage::EMPTY_JSON_PAYLOAD => 'JSON sadržaj ne može biti prazan.',
    ErrorMessage::INVALID_JSON_PAYLOAD => 'JSON sadržaj nije validan.',
    ErrorMessage::INVALID_JSON_PAYLOAD_FORMAT => 'JSON sadržaj mora biti u formatu niza ili objekta.',
    // phpcs:enable Generic.Files.LineLength.TooLong
];
