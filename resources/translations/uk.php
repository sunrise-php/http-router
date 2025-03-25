<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI запиту має неправильний формат і не може бути прийнятий сервером.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Запитуваний ресурс не було знайдено для цього URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Запитуваний метод не дозволено для цього ресурсу.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Тип медіа запиту відсутній.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Тип медіа запиту не підтримується цим ресурсом.',
    ErrorMessage::INVALID_VARIABLE => 'Значення змінної {{{ variable_name }}} у URI запиту "{{ route_uri }}" є недійсним.',
    ErrorMessage::INVALID_QUERY => 'Параметри запиту є недійсними.',
    ErrorMessage::MISSING_HEADER => 'Заголовок запиту "{{ header_name }}" відсутній.',
    ErrorMessage::INVALID_HEADER => 'Заголовок запиту "{{ header_name }}" є недійсним.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" відсутній.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" є недійсним.',
    ErrorMessage::INVALID_BODY => 'Тіло запиту є недійсним.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Щось пішло не так.',
];
