<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'معرف URI الطلب غير صالح ولا يمكن قبوله من قبل الخادم.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'المورد المطلوب غير موجود لهذا المعرف URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'الطريقة المطلوبة غير مسموح بها لهذا المورد.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'نوع وسائط الطلب مفقود.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'نوع وسائط الطلب غير مدعوم لهذا المورد.',
    ErrorMessage::INVALID_VARIABLE => 'قيمة المتغير {{{ variable_name }}} في معرف URI الطلب \"{{ route_uri }}\" غير صالحة.',
    ErrorMessage::INVALID_QUERY => 'معاملات استعلام الطلب غير صالحة.',
    ErrorMessage::MISSING_HEADER => 'رأس الطلب \"{{ header_name }}\" مفقود.',
    ErrorMessage::INVALID_HEADER => 'رأس الطلب \"{{ header_name }}\" غير صالح.',
    ErrorMessage::MISSING_COOKIE => 'ملف تعريف الارتباط \"{{ cookie_name }}\" مفقود.',
    ErrorMessage::INVALID_COOKIE => 'ملف تعريف الارتباط \"{{ cookie_name }}\" غير صالح.',
    ErrorMessage::INVALID_BODY => 'جسم الطلب غير صالح.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'حدث خطأ ما.',
];
