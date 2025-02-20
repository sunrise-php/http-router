<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI запроса неправильно сформирован и не может быть принят сервером.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Запрашиваемый ресурс не найден для этого URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Запрашиваемый метод не разрешен для этого ресурса.',
    ErrorMessage::INVALID_VARIABLE => 'Значение переменной {{{ variable_name }}} в URI запроса "{{ route_uri }}" неверно.',
    ErrorMessage::INVALID_QUERY => 'Параметры запроса неверны.',
    ErrorMessage::MISSING_HEADER => 'Заголовок запроса "{{ header_name }}" отсутствует.',
    ErrorMessage::INVALID_HEADER => 'Заголовок запроса "{{ header_name }}" неверен.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" отсутствует.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" неверен.',
    ErrorMessage::INVALID_BODY => 'Тело запроса неверно.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Произошла ошибка.',
];
