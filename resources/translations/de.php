<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Die Anfrage-URI ist fehlerhaft und kann vom Server nicht akzeptiert werden.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Die angeforderte Ressource wurde für diese URI nicht gefunden.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Die angeforderte Methode ist für diese Ressource nicht erlaubt.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Der Medientyp der Anfrage fehlt.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Der Medientyp der Anfrage wird für diese Ressource nicht unterstützt.',
    ErrorMessage::INVALID_VARIABLE => 'Der Wert der Variable {{{ variable_name }}} in der Anfrage-URI \"{{ route_uri }}\" ist ungültig.',
    ErrorMessage::INVALID_QUERY => 'Die Abfrageparameter der Anfrage sind ungültig.',
    ErrorMessage::MISSING_HEADER => 'Der Anfrage-Header \"{{ header_name }}\" fehlt.',
    ErrorMessage::INVALID_HEADER => 'Der Anfrage-Header \"{{ header_name }}\" ist ungültig.',
    ErrorMessage::MISSING_COOKIE => 'Das Cookie \"{{ cookie_name }}\" fehlt.',
    ErrorMessage::INVALID_COOKIE => 'Das Cookie \"{{ cookie_name }}\" ist ungültig.',
    ErrorMessage::INVALID_BODY => 'Der Anfragetext ist ungültig.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Etwas ist schiefgelaufen.',
];
