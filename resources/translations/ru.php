<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI запроса имеет неверный формат и не может быть принят сервером.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Запрашиваемый ресурс не найден для этого URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Запрашиваемый метод не разрешен для этого ресурса.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Тип данных запроса отсутствует.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Тип данных запроса не поддерживается этим ресурсом.',
    ErrorMessage::INVALID_VARIABLE => 'Значение переменной {{{ variable_name }}} в URI запроса "{{ route_uri }}" недействительно.',
    ErrorMessage::INVALID_QUERY => 'Параметры запроса недействительны.',
    ErrorMessage::MISSING_HEADER => 'Заголовок запроса "{{ header_name }}" отсутствует.',
    ErrorMessage::INVALID_HEADER => 'Заголовок запроса "{{ header_name }}" недействителен.',
    ErrorMessage::MISSING_COOKIE => 'Отсутствует cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" недействителен.',
    ErrorMessage::INVALID_BODY => 'Тело запроса недействительно.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Что-то пошло не так.',
];
