<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI żądania jest źle sformatowany i nie może zostać zaakceptowany przez serwer.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Żądany zasób nie został znaleziony dla tego URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Żądana metoda nie jest dozwolona dla tego zasobu.',
    ErrorMessage::INVALID_VARIABLE => 'Wartość zmiennej {{{ variable_name }}} w URI żądania "{{ route_uri }}" jest nieprawidłowa.',
    ErrorMessage::INVALID_QUERY => 'Parametry żądania są nieprawidłowe.',
    ErrorMessage::MISSING_HEADER => 'Nagłówek żądania "{{ header_name }}" jest brakujący.',
    ErrorMessage::INVALID_HEADER => 'Nagłówek żądania "{{ header_name }}" jest nieprawidłowy.',
    ErrorMessage::MISSING_COOKIE => 'Brakuje ciasteczka "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Ciasteczko "{{ cookie_name }}" jest nieprawidłowe.',
    ErrorMessage::INVALID_BODY => 'Treść żądania jest nieprawidłowa.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Coś poszło nie tak.',
];
