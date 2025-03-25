<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'عنوان URI للطلب مشوه ولا يمكن قبوله من قبل الخادم.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'المورد المطلوب لم يتم العثور عليه لهذا العنوان URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'الطريقة المطلوبة غير مسموح بها لهذا المورد.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'نوع وسائط الطلب مفقود.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'نوع وسائط الطلب غير مدعوم من قبل هذا المورد.',
    ErrorMessage::INVALID_VARIABLE => 'قيمة المتغير {{{ variable_name }}} في عنوان URI للطلب "{{ route_uri }}" غير صالحة.',
    ErrorMessage::INVALID_QUERY => 'معلمات الطلب في الاستعلام غير صالحة.',
    ErrorMessage::MISSING_HEADER => 'عنوان الطلب "{{ header_name }}" مفقود.',
    ErrorMessage::INVALID_HEADER => 'عنوان الطلب "{{ header_name }}" غير صالح.',
    ErrorMessage::MISSING_COOKIE => 'الكوكي "{{ cookie_name }}" مفقود.',
    ErrorMessage::INVALID_COOKIE => 'الكوكي "{{ cookie_name }}" غير صالح.',
    ErrorMessage::INVALID_BODY => 'جسم الطلب غير صالح.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'حدث خطأ.',
];
