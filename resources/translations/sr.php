<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI захтева је неисправно форматиран и сервер га не може прихватити.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Захтевани ресурс није пронађен за овај URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Захтевана метода није дозвољена за овај ресурс.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Недостаје медијски тип захтева.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Медијски тип захтева није подржан за овај ресурс.',
    ErrorMessage::INVALID_VARIABLE => 'Вредност променљиве {{{ variable_name }}} у URI-ју захтева \"{{ route_uri }}\" је неважећа.',
    ErrorMessage::INVALID_QUERY => 'Параметри упита захтева су неважећи.',
    ErrorMessage::MISSING_HEADER => 'Заглавље захтева \"{{ header_name }}\" недостаје.',
    ErrorMessage::INVALID_HEADER => 'Заглавље захтева \"{{ header_name }}\" је неважеће.',
    ErrorMessage::MISSING_COOKIE => 'Колачић \"{{ cookie_name }}\" недостаје.',
    ErrorMessage::INVALID_COOKIE => 'Колачић \"{{ cookie_name }}\" је неважећи.',
    ErrorMessage::INVALID_BODY => 'Тело захтева је неважеће.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Нешто је пошао по злу.',
];
