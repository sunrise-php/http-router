<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Die Anforderungs-URI ist fehlerhaft und kann vom Server nicht akzeptiert werden.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Die angeforderte Ressource wurde für diese URI nicht gefunden.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Die angeforderte Methode ist für diese Ressource nicht erlaubt.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Der Medientyp der Anforderung fehlt.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Der Medientyp der Anforderung wird von dieser Ressource nicht unterstützt.',
    ErrorMessage::INVALID_VARIABLE => 'Der Wert der Variablen {{{ variable_name }}} in der Anforderungs-URI "{{ route_uri }}" ist ungültig.',
    ErrorMessage::INVALID_QUERY => 'Die Anforderungsabfrageparameter sind ungültig.',
    ErrorMessage::MISSING_HEADER => 'Der Anforderungsheader "{{ header_name }}" fehlt.',
    ErrorMessage::INVALID_HEADER => 'Der Anforderungsheader "{{ header_name }}" ist ungültig.',
    ErrorMessage::MISSING_COOKIE => 'Das Cookie "{{ cookie_name }}" fehlt.',
    ErrorMessage::INVALID_COOKIE => 'Das Cookie "{{ cookie_name }}" ist ungültig.',
    ErrorMessage::INVALID_BODY => 'Der Anforderungstext ist ungültig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Etwas ist schief gelaufen.',
];
