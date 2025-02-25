<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI-то на заявката е неправилно и не може да бъде прието от сървъра.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Търсеният ресурс не беше намерен за този URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Използваният метод не е разрешен за този ресурс.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Липсва медийният тип на заявката.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Медийният тип на заявката не се поддържа за този ресурс.',
    ErrorMessage::INVALID_VARIABLE => 'Стойността на променливата {{{ variable_name }}} в URI на заявката \"{{ route_uri }}\" е невалидна.',
    ErrorMessage::INVALID_QUERY => 'Параметрите на заявката са невалидни.',
    ErrorMessage::MISSING_HEADER => 'Заглавката \"{{ header_name }}\" липсва.',
    ErrorMessage::INVALID_HEADER => 'Заглавката \"{{ header_name }}\" е невалидна.',
    ErrorMessage::MISSING_COOKIE => 'Бисквитката \"{{ cookie_name }}\" липсва.',
    ErrorMessage::INVALID_COOKIE => 'Бисквитката \"{{ cookie_name }}\" е невалидна.',
    ErrorMessage::INVALID_BODY => 'Тялото на заявката е невалидно.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Нещо се обърка.',
];
