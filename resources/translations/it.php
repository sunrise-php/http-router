<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'L\'URI della richiesta è malformato e non può essere accettato dal server.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'La risorsa richiesta non è stata trovata per questo URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Il metodo richiesto non è consentito per questa risorsa.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Il tipo di media della richiesta è mancante.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Il tipo di media della richiesta non è supportato da questa risorsa.',
    ErrorMessage::INVALID_VARIABLE => 'Il valore della variabile {{{ variable_name }}} nell\'URI della richiesta "{{ route_uri }}" non è valido.',
    ErrorMessage::INVALID_QUERY => 'I parametri della query della richiesta non sono validi.',
    ErrorMessage::MISSING_HEADER => 'L\'intestazione della richiesta "{{ header_name }}" non è presente.',
    ErrorMessage::INVALID_HEADER => 'L\'intestazione della richiesta "{{ header_name }}" non è valida.',
    ErrorMessage::MISSING_COOKIE => 'Il cookie "{{ cookie_name }}" non è presente.',
    ErrorMessage::INVALID_COOKIE => 'Il cookie "{{ cookie_name }}" non è valido.',
    ErrorMessage::INVALID_BODY => 'Il corpo della richiesta non è valido.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Qualcosa è andato storto.',
];
