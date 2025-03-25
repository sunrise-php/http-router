<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'כתובת ה-URI שגויה ואינה יכולה להתקבל על ידי השרת.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'המשאב המבוקש לא נמצא עבור כתובת ה-URI הזו.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'השיטה המבוקשת אינה מותרת עבור משאב זה.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'סוג המדיה של הבקשה חסר.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'סוג המדיה של הבקשה אינו נתמך על ידי משאב זה.',
    ErrorMessage::INVALID_VARIABLE => 'הערך של המשתנה {{{ variable_name }}} בכתובת ה-URI של הבקשה "{{ route_uri }}" אינו תקין.',
    ErrorMessage::INVALID_QUERY => 'פרמטרי השאילתה של הבקשה אינם תקינים.',
    ErrorMessage::MISSING_HEADER => 'כותרת הבקשה "{{ header_name }}" חסרה.',
    ErrorMessage::INVALID_HEADER => 'כותרת הבקשה "{{ header_name }}" אינה תקינה.',
    ErrorMessage::MISSING_COOKIE => 'העוגייה "{{ cookie_name }}" חסרה.',
    ErrorMessage::INVALID_COOKIE => 'העוגייה "{{ cookie_name }}" אינה תקינה.',
    ErrorMessage::INVALID_BODY => 'גוף הבקשה אינו תקין.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'משהו השתבש.',
];
