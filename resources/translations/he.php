<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'כתובת ה-URI של הבקשה מעוצבת בצורה שגויה ואינה יכולה להתקבל על ידי השרת.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'המשאב המבוקש לא נמצא עבור כתובת ה-URI הזו.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'השיטה המבוקשת אינה מותרת עבור המשאב הזה.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'סוג המדיה של הבקשה חסר.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'סוג המדיה של הבקשה אינו נתמך עבור המשאב הזה.',
    ErrorMessage::INVALID_VARIABLE => 'הערך של המשתנה {{{ variable_name }}} בכתובת ה-URI \"{{ route_uri }}\" אינו חוקי.',
    ErrorMessage::INVALID_QUERY => 'פרמטרי השאילתה של הבקשה אינם חוקיים.',
    ErrorMessage::MISSING_HEADER => 'כותרת הבקשה \"{{ header_name }}\" חסרה.',
    ErrorMessage::INVALID_HEADER => 'כותרת הבקשה \"{{ header_name }}\" אינה חוקית.',
    ErrorMessage::MISSING_COOKIE => 'עוגיה \"{{ cookie_name }}\" חסרה.',
    ErrorMessage::INVALID_COOKIE => 'עוגיה \"{{ cookie_name }}\" אינה חוקית.',
    ErrorMessage::INVALID_BODY => 'גוף הבקשה אינו חוקי.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'משהו השתבש.',
];
