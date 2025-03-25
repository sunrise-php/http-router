<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI-то на заявката е неправилен и не може да бъде приет от сървъра.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Заявеното ресурс не е намерено за този URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Заявеният метод не е позволен за този ресурс.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Липсва типът на медията в заявката.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Типът на медията в заявката не се поддържа от този ресурс.',
    ErrorMessage::INVALID_VARIABLE => 'Стойността на променливата {{{ variable_name }}} в URI-то на заявката "{{ route_uri }}" е невалидна.',
    ErrorMessage::INVALID_QUERY => 'Параметрите на заявката са невалидни.',
    ErrorMessage::MISSING_HEADER => 'Липсва заглавка на заявката "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Заглавката на заявката "{{ header_name }}" е невалидна.',
    ErrorMessage::MISSING_COOKIE => 'Липсва бисквитката "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Бисквитката "{{ cookie_name }}" е невалидна.',
    ErrorMessage::INVALID_BODY => 'Тялото на заявката е невалидно.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Нещо се обърка.',
];
