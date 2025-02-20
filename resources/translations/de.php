<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Die angeforderte URI ist fehlerhaft und kann vom Server nicht akzeptiert werden.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Die angeforderte Ressource wurde für diese URI nicht gefunden.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Die angeforderte Methode ist für diese Ressource nicht erlaubt.',
    ErrorMessage::INVALID_VARIABLE => 'Der Wert der Variablen {{{ variable_name }}} in der URI der Anfrage "{{ route_uri }}" ist ungültig.',
    ErrorMessage::INVALID_QUERY => 'Die Anforderungsparameter sind ungültig.',
    ErrorMessage::MISSING_HEADER => 'Der Anforderungs-Header "{{ header_name }}" fehlt.',
    ErrorMessage::INVALID_HEADER => 'Der Anforderungs-Header "{{ header_name }}" ist ungültig.',
    ErrorMessage::MISSING_COOKIE => 'Das Cookie "{{ cookie_name }}" fehlt.',
    ErrorMessage::INVALID_COOKIE => 'Das Cookie "{{ cookie_name }}" ist ungültig.',
    ErrorMessage::INVALID_BODY => 'Der Anforderungskörper ist ungültig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Es ist ein Fehler aufgetreten.',
];
