<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI запроса поврежден и не может быть принят сервером.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Запрашиваемый ресурс не найден для данного URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Запрашиваемый метод не разрешен для этого ресурса.',
    ErrorMessage::INVALID_VARIABLE => 'Значение переменной {{{ variable_name }}} в URI запроса "{{ route_uri }}" невалидно.',
    ErrorMessage::INVALID_QUERY => 'Параметры запроса невалидны.',
    ErrorMessage::MISSING_HEADER => 'Отсутствует заголовок запроса "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Заголовок запроса "{{ header_name }}" невалиден.',
    ErrorMessage::MISSING_COOKIE => 'Отсутствует cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" невалидно.',
    ErrorMessage::INVALID_BODY => 'Тело запроса невалидно.',
];
