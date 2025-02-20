<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'معرّف الموارد المطلوب غير صحيح ولا يمكن قبوله من الخادم.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'المورد المطلوب غير موجود لهذا المعرّف.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'الطريقة المطلوبة غير مسموح بها لهذا المورد.',
    ErrorMessage::INVALID_VARIABLE => 'قيمة المتغير {{{ variable_name }}} في معرّف الموارد "{{ route_uri }}" غير صحيحة.',
    ErrorMessage::INVALID_QUERY => 'معلمات الطلب غير صحيحة.',
    ErrorMessage::MISSING_HEADER => 'رأس الطلب "{{ header_name }}" مفقود.',
    ErrorMessage::INVALID_HEADER => 'رأس الطلب "{{ header_name }}" غير صحيح.',
    ErrorMessage::MISSING_COOKIE => 'ملف تعريف الارتباط "{{ cookie_name }}" مفقود.',
    ErrorMessage::INVALID_COOKIE => 'ملف تعريف الارتباط "{{ cookie_name }}" غير صالح.',
    ErrorMessage::INVALID_BODY => 'محتوى الطلب غير صالح.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'حدث خطأ ما.',
];
