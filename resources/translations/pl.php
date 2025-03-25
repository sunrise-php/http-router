<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI żądania jest sformułowane nieprawidłowo i nie może zostać zaakceptowane przez serwer.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Żądany zasób nie został znaleziony dla tego URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Żądana metoda nie jest dozwolona dla tego zasobu.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Typ mediów żądania jest brakujący.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Typ mediów żądania nie jest obsługiwany przez ten zasób.',
    ErrorMessage::INVALID_VARIABLE => 'Wartość zmiennej {{{ variable_name }}} w URI żądania "{{ route_uri }}" jest nieprawidłowa.',
    ErrorMessage::INVALID_QUERY => 'Parametry zapytania żądania są nieprawidłowe.',
    ErrorMessage::MISSING_HEADER => 'Nagłówek żądania "{{ header_name }}" jest brakujący.',
    ErrorMessage::INVALID_HEADER => 'Nagłówek żądania "{{ header_name }}" jest nieprawidłowy.',
    ErrorMessage::MISSING_COOKIE => 'Ciasteczko "{{ cookie_name }}" jest brakujące.',
    ErrorMessage::INVALID_COOKIE => 'Ciasteczko "{{ cookie_name }}" jest nieprawidłowe.',
    ErrorMessage::INVALID_BODY => 'Treść żądania jest nieprawidłowa.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Coś poszło nie tak.',
];
