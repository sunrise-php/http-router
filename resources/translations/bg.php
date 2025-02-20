<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI на заявката е невалиден и не може да бъде приет от сървъра.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Заявеното ресурс не е намерено за този URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Заявен метод не е разрешен за този ресурс.',
    ErrorMessage::INVALID_VARIABLE => 'Стойността на променливата {{{ variable_name }}} в URI на заявката "{{ route_uri }}" е невалидна.',
    ErrorMessage::INVALID_QUERY => 'Параметрите на заявката са невалидни.',
    ErrorMessage::MISSING_HEADER => 'Липсва хедър в заявката: "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Хедърът "{{ header_name }}" е невалиден.',
    ErrorMessage::MISSING_COOKIE => 'Липсва бисквитка "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Бисквитката "{{ cookie_name }}" е невалидна.',
    ErrorMessage::INVALID_BODY => 'Тялото на заявката е невалидно.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Възникна грешка.',
];
