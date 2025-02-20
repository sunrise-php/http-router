<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'L\'URI della richiesta è formattata in modo errato e non può essere accettata dal server.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'La risorsa richiesta non è stata trovata per questo URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Il metodo richiesto non è consentito per questa risorsa.',
    ErrorMessage::INVALID_VARIABLE => 'Il valore della variabile {{{ variable_name }}} nell\'URI della richiesta "{{ route_uri }}" non è valido.',
    ErrorMessage::INVALID_QUERY => 'I parametri della richiesta non sono validi.',
    ErrorMessage::MISSING_HEADER => 'L\'intestazione della richiesta "{{ header_name }}" è mancante.',
    ErrorMessage::INVALID_HEADER => 'L\'intestazione della richiesta "{{ header_name }}" non è valida.',
    ErrorMessage::MISSING_COOKIE => 'Il cookie "{{ cookie_name }}" è mancante.',
    ErrorMessage::INVALID_COOKIE => 'Il cookie "{{ cookie_name }}" non è valido.',
    ErrorMessage::INVALID_BODY => 'Il corpo della richiesta non è valido.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Si è verificato un errore.',
];
