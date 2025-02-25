<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI zahteve je napačno oblikovan in ga strežnik ne more sprejeti.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Zahtevani vir ni bil najden za ta URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Zahtevana metoda ni dovoljena za ta vir.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Manjka medijski tip zahteve.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Medijski tip zahteve ni podprt za ta vir.',
    ErrorMessage::INVALID_VARIABLE => 'Vrednost spremenljivke {{{ variable_name }}} v URI-ju zahteve \"{{ route_uri }}\" je neveljavna.',
    ErrorMessage::INVALID_QUERY => 'Parametri poizvedbe v zahtevi so neveljavni.',
    ErrorMessage::MISSING_HEADER => 'Manjka glava zahteve \"{{ header_name }}\".',
    ErrorMessage::INVALID_HEADER => 'Glava zahteve \"{{ header_name }}\" je neveljavna.',
    ErrorMessage::MISSING_COOKIE => 'Piškotek \"{{ cookie_name }}\" manjka.',
    ErrorMessage::INVALID_COOKIE => 'Piškotek \"{{ cookie_name }}\" je neveljaven.',
    ErrorMessage::INVALID_BODY => 'Telo zahteve je neveljavno.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Nekaj je šlo narobe.',
];
