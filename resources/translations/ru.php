<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI запроса имеет неверный формат и не может быть принят сервером.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Запрашиваемый ресурс не найден для этого URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Запрошенный метод не разрешён для этого ресурса.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Отсутствует тип медиа запроса.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Тип медиа запроса не поддерживается для этого ресурса.',
    ErrorMessage::INVALID_VARIABLE => 'Значение переменной {{{ variable_name }}} в URI запроса \"{{ route_uri }}\" недопустимо.',
    ErrorMessage::INVALID_QUERY => 'Параметры запроса недопустимы.',
    ErrorMessage::MISSING_HEADER => 'Заголовок запроса \"{{ header_name }}\" отсутствует.',
    ErrorMessage::INVALID_HEADER => 'Заголовок запроса \"{{ header_name }}\" недопустим.',
    ErrorMessage::MISSING_COOKIE => 'Cookie \"{{ cookie_name }}\" отсутствует.',
    ErrorMessage::INVALID_COOKIE => 'Cookie \"{{ cookie_name }}\" недопустим.',
    ErrorMessage::INVALID_BODY => 'Тело запроса недопустимо.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Что-то пошло не так.',
];
