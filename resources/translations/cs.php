<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI požadavku je špatně formátováno a server ho nemůže přijmout.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Požadovaný zdroj nebyl pro toto URI nalezen.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Požadovaná metoda není pro tento zdroj povolena.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Chybí mediální typ požadavku.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Mediální typ požadavku není pro tento zdroj podporován.',
    ErrorMessage::INVALID_VARIABLE => 'Hodnota proměnné {{{ variable_name }}} v URI požadavku \"{{ route_uri }}\" je neplatná.',
    ErrorMessage::INVALID_QUERY => 'Parametry dotazu požadavku jsou neplatné.',
    ErrorMessage::MISSING_HEADER => 'Záhlaví požadavku \"{{ header_name }}\" chybí.',
    ErrorMessage::INVALID_HEADER => 'Záhlaví požadavku \"{{ header_name }}\" je neplatné.',
    ErrorMessage::MISSING_COOKIE => 'Cookie \"{{ cookie_name }}\" chybí.',
    ErrorMessage::INVALID_COOKIE => 'Cookie \"{{ cookie_name }}\" je neplatné.',
    ErrorMessage::INVALID_BODY => 'Tělo požadavku je neplatné.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Něco se pokazilo.',
];
