<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI запиту неправильний і не може бути прийнятий сервером.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Запитаний ресурс не знайдений для цього URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Запитаний метод не дозволений для цього ресурсу.',
    ErrorMessage::INVALID_VARIABLE => 'Значення змінної {{{ variable_name }}} в URI запиту "{{ route_uri }}" є недійсним.',
    ErrorMessage::INVALID_QUERY => 'Параметри запиту недійсні.',
    ErrorMessage::MISSING_HEADER => 'Заголовок запиту "{{ header_name }}" відсутній.',
    ErrorMessage::INVALID_HEADER => 'Заголовок запиту "{{ header_name }}" є недійсним.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" відсутнє.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" є недійсним.',
    ErrorMessage::INVALID_BODY => 'Тіло запиту є недійсним.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Сталася помилка.',
];
