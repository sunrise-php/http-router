<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'ה-URI של הבקשה מעוות ולא ניתן לקבל אותו על ידי השרת.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'המשאב המבוקש לא נמצא עבור ה-URI הזה.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'המתודה המבוקשת אינה מותרת עבור משאב זה.',
    ErrorMessage::INVALID_VARIABLE => 'הערך של המשתנה {{{ variable_name }}} ב-URI של הבקשה "{{ route_uri }}" אינו תקין.',
    ErrorMessage::INVALID_QUERY => 'הפרמטרים של הבקשה אינם תקינים.',
    ErrorMessage::MISSING_HEADER => 'הכותרת של הבקשה "{{ header_name }}" חסרה.',
    ErrorMessage::INVALID_HEADER => 'הכותרת של הבקשה "{{ header_name }}" אינה תקינה.',
    ErrorMessage::MISSING_COOKIE => 'הקוקי "{{ cookie_name }}" חסר.',
    ErrorMessage::INVALID_COOKIE => 'הקוקי "{{ cookie_name }}" אינו תקין.',
    ErrorMessage::INVALID_BODY => 'גוף הבקשה אינו תקין.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'אירעה שגיאה.',
];
