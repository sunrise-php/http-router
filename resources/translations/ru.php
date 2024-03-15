<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::INVALID_URI => 'URI запроса невалиден и не может быть принят сервером.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Запрашиваемый ресурс не найден для данного URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Запрашиваемый метод не разрешен для данного ресурса; Проверьте заголовок ответа "Allow" на разрешенные методы.',
    ErrorMessage::MISSING_CONTENT_TYPE => 'Заголовок запроса Content-Type должен быть предоставлен и не может быть пустым; Проверьте заголовок ответа "Accept" на поддерживаемые типы медиа.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Тип медиа {{ media_type }} не поддерживается; Проверьте заголовок ответа "Accept" на поддерживаемые типы медиа.',
    ErrorMessage::INVALID_VARIABLE => 'Значение переменной {{{ variable_name }}} в URI запроса {{ route_uri }} невалидно.',
    ErrorMessage::INVALID_QUERY => 'Параметры запроса невалидны.',
    ErrorMessage::MISSING_HEADER => 'Заголовок запроса {{ header_name }} должен быть предоставлен.',
    ErrorMessage::INVALID_HEADER => 'Заголовок запроса {{ header_name }} невалиден.',
    ErrorMessage::MISSING_COOKIE => 'Отсутствует cookie {{ cookie_name }}.',
    ErrorMessage::INVALID_COOKIE => 'Cookie {{ cookie_name }} невалидно.',
    ErrorMessage::INVALID_BODY => 'Тело запроса невалидно.',
    ErrorMessage::EMPTY_JSON_PAYLOAD => 'JSON payload не может быть пустым.',
    ErrorMessage::INVALID_JSON_PAYLOAD => 'JSON payload невалидно.',
    ErrorMessage::INVALID_JSON_PAYLOAD_FORMAT => 'JSON payload должен быть в формате массива или объекта.',
];
